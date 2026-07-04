<?php

session_start();

/* chỉ xoá thông tin đăng nhập */
unset($_SESSION['user']);

session_destroy();
/* chuyển về login */
header('Location: login.php');

exit;
