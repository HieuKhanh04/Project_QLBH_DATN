<?php

session_start();

require_once '../config/database.php';

/* KIỂM TRA ĐĂNG NHẬP (ADMIN HOẶC CUSTOMER) */
if (isset($_SESSION['admin'])) {
    $user = $_SESSION['admin'];
    $sessionKey = 'admin';
} elseif (isset($_SESSION['customer'])) {
    $user = $_SESSION['customer'];
    $sessionKey = 'customer';
} else {
    header('Location: ../views/login.php');
    exit;
}

$userId = $user['user_id'];

/* LẤY DỮ LIỆU POST */
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

/* UPDATE DATABASE */
$stmt = $conn->prepare('
    UPDATE users
    SET
        name = ?,
        email = ?,
        phone = ?,
        address = ?
    WHERE user_id = ?
');

$stmt->execute([
    $name,
    $email,
    $phone,
    $address,
    $userId,
]);

/* CẬP NHẬT SESSION TƯƠNG ỨNG */
$_SESSION[$sessionKey]['name'] = $name;
$_SESSION[$sessionKey]['email'] = $email;
$_SESSION[$sessionKey]['phone'] = $phone;
$_SESSION[$sessionKey]['address'] = $address;

/* THÔNG BÁO THÀNH CÔNG */
$_SESSION['profile_success'] = 'Cập nhật thông tin thành công!';

/* QUAY LẠI TRANG PROFILE */
header('Location: ../views/profile.php');
exit;
