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

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<title>Tài khoản</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#fff5f9;
}

/* HEADER */
.header{

    background:#ff85c1;
    color:white;
    padding:18px 30px;
    font-size:28px;
    font-weight:bold;
    display:flex;
    align-items:center;
    gap:12px;
}

/* CONTAINER */
.profile-container{

    width:1100px;

    margin:30px auto;

    display:flex;

    gap:25px;
}

/* SIDEBAR */
.sidebar{

    width:250px;

    background:white;

    border-radius:15px;

    padding:20px;

    box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

/* USER */
.user-box{

    text-align:center;

    margin-bottom:25px;
}

.avatar{

    width:80px;
    height:80px;

    border-radius:50%;

    background:#ff85c1;

    margin:auto;

    display:flex;
    align-items:center;
    justify-content:center;

    color:white;

    font-size:35px;
}

.user-name{

    margin-top:10px;

    font-size:18px;

    font-weight:bold;
}

/* MENU */
.menu{

    display:flex;
    flex-direction:column;

    gap:10px;
}

.menu a{

    padding:12px;

    border-radius:10px;

    text-decoration:none;

    color:#444;

    transition:0.2s;
}

.menu a:hover{

    background:#ffe3f1;

    color:#ff4fa3;
}

/* CONTENT */
.content{

    flex:1;

    background:white;

    border-radius:15px;

    padding:30px;

    box-shadow:0 4px 10px rgba(0,0,0,0.08);
}

/* TITLE */
.content h2{

    margin-top:0;

    color:#ff4fa3;
}

/* INFO */
.info{

    margin-top:20px;
}

.info p{

    font-size:17px;

    margin:15px 0;
}

/* BUTTON */
.logout-btn{

    display:inline-block;

    margin-top:25px;

    padding:12px 20px;

    background:#ff4fa3;

    color:white;

    border-radius:10px;

    text-decoration:none;
}

.logout-btn:hover{

    background:#e63d8d;
}

/* ICON TRANG CHỦ */
.home-icon {
    color:white;
    font-size:20px;

    width:40px;
    height:40px;

    display:flex;
    align-items:center;
    justify-content:center;

    border-radius:50%;
    text-decoration:none;

    transition:0.2s;
}

.home-icon:hover {
    background:rgba(255,255,255,0.2);
}


</style>
</head>

<body>
    

<div class="header">

    <a href="index.php" class="home-icon">
        <i class="fa-solid fa-house"></i>
    </a>

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
        <div class="menu">

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

            <p>
                <strong>Họ tên:</strong>
                <?php echo $user['name']; ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?php echo $user['email']; ?>
            </p>

            <p>
                <strong>Vai trò:</strong>

                <?php echo $user['role'] == 1
                    ? 'Admin'
                    : 'Khách hàng';
?>
            </p>

        </div>

    </div>

</div>

</body>
</html>