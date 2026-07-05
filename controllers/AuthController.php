<?php

session_start();

require_once '../config/database.php';
require_once '../models/UserModel.php';
require_once '../includes/activity_log.php';

$userModel = new UserModel($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $userModel->login($email, $password);

    if ($user) {
        session_regenerate_id(true);

        // ADMIN
        if ($user['role'] == 1) {
            $_SESSION['admin'] = $user;
            writeLog(
                $conn,
                'LOGIN',
                'Tài khoản',
                'Đăng nhập: '.$user['email']
            );

            header('Location: ../views/admin/admin_dashboard.php');
            exit;
        }

        // CUSTOMER
        $_SESSION['customer'] = $user;

        header('Location: ../views/index.php');
        exit;
    } else {
        // LOGIN FAIL
        $_SESSION['login_error'] = 'Sai email hoặc mật khẩu!';

        header('Location: ../views/login.php');
        exit;
    }
}
