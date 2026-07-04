<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title> Quản lý danh mục </title>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#fff5f9;
}

.admin-container{
    display:flex;
}

.sidebar{
    width:260px;
    background:white;
    height:100vh;
    position:fixed;
    padding:30px 20px;
    border-right:1px solid #ffd9ea;
}

.logo{
    font-family:'Great Vibes', cursive;
    font-size:42px;
    color:#ff4fa3;
    font-weight:bold;
    text-decoration:none;
}

.menu-title{
    margin:30px 0 15px;
    color:#999;
    font-size:13px;
    font-weight:bold;
}

.menu a{
    display:flex;
    gap:12px;
    padding:14px;
    border-radius:14px;
    text-decoration:none;
    color:#333;
    margin-bottom:10px;
}

.menu .active{
    background:#ff4fa3;
    color:white;
}

.main-content{
    margin-left:260px;
    width:100%;
    padding:30px;
}

.topbar{
    display:flex;
    justify-content:space-between;
}

.page-title h1{
    font-size:36px;
}

.page-title p{
    color:#777;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:25px 0;
}

.add-btn{
    background:#ff4fa3;
    color:white;
    border:none;
    padding:12px 18px;
    border-radius:12px;
    cursor:pointer;
}

.table-box{
    background:white;
    border-radius:22px;
    padding:25px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    text-align:left;
    color:#999;
    padding:15px;
}

td{
    padding:15px;
    border-top:1px solid #eee;
}

.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:13px;
}

.status-active{
    background:#e4fff0;
    color:#23b26d;
}

.hidden{
    background:#ffe4e4;
    color:#ff4d4d;
}

.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    justify-content:center;
    align-items:center;
    z-index:9999;
}

.modal-content{
    width:520px;
    background:white;
    padding:30px;
    border-radius:24px;
}

.modal-header{
    text-align:center;
    margin-bottom:20px;
}

.modal-icon{
    font-size:60px;
    color:#dc3545;
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    font-weight:bold;
    margin-bottom:7px;
}

input,
textarea,
select{
    width:100%;
    padding:12px;
    border-radius:12px;
    border:1px solid #ddd;
}

.modal-actions{

    display:flex;
    justify-content:flex-end;
    gap:12px;
    margin-top:20px;
}

.admin-box{
    display:flex;
    align-items:center;
    gap:15px;
}

.admin-box img{
    width:50px;
    height:50px;
    border-radius:50%;
}
</style>
</head>

<body>
<div class="admin-container">
    <!-- SIDEBAR -->
<div class="sidebar">

    <a href="admin_dashboard.php" class="logo">
        HAN STORE
    </a>

    <div class="sidebar-content">

        <div class="menu">

            <div class="menu-title">
                MENU CHÍNH
            </div>

            <a href="admin_dashboard.php">
                <i class="fa-solid fa-chart-line"></i>
                Dashboard
            </a>

            <a href="products.php">
                <i class="fa-solid fa-shirt"></i>
                Sản phẩm
            </a>

            <a href="orders.php">
                <i class="fa-solid fa-cart-shopping"></i>
                Đơn hàng
            </a>

            <a href="customers.php">
                <i class="fa-solid fa-users"></i>
                Khách hàng
            </a>

            <a href="promotions.php">
                <i class="fa-solid fa-tags"></i>
                Khuyến mãi
            </a>

            <a href="reports.php">
                <i class="fa-solid fa-chart-pie"></i>
                Báo cáo
            </a>

            <div class="menu-title">
                QUẢN LÝ NỘI DUNG
            </div>

            <a href="categories.php" class="active">
                <i class="fa-regular fa-folder"></i>
                Danh mục
            </a>

            <a href="#">
                <i class="fa-regular fa-image"></i>
                Banner
            </a>

            <a href="notifications.php" class="sidebar-item">
                <i class="fa-regular fa-bell"></i>
                Thông báo
            </a>

            <div class="menu-title">
                HỆ THỐNG
            </div>

            <a href="#">
                <i class="fa-solid fa-gear"></i>
                Cài đặt
            </a>

            <a href="account.php">
                <i class="fa-regular fa-user"></i>
                Tài khoản
            </a>

            <a href="#">
                <i class="fa-regular fa-clock"></i>
                Nhật ký hoạt động
            </a>

        </div>
    </div>

    <a href="../logout.php" class="logout-btn">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
        Đăng xuất
    </a>

</div>
</div>
</body>
</html>