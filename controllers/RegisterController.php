<?php

session_start();

require_once '../config/database.php';
require_once '../models/UserModel.php';

$userModel = new UserModel($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Kiểm tra xác nhận mật khẩu

    if ($password !== $confirmPassword) {
        $_SESSION['register_error'] =
            'Mật khẩu xác nhận không khớp!';

        header('Location: ../views/register.php');
        exit;
    }

    // Kiểm tra độ mạnh mật khẩu
    // - Tối thiểu 6 ký tự
    // - Có chữ thường
    // - Có chữ hoa
    // - Có số
    // - Có ký tự đặc biệt

    $pattern =
        '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.#_-]).{6,}$/';

    if (!preg_match($pattern, $password)) {
        $_SESSION['register_error'] =
            'Mật khẩu phải có ít nhất 6 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt!';

        header('Location: ../views/register.php');
        exit;
    }

    // Kiểm tra email đã tồn tại
    $check = $userModel->checkEmail($email);

    if ($check) {
        $_SESSION['register_error'] =
            'Email đã tồn tại!';

        header('Location: ../views/register.php');
        exit;
    }

    // Đăng ký tài khoản
    $result = $userModel->register(
        $name,
        $email,
        $password
    );

    if ($result) {
        $_SESSION['register_success'] =
            'Đăng ký thành công!';

        header('Location: ../views/login.php');
        exit;
    }

    $_SESSION['register_error'] =
        'Đăng ký thất bại!';

    header('Location: ../views/register.php');
    exit;
}
