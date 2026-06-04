<?php
require_once '../../config/database.php';

/* LẤY KHUYẾN MÃI */
$stmt = $conn->query('
    SELECT *
    FROM promotions
    ORDER BY promotion_id DESC
');
$promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Quản lý khuyến mãi</title>

<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* RESET */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#fff5f9;
}

/* LAYOUT */
.admin-container{
    display:flex;
}

/* SIDEBAR (GIỮ NGUYÊN PRODUCTS) */
.sidebar{
    width:260px;
    background:white;
    border-right:1px solid #ffd9ea;
    position:fixed;
    top:0;
    left:0;
    height:100vh;
    display:flex;
    flex-direction:column;
    padding:30px 20px;
}

.sidebar-content{
    flex:1;
    overflow-y:auto;
    padding-right:5px;
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
    font-size:13px;
    color:#999;
    font-weight:bold;
}

.menu a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px 16px;
    border-radius:14px;
    text-decoration:none;
    color:#333;
    margin-bottom:10px;
}

.menu a:hover{
    background:#fff0f7;
    color:#ff4fa3;
}

.menu .active{
    background:#ff4fa3;
    color:white;
}

/* LOGOUT */
.logout-btn{
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px 18px;
    border-radius:14px;
    color:#ff4fa3;
    text-decoration:none;
    margin-top:15px;
}

/* CONTENT */
.main-content{
    flex:1;
    margin-left:260px;
    padding:30px;
}

/* TOPBAR */
.topbar{
    display:flex;
    justify-content:space-between;
    margin-bottom:30px;
}

.page-title h1{
    font-size:36px;
}

.page-title p{
    color:#777;
}

/* TABLE */
.table-box{
    background:white;
    border-radius:22px;
    padding:25px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    text-align:left;
    color:#999;
    padding-bottom:15px;
}

table td{
    padding:18px 0;
    border-top:1px solid #f3f3f3;
}

/* BADGE */
.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:13px;
    font-weight:bold;
}

.active{
    background:#e4fff0;
    color:#23b26d;
}

.expired{
    background:#ffe4e4;
    color:#ff4d4d;
}

/* BUTTON */
.add-btn{
    background:#ff4fa3;
    color:white;
    border:none;
    padding:12px 18px;
    border-radius:12px;
    cursor:pointer;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.admin-box{
    display:flex;
    align-items:center;
    gap:12px;
    cursor:pointer;
}

.admin-avatar{
    width:45px;
    height:45px;
    border-radius:50%;
    object-fit:cover;
}

.admin-info{
    display:flex;
    flex-direction:column;
    justify-content:center;
    line-height:1.2;
}

.admin-name{
    font-weight:600;
    font-size:15px;
    color:#000;
}

.admin-role{
    color:#777;
    font-size:13px;
}

.admin-icon{
    margin-left:6px;
    color:#555;
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

            <a href="promotions.php" class="active">
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

            <a href="#">
                <i class="fa-regular fa-folder"></i>
                Danh mục
            </a>

            <a href="#">
                <i class="fa-regular fa-image"></i>
                Banner
            </a>

            <a href="#">
                <i class="fa-regular fa-file-lines"></i>
                Bài viết
            </a>

            <div class="menu-title">
                HỆ THỐNG
            </div>

            <a href="#">
                <i class="fa-solid fa-gear"></i>
                Cài đặt
            </a>

            <a href="#">
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

<!-- CONTENT -->
<div class="main-content">

    <div class="topbar">

        <div class="page-title">
            <h1>Quản lý khuyến mãi</h1>
            <p>Quản lý mã giảm giá hệ thống</p>
        </div>

        <div class="admin-box">
            <img src="https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg?semt=ais_hybrid&w=740&q=80" class="admin-avatar">
            <div class="admin-info">
                <div class="admin-name">Admin</div>
                <div class="admin-role">Quản trị viên</div>
            </div>
            <i class="fa-solid fa-chevron-down admin-icon"></i>
        </div>
    </div>

    <div class="header">
        <h2>Danh sách khuyến mãi</h2>
        <button class="add-btn">+ Thêm mã</button>
    </div>

    <div class="table-box">

        <table>

            <tr>
                <th>Mã</th>
                <th>Loại</th>
                <th>Giá trị</th>
                <th>Số lượng</th>
                <th>Đã dùng</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>

            <?php foreach ($promotions as $p) { ?>

            <tr>

                <td><strong><?php echo $p['code']; ?></strong></td>

                <td>
                    <?php echo $p['discount_type']; ?>
                </td>

                <td>
                    <?php echo $p['discount_value']; ?>
                </td>

                <td>
                    <?php echo $p['quantity']; ?>
                </td>

                <td>
                    <?php echo $p['used_count']; ?>
                </td>

                <td>
                    <?php echo $p['start_date']; ?> → <?php echo $p['end_date']; ?>
                </td>

                <td>
                    <span class="badge <?php echo $p['status']; ?>">
                        <?php echo $p['status']; ?>
                    </span>
                </td>

                 <td>
                    <div style="display:flex; gap:10px;">
                        <!-- SỬA -->
                        <a href="edit_promotion.php?id=<?php echo $p['promotion_id']; ?>">
                            <button style="
                                width:38px;
                                height:38px;
                                border:none;
                                border-radius:10px;
                                background:#ffb400;
                                color:white;
                                cursor:pointer;">
                                <i class="fa fa-pen"></i>
                            </button>
                        </a>

                        <!-- XÓA -->
                        <a href="promotions.php?delete=<?php echo $p['promotion_id']; ?>"
                        onclick="return confirm('Bạn có chắc muốn xóa mã này?')">

                            <button style="
                                width:38px;
                                height:38px;
                                border:none;
                                border-radius:10px;
                                background:#ff4d6d;
                                color:white;
                                cursor:pointer;">
                                <i class="fa fa-trash"></i>
                            </button>
                        </a>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
</div>
</body>
</html>