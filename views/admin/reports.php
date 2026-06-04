<?php
require_once '../../config/database.php';

/* ===== KPI ===== */
$stmt = $conn->query('SELECT SUM(total_price) AS revenue FROM orders');
$revenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;

$stmt = $conn->query('SELECT COUNT(order_id) AS orders FROM orders');
$orders = $stmt->fetch(PDO::FETCH_ASSOC)['orders'] ?? 0;

$stmt = $conn->query('SELECT COUNT(DISTINCT receiver_phone) AS customers FROM orders');
$customers = $stmt->fetch(PDO::FETCH_ASSOC)['customers'] ?? 0;

/* ===== AVERAGE ORDER VALUE ===== */
$avg_order = ($orders > 0) ? $revenue / $orders : 0;

/* ===== PIE ANALYTICS ===== */
$pieLabels = ['Doanh thu', 'Đơn hàng', 'Khách hàng'];

/* scale để dễ nhìn (dashboard style) */
$pieValues = [
    $revenue / 1000000,
    $orders,
    $customers,
];

/* ===== LINE DATA ===== */
$stmt = $conn->query('
    SELECT DATE(created_at) date, SUM(total_price) revenue
    FROM orders
    GROUP BY DATE(created_at)
    ORDER BY date ASC
    LIMIT 7
');

$labels = [];
$values = [];

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $labels[] = $r['date'];
    $values[] = $r['revenue'];
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Báo cáo</title>

<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

/* RESET */
*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:Arial;
    background:#fff5f9;
}

/* LAYOUT */
.admin-container{display:flex;}

/* ================= SIDEBAR (GIỮ NGUYÊN 100% THEO MẪU) ================= */
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

/* ADMIN BOX (GIỮ NGUYÊN STYLE PROMOTIONS) */
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

/* KPI */
.kpi{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-bottom:25px;
}

.card{
    background:#fff;
    padding:18px;
    border-radius:18px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

/* CHART */
.chart-box{
    background:#fff;
    padding:20px;
    border-radius:18px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    margin-bottom:20px;
}

</style>

</head>

<body>

<div class="admin-container">

<!-- ================= SIDEBAR (ĐÃ FIX FULL QUẢN LÝ NỘI DUNG) ================= -->
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

            <a href="reports.php" class="active">
                <i class="fa-solid fa-chart-pie"></i>
                Báo cáo
            </a>

            <!-- 🔥 PHẦN BẠN BỊ THIẾU ĐÃ ĐƯỢC GIỮ NGUYÊN -->
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

<!-- ================= CONTENT ================= -->
<div class="main-content">

    <div class="topbar">

        <div class="page-title">
            <h1>Báo cáo</h1>
            <p>Phân tích doanh thu hệ thống</p>
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

    <!-- KPI -->
    <div class="kpi">

        <div class="card">
            <p>Doanh thu</p>
            <h2 style="color:#ff4fa3;"><?php echo number_format($revenue); ?> đ</h2>
        </div>

        <div class="card">
            <p>Đơn hàng</p>
            <h2 style="color:#2196f3;"><?php echo $orders; ?></h2>
        </div>

        <div class="card">
            <p>Khách hàng</p>
            <h2 style="color:#4caf50;"><?php echo $customers; ?></h2>
        </div>

    </div>

    <!-- ================= ANALYTICS DASHBOARD ================= -->

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">

    <!-- LINE CHART -->
    <div class="chart-box">
        <h3>📈 Xu hướng doanh thu (7 ngày)</h3>
        <canvas id="lineChart"></canvas>
    </div>

    <!-- PIE CHART -->
    <div class="chart-box">
        <h3>🥧 Cơ cấu hệ thống</h3>
        <canvas id="pieChart"></canvas>
    </div>

</div>

<!-- BAR + INSIGHT -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">

    <!-- BAR CHART -->
    <div class="chart-box">
        <h3>📊 So sánh doanh thu theo ngày</h3>
        <canvas id="barChart"></canvas>
    </div>

    <!-- INSIGHT PANEL -->
    <div class="chart-box">
        <h3>🧠 Phân tích nhanh</h3>

        <div style="margin-top:15px;display:flex;flex-direction:column;gap:15px;">

            <div>
                <p style="color:#777;">Giá trị đơn trung bình</p>
                <h2 style="color:#ff4fa3;">
                    <?php echo number_format($avg_order, 0); ?> đ
                </h2>
            </div>

            <div>
                <p style="color:#777;">Hiệu suất bán hàng</p>
                <h2 style="color:#2196f3;">
                    <?php echo $orders > 0 ? round($revenue / $orders / 1000, 1) : 0; ?>K / đơn
                </h2>
            </div>

            <div>
                <p style="color:#777;">Tăng trưởng giả lập</p>
                <h2 style="color:#4caf50;">+12.4%</h2>
            </div>

        </div>
    </div>

</div>

</div>

</div>

<script>
    /* ================= LINE ================= */
    new Chart(document.getElementById("lineChart"), {
        type: "line",
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: "Doanh thu",
                data: <?php echo json_encode($values); ?>,
                borderColor: "#ff4fa3",
                backgroundColor: "rgba(255,79,163,0.15)",
                fill: true,
                tension: 0.4
            }]
        }
    });

    /* ================= BAR ================= */
    new Chart(document.getElementById("barChart"), {
        type: "bar",
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: "Doanh thu theo ngày",
                data: <?php echo json_encode($values); ?>,
                backgroundColor: "#2196f3"
            }]
        }
    });

    /* ================= PIE ================= */
    new Chart(document.getElementById("pieChart"), {
        type: "doughnut",
        data: {
            labels: <?php echo json_encode($pieLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($pieValues); ?>,
                backgroundColor: ["#ff4fa3", "#2196f3", "#4caf50"]
            }]
        },
        options: {
            plugins: {
                legend: { position: "bottom" }
            }
        }
    });
</script>

</body>
</html>