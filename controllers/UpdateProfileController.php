<?php

session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../views/login.php');
    exit;
}

$userId = $_SESSION['user']['user_id'];

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);

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

$_SESSION['user']['name'] = $name;
$_SESSION['user']['email'] = $email;
$_SESSION['user']['phone'] = $phone;
$_SESSION['user']['address'] = $address;

// Thông báo thành công
$_SESSION['profile_success'] = 'Cập nhật thông tin thành công!';

header('Location: ../views/profile.php');
exit;
