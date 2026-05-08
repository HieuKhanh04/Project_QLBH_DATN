<?php

session_start();

require_once '../config/database.php';
require_once '../models/UserModel.php';

$userModel = new UserModel($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Kiểm tra email đã tồn tại chưa
    $check = $userModel->checkEmail($email);

    if ($check) {
        echo "
        <script>
            alert('Email đã tồn tại!');
            window.history.back();
        </script>
        ";

        exit;
    }

    // Thêm user mới
    $result = $userModel->register($name, $email, $password);

    if ($result) {
        echo "
        <script>
            alert('Đăng ký thành công!');
            window.location='../views/login.php';
        </script>
        ";
    } else {
        echo "
        <script>
            alert('Đăng ký thất bại!');
            window.history.back();
        </script>
        ";
    }
}
