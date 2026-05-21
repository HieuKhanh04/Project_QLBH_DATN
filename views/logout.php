<?php

session_start();

/* chỉ xoá thông tin đăng nhập */
unset($_SESSION['user']);

/* chuyển về login */
header('Location: login.php');

exit;
