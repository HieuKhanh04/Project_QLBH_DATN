<?php
require_once '../../config/database.php';

/* LẤY KHÁCH HÀNG (từ orders - vì bạn chưa có bảng users) */
$stmt = $conn->query('
    SELECT 
        receiver_name,
        receiver_phone,
        COUNT(order_id) AS total_orders,
        SUM(total_price) AS total_spent,
        MAX(created_at) AS last_order
    FROM orders
    GROUP BY receiver_phone, receiver_name
    ORDER BY last_order DESC
');
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Quản lý khách hàng</title>

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

/* SIDEBAR (GIỮ NGUYÊN STYLE PRODUCTS) */
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

/* SEARCH */
.search-box{
    margin-bottom:20px;
    display:flex;
    gap:10px;
}

.search-box input{
    width:300px;
    padding:10px 14px;
    border:1px solid #ddd;
    border-radius:12px;
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
    table-layout:fixed;
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

.badge{
    padding:6px 12px;
    border-radius:20px;
    background:#e7f1ff;
    color:#2196f3;
    font-size:13px;
}

</style>

</head>

<body>

<div class="admin-container">

<!-- SIDEBAR -->
<div class="sidebar">

    <a href="admin_dashboard.php" class="logo">HAN STORE</a>

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

            <a href="orders.php">
                <i class="fa-solid fa-cart-shopping"></i>
                Đơn hàng
            </a>

            <a href="customers.php" class="active">
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

            <div class="menu-title">QUẢN LÝ NỘI DUNG</div>

            <a href="categories.php">
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

<!-- CONTENT -->
<div class="main-content">

    <div class="topbar">

        <div class="page-title">
            <h1>Quản lý khách hàng</h1>
            <p>Danh sách khách hàng đã mua hàng</p>
        </div>

        <div class="admin-box">
            <img src="https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg?semt=ais_hybrid&w=740&q=80">
            <div>
                <strong>Admin</strong><br>
                <small>Quản trị viên</small>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </div>

    </div>

    <div class="table-box">

        <table>

            <tr>
                <th>Tên khách hàng</th>
                <th>Số điện thoại</th>
                <th>Số đơn</th>
                <th>Tổng chi tiêu</th>
                <th>Đơn gần nhất</th>
                <th>Thao tác</th>
            </tr>

            <?php foreach ($customers as $c) { ?>

            <tr>
                <td><?php echo htmlspecialchars($c['receiver_name']); ?></td>
                <td><?php echo htmlspecialchars($c['receiver_phone']); ?></td>

                <td>
                    <span class="badge">
                        <?php echo $c['total_orders']; ?>
                    </span>
                </td>

                <td>
                    <?php echo number_format($c['total_spent']); ?> đ
                </td>

                <td>
                    <?php echo date('d/m/Y H:i', strtotime($c['last_order'])); ?>
                </td>

                <td>
                    <a href="customer_orders.php?phone=<?php echo urlencode($c['receiver_phone']); ?>">
                        <button style="
                            background:#0d6efd;
                            color:white;
                            border:none;
                            padding:9px 18px;
                            border-radius:10px;
                            cursor:pointer;
                            font-weight:bold;
                        ">
                            Xem chi tiết
                        </button>
                    </a>
                </td>
            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</div>

</body>
</html>