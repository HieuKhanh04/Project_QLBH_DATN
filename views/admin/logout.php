<?php

session_start();

require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

/* Ghi nhật ký trước khi xóa session */
writeLog(
    $conn,
    'LOGOUT',
    'Tài khoản',
    'Đăng xuất khỏi hệ thống'
);

unset($_SESSION['admin']);

header('Location: ../login.php');
exit;
