<?php

session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../views/login.php');
    exit;
}

$userId = $_SESSION['user']['user_id'];

$oldPassword = trim($_POST['old_password'] ?? '');
$newPassword = trim($_POST['new_password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

/* Kiểm tra dữ liệu */
if (
    $oldPassword == ''
    || $newPassword == ''
    || $confirmPassword == ''
) {
    exit('Vui lòng nhập đầy đủ thông tin.');
}

if ($newPassword != $confirmPassword) {
    exit('Mật khẩu xác nhận không khớp.');
}

/* Lấy mật khẩu hiện tại */
$stmt = $conn->prepare('
    SELECT password
    FROM users
    WHERE user_id = ?
');

$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit('Không tìm thấy tài khoản.');
}

/* Kiểm tra mật khẩu cũ */
// if (!password_verify($oldPassword, $user['password'])) {
//     exit('Mật khẩu hiện tại không đúng.');
// }
if ($oldPassword != $user['password']) {
    exit('Mật khẩu hiện tại không đúng.');
}

/* Hash mật khẩu mới */
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

/* Update */
// $stmt = $conn->prepare('
//     UPDATE users
//     SET password = ?
//     WHERE user_id = ?
// ');

// $stmt->execute([
//     $newHash,
//     $userId,
// ]);

$stmt = $conn->prepare('
UPDATE users
SET password=?
WHERE user_id=?
');

$stmt->execute([
    $newPassword,
    $userId,
]);

$_SESSION['password_success'] = 'Đổi mật khẩu thành công!';

header('Location: ../views/profile.php?tab=password&success=1');
exit;
