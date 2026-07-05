<?php

session_start();
require_once '../config/database.php';

/* CHECK LOGIN */
if (!isset($_SESSION['customer'])) {
    header('Location: ../views/login.php');
    exit;
}

$userId = $_SESSION['customer']['user_id'];

$oldPassword = trim($_POST['old_password'] ?? '');
$newPassword = trim($_POST['new_password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

/* VALIDATION */
if ($oldPassword == '' || $newPassword == '' || $confirmPassword == '') {
    exit('Vui lòng nhập đầy đủ thông tin.');
}

if ($newPassword != $confirmPassword) {
    exit('Mật khẩu xác nhận không khớp.');
}

/* GET CURRENT PASSWORD */
$stmt = $conn->prepare('SELECT password FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit('Không tìm thấy tài khoản.');
}

/* CHECK OLD PASSWORD (CHUẨN HASH) */
if (!password_verify($oldPassword, $user['password'])) {
    exit('Mật khẩu hiện tại không đúng.');
}

/* HASH NEW PASSWORD */
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

/* UPDATE PASSWORD */
$stmt = $conn->prepare('
    UPDATE users
    SET password = ?
    WHERE user_id = ?
');

$stmt->execute([
    $newHash,
    $userId,
]);

/* SUCCESS MESSAGE */
$_SESSION['password_success'] = 'Đổi mật khẩu thành công!';

header('Location: ../views/profile.php?tab=password&success=1');
exit;
