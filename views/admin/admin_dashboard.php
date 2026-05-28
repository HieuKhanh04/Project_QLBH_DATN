<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

/* MAIN */
.admin-container{
    display:flex;
    min-height:100vh;
}

/* SIDEBAR */
.sidebar{

    width:260px;

    background:white;

    padding:30px 20px;

    border-right:1px solid #ffd9ea;

    display:flex;

    flex-direction:column;

    position:fixed;

    top:0;

    left:0;

    height:100vh;

    overflow:hidden;
}

.sidebar-content{

    flex:1;

    overflow-y:auto;

    padding-right:5px;
}

/* LOGO */
.logo{

    font-family: 'Great Vibes', cursive;
    font-size: 42px;
    color: #ff4fa3;
    font-weight: bold;
    text-shadow: 0 2px 6px rgba(255, 79, 163, 0.3);
    text-decoration: none;
}

/* MENU */
.menu-title{

    color:#999;

    font-size:13px;

    margin:25px 0 15px;
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

/* ACTIVE */
.menu .active{

    background:#ff4fa3;

    color:white;
}

/* CONTENT */
.main-content{

    flex:1;

    padding:30px;

    margin-left:260px;

    width:calc(100% - 260px);
}

/* TOP BAR */
.topbar{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:30px;
}

/* TITLE */
.page-title h1{

    font-size:36px;

    margin-bottom:10px;
}

.page-title p{

    color:#777;
}

/* ADMIN INFO */
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

/* CARD GRID */
.card-grid{

    display:grid;

    grid-template-columns:repeat(4,1fr);

    gap:20px;

    margin-bottom:30px;
}

/* CARD */
.card{

    background:white;

    border-radius:22px;

    padding:25px;

    box-shadow:0 4px 12px rgba(0,0,0,0.05);
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

    margin-bottom:10px;
}

.card-value{

    font-size:36px;

    font-weight:bold;

    margin-bottom:10px;
}

.card-growth{

    color:#23b26d;

    font-size:14px;
}

/* CHART AREA */
.dashboard-row{

    display:grid;

    grid-template-columns:2fr 1fr;

    gap:20px;

    margin-bottom:30px;
}

/* CHART */
.chart-box{

    background:white;

    border-radius:22px;

    padding:25px;

    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

.chart-title{

    font-size:24px;

    margin-bottom:30px;
}

/* FAKE CHART */
.chart{

    display:flex;

    align-items:flex-end;

    gap:25px;

    height:300px;
}

.bar{

    flex:1;

    background:#ffd6e8;

    border-radius:12px 12px 0 0;
}

.bar.active{

    background:#ff4fa3;
}

/* PRODUCT BOX */
.product-box{

    background:white;

    border-radius:22px;

    padding:25px;

    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

.product-item{

    display:flex;

    align-items:center;

    gap:15px;

    margin-bottom:20px;
}

.product-item img{

    width:70px;
    height:70px;

    border-radius:14px;

    object-fit:cover;
}

.product-info h4{

    margin-bottom:8px;
}

.product-info p{

    color:#ff4fa3;

    font-weight:bold;
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

.processing{

    background:#fff0d9;

    color:#ff9800;
}

.shipping{

    background:#e7f1ff;

    color:#2196f3;
}

.success{

    background:#e4fff0;

    color:#23b26d;
}

.menu-title{

    margin:30px 0 15px;

    font-size:13px;

    color:#999;

    font-weight:600;
}

.logout-btn{

    display:flex;

    align-items:center;

    gap:12px;

    padding:14px 18px;

    border-radius:14px;

    color:#ff4fa3;

    text-decoration:none;

    transition:0.2s;

    margin-top:15px;

    flex-shrink:0;
}

.logout-btn:hover{

    background:#fff0f7;
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

                <a href="admin_dashboard.php" class="active">
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

                <div class="menu-title">
                    QUẢN LÝ NỘI DUNG
                </div>  
                <a href="#" class="sidebar-item">
                    <i class="fa-regular fa-folder"></i>
                    Danh mục
                </a>

                <a href="#" class="sidebar-item">
                    <i class="fa-regular fa-image"></i>
                    Banner
                </a>

                <a href="#" class="sidebar-item">
                    <i class="fa-regular fa-file-lines"></i>
                    Bài viết
                </a>

                <div class="menu-title">
                    HỆ THỐNG
                </div>

                <a href="#" class="sidebar-item">
                    <i class="fa-solid fa-gear"></i>
                    Cài đặt
                </a>

                <a href="#" class="sidebar-item">
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

        <!-- TOPBAR -->
        <div class="topbar">

            <div class="page-title">

                <h1>
                    Chào mừng trở lại, Admin!
                </h1>

                <p>
                    Đây là tổng quan hoạt động cửa hàng hôm nay
                </p>

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

        <!-- CARDS -->
        <div class="card-grid">

            <div class="card">

                <div class="card-icon">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>

                <div class="card-title">
                    Tổng doanh thu
                </div>

                <div class="card-value">
                    $100.450
                </div>

                <div class="card-growth">
                    +12.5%
                </div>

            </div>

            <div class="card">

                <div class="card-icon">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>

                <div class="card-title">
                    Tổng đơn hàng
                </div>

                <div class="card-value">
                    5.000
                </div>

                <div class="card-growth">
                    +8.7%
                </div>

            </div>

            <div class="card">

                <div class="card-icon">
                    <i class="fa-solid fa-cart-plus"></i>
                </div>

                <div class="card-title">
                    Đơn mới
                </div>

                <div class="card-value">
                    65
                </div>

                <div class="card-growth">
                    +15.3%
                </div>

            </div>

            <div class="card">

                <div class="card-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div class="card-title">
                    Khách hàng
                </div>

                <div class="card-value">
                    320
                </div>

                <div class="card-growth">
                    +6.4%
                </div>

            </div>

        </div>

        <!-- CHART + PRODUCT -->
        <div class="dashboard-row">

            <!-- CHART -->
            <div class="chart-box">

                <div class="chart-title">
                    Doanh thu
                </div>

                <div class="chart">

                    <div class="bar" style="height:120px"></div>

                    <div class="bar" style="height:150px"></div>

                    <div class="bar" style="height:170px"></div>

                    <div class="bar" style="height:180px"></div>

                    <div class="bar" style="height:220px"></div>

                    <div class="bar active" style="height:260px"></div>

                </div>

            </div>

            <!-- PRODUCT -->
            <div class="product-box">

                <div class="chart-title">
                    Top sản phẩm
                </div>

                <div class="product-item">

                    <img src="https://picsum.photos/100?1">

                    <div class="product-info">
                        <h4>Áo Hoodie</h4>
                        <p>Đã bán: 456</p>
                    </div>

                </div>

                <div class="product-item">

                    <img src="https://picsum.photos/100?2">

                    <div class="product-info">
                        <h4>Áo Sweater</h4>
                        <p>Đã bán: 332</p>
                    </div>

                </div>

                <div class="product-item">

                    <img src="https://picsum.photos/100?3">

                    <div class="product-info">
                        <h4>Quần Jean</h4>
                        <p>Đã bán: 245</p>
                    </div>

                </div>

            </div>

        </div>

        <!-- TABLE -->
        <div class="table-box">

            <div class="chart-title">
                Đơn hàng gần đây
            </div>

            <table>

                <tr>

                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Sản phẩm</th>
                    <th>Ngày</th>
                    <th>Trạng thái</th>
                    <th>Tổng tiền</th>

                </tr>

                <tr>

                    <td>#089508</td>
                    <td>Nguyễn Văn A</td>
                    <td>Áo Hoodie</td>
                    <td>30/11/2025</td>

                    <td>
                        <span class="status processing">
                            Đang xử lý
                        </span>
                    </td>

                    <td>$249</td>

                </tr>

                <tr>

                    <td>#089507</td>
                    <td>Trần Thị B</td>
                    <td>Áo Sweater</td>
                    <td>29/11/2025</td>

                    <td>
                        <span class="status shipping">
                            Đang giao
                        </span>
                    </td>

                    <td>$130</td>

                </tr>

                <tr>

                    <td>#089506</td>
                    <td>Lê Văn C</td>
                    <td>Quần Jean</td>
                    <td>28/11/2025</td>

                    <td>
                        <span class="status success">
                            Hoàn thành
                        </span>
                    </td>

                    <td>$89</td>

                </tr>

            </table>

        </div>

    </div>

</div>

</body>
</html>