<?php
require_once '../../config/database.php';

/* LẤY ĐƠN HÀNG */
$stmt = $conn->query('
    SELECT *
    FROM orders
    ORDER BY order_id DESC
');
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Quản lý đơn hàng</title>

<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* ================= RESET ================= */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#fff5f9;
}

/* ================= LAYOUT ================= */
.admin-container{
    display:flex;
}

/* ================= SIDEBAR (GIỮ NGUYÊN 100% PRODUCTS) ================= */
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
    transition:0.2s;
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

/* ================= CONTENT ================= */
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

/* TABLE */
.table-box{
    background:white;
    border-radius:22px;
    padding:25px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

.chart-title{
    font-size:24px;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    text-align:left;
    padding-bottom:15px;
    color:#999;
}

table td{
    padding:18px 0;
    border-top:1px solid #f3f3f3;
}

/* STATUS */
.status{
    padding:8px 14px;
    border-radius:30px;
    font-size:13px;
    font-weight:bold;
}

.processing{ background:#fff0d9; color:#ff9800; }
.shipping{ background:#e7f1ff; color:#2196f3; }
.success{ background:#e4fff0; color:#23b26d; }
.cancel{ background:#ffe4e4; color:#ff4d4d; }

</style>

</head>

<body>

<div class="admin-container">

<!-- SIDEBAR (GIỮ NGUYÊN 100% TỪ PRODUCTS.PHP) -->
<div class="sidebar">

    <a href="admin_dashboard.php" class="logo">
        HAN STORE
    </a>

    <div class="sidebar-content">
        <div class="menu">

            <div class="menu-title">MENU CHÍNH</div>

            <a href="admin_dashboard.php">
                <i class="fa-solid fa-chart-line"></i>
                Dashboard
            </a>

            <a href="products.php">
                <i class="fa-solid fa-shirt"></i>
                Sản phẩm
            </a>

            <a href="orders.php" class="active">
                <i class="fa-solid fa-cart-shopping"></i>
                Đơn hàng
            </a>

            <a href="#">
                <i class="fa-solid fa-users"></i>
                Khách hàng
            </a>

            <a href="#">
                <i class="fa-solid fa-tags"></i>
                Khuyến mãi
            </a>

            <a href="#">
                <i class="fa-solid fa-chart-pie"></i>
                Báo cáo
            </a>

            <div class="menu-title">QUẢN LÝ NỘI DUNG</div>

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

            <div class="menu-title">HỆ THỐNG</div>

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

<!-- CONTENT (GIỮ NGUYÊN) -->
<div class="main-content">

    <div class="topbar">

        <div class="page-title">
            <h1>Quản lý đơn hàng</h1>
            <p>Theo dõi toàn bộ đơn hàng cửa hàng</p>
        </div>

        <div class="admin-box">
            <img src="https://i.pravatar.cc/100">
            <div>
                <strong>Admin</strong><br>
                <small>Quản trị viên</small>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </div>

    </div>

    <div class="table-box">

        <div class="chart-title">Đơn hàng gần đây</div>

        <table>

            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>SĐT</th>
                <th>Ngày</th>
                <th>Trạng thái</th>
                <th>Tổng tiền</th>
            </tr>

            <?php foreach ($orders as $o) { ?>

            <tr>

                <td>#<?php echo $o['order_id']; ?></td>
                <td><?php echo $o['receiver_name']; ?></td>
                <td><?php echo $o['receiver_phone']; ?></td>
                <td><?php echo $o['created_at']; ?></td>

                <td>
                    <?php
                        $status = $o['status'];
                $class = match ($status) {
                    'pending' => 'processing',
                    'shipping' => 'shipping',
                    'done' => 'success',
                    default => 'cancel'
                };
                ?>

                    <span class="status <?php echo $class; ?>">
                        <?php echo $status; ?>
                    </span>
                </td>

                <td>
                    <?php echo number_format($o['total_price']); ?> đ
                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</div>

</body>
</html>