<?php
require_once '../../config/database.php';

/* ===== KPI ===== */
$stmt = $conn->query('SELECT COUNT(order_id) AS orders FROM orders');
$orders = $stmt->fetch(PDO::FETCH_ASSOC)['orders'] ?? 0;

$stmt = $conn->query('SELECT SUM(total_price) AS revenue FROM orders');
$revenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;

$stmt = $conn->query('
    SELECT COUNT(order_id) AS new_orders
    FROM orders
    WHERE DATE(created_at) = CURDATE()
');
$newOrders = $stmt->fetch(PDO::FETCH_ASSOC)['new_orders'] ?? 0;

$stmt = $conn->query('
    SELECT COUNT(DISTINCT receiver_phone) AS customers 
    FROM orders
');
$customers = $stmt->fetch(PDO::FETCH_ASSOC)['customers'] ?? 0;

/* ===== CHART 7 DAYS ===== */
$stmt = $conn->query('
    SELECT 
        DATE(created_at) AS date,
        SUM(total_price) AS revenue
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
');

$labels = [];
$values = [];

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $labels[] = $row['date'];
    $values[] = (float) $row['revenue'];
}

/* ===== TOP PRODUCTS ===== */
$stmt = $conn->query('
    SELECT 
        name AS product_name,
        SUM(quantity) AS sold
    FROM order_details
    GROUP BY name
    ORDER BY sold DESC
    LIMIT 3
');

$topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== RECENT ORDERS ===== */
$stmt = $conn->query('
    SELECT 
        o.order_id,
        o.receiver_name,
        o.created_at,
        o.status,
        o.total_price,
        od.name AS product_name,
        od.quantity
    FROM orders o
    JOIN order_details od ON o.order_id = od.order_id
    ORDER BY o.created_at DESC, o.order_id DESC
    LIMIT 50
');
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== ORDER STATUS ===== */

$pending = $conn->query("
    SELECT COUNT(*)
    FROM orders
    WHERE status='pending'
")->fetchColumn();

$confirmed = $conn->query("
    SELECT COUNT(*)
    FROM orders
    WHERE status='confirmed'
")->fetchColumn();

$shipping = $conn->query("
    SELECT COUNT(*)
    FROM orders
    WHERE status='shipping'
")->fetchColumn();

$delivered = $conn->query("
    SELECT COUNT(*)
    FROM orders
    WHERE status='delivered'
")->fetchColumn();

$cancelled = $conn->query("
    SELECT COUNT(*)
    FROM orders
    WHERE status='cancelled'
")->fetchColumn();

$totalOrders = $pending + $confirmed + $shipping + $delivered + $cancelled;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* ===== RESET ===== */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#fff5f9;
    width:100%;
    overflow-x:hidden;
}

/* ===== LAYOUT ===== */
.admin-container{
    display:flex;
}

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

/* ===== CONTENT ===== */
.main-content{
    flex:1;
    margin-left:260px;
    padding:30px;
}

/* TOPBAR */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

.page-title h1{
    font-size:36px;
    margin-bottom:10px;
}

.page-title p{
    color:#777;
}

/* ADMIN */
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

/* CARDS */
.card-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-bottom:30px;
}

.card{
    background:white;
    border-radius:22px;
    padding:25px;
}

.card-icon{
    width:60px;
    height:60px;
    border-radius:50%;
    background:#fff0f7;
    color:#ff4fa3;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    margin-bottom:20px;
}

.card-title{
    color:#777;
}

.card-value{
    font-size:36px;
    font-weight:bold;
}

/* DASHBOARD */
.dashboard-row{
    display:flex;
    flex-direction:column;
    gap:20px;
}

.chart-box{
    background:white;
    border-radius:22px;
    padding:25px;
    height:400px;
}

#revenueChart{
    width:100% !important;
    height:320px !important;
}

.product-box{
    background:white;
    border-radius:22px;
    padding:25px;
}

.section-title{
    font-size:18px;
    font-weight:700;
    margin-bottom:18px;
}

.product-item{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px;
    border-radius:14px;
    margin-bottom:12px;
    transition:0.2s;
}

.product-item:hover{
    background:#fff0f7;
}

.rank{
    width:28px;
    height:28px;
    border-radius:8px;
    background:#ff4fa3;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:13px;
    font-weight:bold;
}

.product-item img{
    width:55px;
    height:55px;
    border-radius:12px;
    object-fit:cover;
}

.product-info{
    flex:1;
}

.name{
    font-weight:600;
    margin-bottom:4px;
}

.sold{
    font-size:12px;
    color:#777;
    margin-bottom:6px;
}

.bar{
    width:100%;
    height:6px;
    background:#f1f1f1;
    border-radius:10px;
    overflow:hidden;
}

.bar-fill{
    height:100%;
    background:#ff4fa3;
    border-radius:10px;
}

.order-box{
    background:white;
    border-radius:22px;
    padding:25px;
    overflow-x:auto;
}

.order-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:14px 16px;
    border-radius:14px;
    margin-bottom:10px;
    transition:0.2s;
    border:1px solid #f3f3f3;
}

.order-item:hover{
    background:#fff0f7;
    transform:translateY(-2px);
}

.order-id{
    font-weight:700;
    color:#ff4fa3;
    width:70px;
}

.order-info{
    flex:1;
}

.customer{
    font-weight:600;
    margin-bottom:3px;
}

.date{
    font-size:12px;
    color:#777;
}

.order-price{
    font-weight:700;
    color:#23b26d;
}

.product{
    font-size:13px;
    color:#555;
    margin-top:3px;
}

.qty{
    font-size:12px;
    color:#777;
}

.order-status{
    padding:6px 10px;
    border-radius:12px;
    font-size:12px;
    font-weight:600;
    background:#e7f1ff;
    color:#2196f3;
    margin-right:10px;
}

.status-pending{
    background:#fff3cd;
    color:#856404;
}

.status-confirmed{
    background:#d4edda;
    color:#155724;
}

.status-shipping{
    background:#d1ecf1;
    color:#0c5460;
}

.status-delivered{
    background:#e7d6ff;
    color:#6f42c1;
}

.order-table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

.order-table th{
    text-align:left;
    padding:12px;
    font-size:13px;
    color:#999;
    border-bottom:1px solid #eee;
}

.order-table td{
    padding:14px 12px;
    border-bottom:1px solid #f5f5f5;
    font-size:14px;
}

.order-table tr:hover{
    background:#fff0f7;
}

.bottom-row{
    display:grid;
    grid-template-columns:1.5fr 1fr;
    gap:20px;
}

.stats-box{
    background:#fff;
    border-radius:22px;
    padding:25px;
}

.stats-header{
    font-size:22px;
    font-weight:700;
    margin-bottom:25px;
}

.stats-content{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
}

.donut-wrap{
    width:220px;
    height:220px;
    position:relative;
}

.total-center{
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    text-align:center;
}

.total-center h2{
    font-size:28px;
}

.total-center p{
    color:#777;
    font-size:14px;
}

.stats-list{
    flex:1;
}

.stats-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
}

.stats-left{
    display:flex;
    align-items:center;
    gap:10px;
}

.dot{
    width:12px;
    height:12px;
    border-radius:50%;
}

.dot1{background:#ff4fa3;}
.dot2{background:#ff7eb8;}
.dot3{background:#ff9dc9;}
.dot4{background:#ffc4dd;}
.dot5{background:#ff6b6b;}

.report-btn{
    margin-top:25px;
    background:#fff0f7;
    color:#ff4fa3;
    padding:16px 20px;
    border-radius:16px;
    font-weight:600;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.top-row{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
}

.status-processing{
    background:#fff1f6;
    color:#ff4fa3;
}

.status-shipping{
    background:#eef6ff;
    color:#2196f3;
}

.status-completed{
    background:#ecfff4;
    color:#23b26d;
}

.status-cancelled{
    background:#fff0f0;
    color:#e74c3c;
}

</style>

</head>

<body>

<div class="admin-container">

<!-- =========================================================
     SIDEBAR GIỮ NGUYÊN 100% TỪ FILE PRODUCTS CỦA BẠN
     ========================================================= -->
<div class="sidebar">

        <a href="admin_dashboard.php" class="logo">
            HAN STORE
        </a>

        <div class="sidebar-content">
            <div class="menu">

                <div class="menu-title">
                    MENU CHÍNH
                </div>

                <a class="active">
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

                <a href="categories.php">
                    <i class="fa-regular fa-folder"></i>
                    Danh mục
                </a>

                <a href="#" class="sidebar-item">
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

                <a href="#" class="sidebar-item">
                    <i class="fa-solid fa-gear"></i>
                    Cài đặt
                </a>

                <a href="account.php" class="sidebar-item">
                    <i class="fa-regular fa-user"></i>
                    Tài khoản
                </a>

                <a href="#" class="sidebar-item">
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
            <h1>Dashboard</h1>
            <p>Tổng quan hệ thống</p>
        </div>

        <!-- ADMIN ACCOUNT -->
        <div class="admin-box">
            <img src="https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg?semt=ais_hybrid&w=740&q=80">
            <div>
                <strong>Admin</strong><br>
                <small>Quản trị viên</small>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
    </div>

    <!-- CARDS -->
    <div class="card-grid">

        <div class="card">
            <div class="card-icon"><i class="fa fa-dollar-sign"></i></div>
            <div class="card-title">Doanh thu</div>
            <div class="card-value"><?php echo number_format($revenue); ?> đ</div>
        </div>

        <div class="card">
            <div class="card-icon"><i class="fa fa-cart-shopping"></i></div>
            <div class="card-title">Đơn hàng</div>
            <div class="card-value"><?php echo $orders; ?></div>
        </div>

        <div class="card">
            <div class="card-icon"><i class="fa fa-cart-plus"></i></div>
            <div class="card-title">Đơn mới</div>
            <div class="card-value"><?php echo $newOrders; ?></div>
        </div>

        <div class="card">
            <div class="card-icon"><i class="fa fa-user"></i></div>
            <div class="card-title">Khách hàng</div>
            <div class="card-value"><?php echo $customers; ?></div>
        </div>

    </div>

    <!-- CHART + PRODUCT -->
    <div class="dashboard-row">
        <div class="top-row">

            <div class="chart-box">
                <div class="chart-title">Doanh thu 7 ngày</div>
                <div style="height:320px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="product-box">
                <div class="section-title">
                    Top sản phẩm nổi bật
                </div>

                <?php foreach ($topProducts as $index => $p) { ?>
                    <div class="product-item">
                        <div class="rank">
                            <?php echo $index + 1; ?>
                        </div>
                        <img src="https://picsum.photos/seed/<?php echo $p['product_name']; ?>/100">
                        <div class="product-info">
                            <div class="name">
                                <?php echo htmlspecialchars($p['product_name']); ?>
                            </div>

                            <div class="sold">
                                Đã bán: <?php echo $p['sold']; ?> sản phẩm
                            </div>

                            <div class="bar">
                                <div class="bar-fill" style="width: <?php echo min(100, $p['sold'] * 5); ?>%"></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="bottom-row">
            <!-- ĐƠN HÀNG -->
            <div class="order-box">
                <div class="section-title">
                    Đơn hàng gần đây
                </div>

                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Sản phẩm</th>
                            <th>SL</th>
                            <th>Ngày đặt</th>
                            <th>Trạng thái</th>
                            <th>Tổng tiền</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($recentOrders as $o) { ?>
                        <tr>
                            <td>
                                #<?php echo $o['order_id']; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($o['receiver_name']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($o['product_name']); ?>
                            </td>

                            <td>
                                <?php echo $o['quantity']; ?>
                            </td>

                            <td>
                                <?php echo date('d/m/Y', strtotime($o['created_at'])); ?>
                            </td>

                            <td>
                                <span class="order-status status-<?php echo $o['status']; ?>">
                                    <?php echo ucfirst($o['status']); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo number_format($o['total_price']); ?> đ
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- THỐNG KÊ -->
            <div class="stats-box">

                <div class="stats-header">
                    Thống kê đơn hàng
                </div>

                <div class="stats-content">

                    <div class="donut-wrap">
                        <canvas id="orderChart"></canvas>

                        <div class="total-center">
                            <h2><?php echo $totalOrders; ?></h2>
                            <p>Tổng đơn</p>
                        </div>
                    </div>

                    <div class="stats-list">

                        <div class="stats-item">
                            <div class="stats-left">
                                <span class="dot dot1"></span>
                                Chờ xác nhận
                            </div>
                            <strong>
                                <?php
                                echo $pending.' ('.
                                round(($pending / max($totalOrders, 1)) * 100, 1)
                                .'%)'; ?>
                            </strong>
                        </div>

                        <div class="stats-item">
                            <div class="stats-left">
                                <span class="dot dot2"></span>
                                Đang giao
                            </div>
                            <strong>
                                <?php
                                echo $shipping.' ('.
                                round(($shipping / max($totalOrders, 1)) * 100, 1)
                                .'%)'; ?>
                            </strong>
                        </div>

                        <div class="stats-item">
                            <div class="stats-left">
                                <span class="dot dot3"></span>
                                Hoàn thành
                            </div>
                            <strong>
                                <?php
                                echo $delivered.' ('.
                                round(($delivered / max($totalOrders, 1)) * 100, 1)
                                .'%)'; ?>
                            </strong>
                        </div>

                        <div class="stats-item">
                            <div class="stats-left">
                                <span class="dot dot4"></span>
                                Đã xác nhận
                            </div>
                            <strong>
                                <?php
                                echo $confirmed.' ('.
                                round(($confirmed / max($totalOrders, 1)) * 100, 1)
                                .'%)'; ?>
                            </strong>
                        </div>

                        <div class="stats-item">
                            <div class="stats-left">
                                <span class="dot dot5"></span>
                                Đã hủy
                            </div>

                            <strong>
                                <?php
                                echo $cancelled.' ('.
                                round(($cancelled / max($totalOrders, 1)) * 100, 1)
                                .'%)';
?>
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="report-btn">
                    Xem báo cáo chi tiết
                    <i class="fa-solid fa-arrow-right"></i>
                </div>

            </div>

        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById("revenueChart"), {
        type: "line",
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                data: <?php echo json_encode($values); ?>,
                borderColor: "#ff4fa3",
                fill: true
            }]
        }
    });

    new Chart(document.getElementById("orderChart"), {
        type: "doughnut",
        data: {
            labels: [
                "Chờ xác nhận",
                "Đã xác nhận",
                "Đang giao",
                "Hoàn thành",
                "Đã hủy"
            ],
            datasets: [{
                data: [
                    <?php echo $pending; ?>,
                    <?php echo $confirmed; ?>,
                    <?php echo $shipping; ?>,
                    <?php echo $delivered; ?>,
                    <?php echo $cancelled; ?>
                ],
                backgroundColor:[
                    "#ff4fa3", // pending
                    "#ff7eb8", // confirmed
                    "#ff9dc9", // shipping
                    "#ffc4dd", // delivered
                    "#ff6b6b"  // cancelled
                ],
                borderWidth:0
            }]
        },
        options:{
            cutout:"70%",
            plugins:{
                legend:{
                    display:false
                }
            }
        }
    });
</script>

</body>
</html>