<?php

session_start();

require_once '../config/database.php';
require_once '../models/UserModel.php';

$userModel = new UserModel($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $userModel->login($email, $password);

    if ($user) {
        $_SESSION['user'] = $user;

        if ($user['role'] == 1) {
            header('Location: ../views/admin/admin_dashboard.php');
        } else {
            header('Location: ../views/index.php');
        }

        exit;
    } else {
        $_SESSION['login_error'] = 'Sai email hoặc mật khẩu!';
        header('Location: ../views/login.php');
        exit;
    }
}
