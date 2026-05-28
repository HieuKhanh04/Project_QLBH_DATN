<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">

<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<title>Tài khoản</title>

<style>

/* RESET */
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial;
    background: #fff5f9;
}

/* PAGE CONTAINER */
.profile-container {
    width: 1100px;
    margin: 30px auto;
    display: flex;
    gap: 25px;
}

/* SIDEBAR */
.sidebar {
    width: 250px;
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}

/* USER INFO */
.user-box {
    text-align: center;
    margin-bottom: 25px;
}

.avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #ff85c1;
    margin: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 35px;
}

.user-name {
    margin-top: 10px;
    font-size: 18px;
    font-weight: bold;
}

/* SIDEBAR MENU (ĐÃ SỬA KHÔNG ĐỤNG HEADER) */
.sidebar-menu {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sidebar-menu a {
    padding: 12px;
    border-radius: 10px;
    text-decoration: none;
    color: #444;
    transition: 0.2s;
}

.sidebar-menu a:hover {
    background: #ffe3f1;
    color: #ff4fa3;
}

/* CONTENT */
.content {
    flex: 1;
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}

.content h2 {
    margin-top: 0;
    color: #ff4fa3;
}

.info p {
    font-size: 17px;
    margin: 15px 0;
}

/* PAGE HEADER TITLE */
.profile-page-header {
    width: 100%;
    background: white;
    padding: 20px 50px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.header-title{
    font-family: 'Great Vibes', cursive;
    font-size: 34px;
    font-weight: normal;
    color:#ff4fa3;
}

</style>
</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="profile-page-header">
        <div class="header-title">
            Tài khoản của tôi
        </div>
</div>

<div class="profile-container">

    <!-- SIDEBAR -->
    <div class="sidebar">

        <div class="user-box">
            <div class="avatar">
                <i class="fa-solid fa-user"></i>
            </div>

            <div class="user-name">
                <?php echo $user['name']; ?>
            </div>
        </div>

        <!-- MENU -->
        <div class="sidebar-menu">

            <a href="#">
                <i class="fa-regular fa-user"></i>
                Thông tin tài khoản
            </a>

            <a href="#">
                <i class="fa-solid fa-box"></i>
                Đơn mua
            </a>

            <a href="#">
                <i class="fa-solid fa-tags"></i>
                Voucher của tôi
            </a>

            <a href="#">
                <i class="fa-solid fa-lock"></i>
                Đổi mật khẩu
            </a>

            <a href="logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                Đăng xuất
            </a>

        </div>

    </div>

    <!-- CONTENT -->
    <div class="content">

        <h2>Thông tin tài khoản</h2>

        <div class="info">

            <p><strong>Họ tên:</strong> <?php echo $user['name']; ?></p>
            <p><strong>Email:</strong> <?php echo $user['email']; ?></p>

            <p>
                <strong>Vai trò:</strong>
                <?php echo ($user['role'] ?? 0) == 1 ? 'Admin' : 'Khách hàng'; ?>
            </p>

        </div>

    </div>

</div>

<?php include 'layout/footer.php'; ?>

</body>
</html>