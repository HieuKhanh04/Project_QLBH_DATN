<?php
session_start();

require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    exit('ID người dùng không hợp lệ.');
}

/*  LẤY THÔNG TIN USER */
$stmt = $conn->prepare('
    SELECT *
    FROM users
    WHERE user_id = ?
    LIMIT 1
    ');
$stmt->execute([$id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    exit('Không tìm thấy người dùng.');
}

/*  THỐNG KÊ */
// Tổng đơn
$stmt = $conn->prepare('
    SELECT COUNT(*) 
    FROM orders
    WHERE user_id=?
    ');
$stmt->execute([$id]);
$totalOrders = $stmt->fetchColumn();

// Tổng chi tiêu
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(total_price),0)
    FROM orders
    WHERE user_id=?
    AND status='delivered'
    ");
$stmt->execute([$id]);
$totalSpent = $stmt->fetchColumn();

// Đơn hoàn thành
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM orders
    WHERE user_id=?
    AND status='delivered'
    ");
$stmt->execute([$id]);
$completedOrders = $stmt->fetchColumn();

// Đơn đã hủy
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM orders
    WHERE user_id=?
    AND status='cancelled'
    ");
$stmt->execute([$id]);
$cancelOrders = $stmt->fetchColumn();

/*  ĐƠN GẦN NHẤT */

$stmt = $conn->prepare('
    SELECT *
    FROM orders
    WHERE user_id=?
    ORDER BY created_at DESC
    LIMIT 10
    ');
$stmt->execute([$id]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

function orderStatus($status)
{
    switch ($status) {
        case 'pending':
            return 'Chờ xác nhận';
        case 'confirmed':
            return 'Đã xác nhận';
        case 'shipping':
            return 'Đang giao';
        case 'delivered':
            return 'Đã giao';
        case 'cancelled':
            return 'Đã hủy';
        default:
            return ucfirst($status);
    }
}
function statusClass($status)
{
    switch ($status) {
        case 'pending':
            return 'pending';
        case 'confirmed':
            return 'confirmed';
        case 'shipping':
            return 'shipping';
        case 'delivered':
            return 'delivered';
        case 'cancelled':
            return 'cancelled';
        default:
            return '';
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>
    Chi tiết tài khoản
    </title>
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap"
    rel="stylesheet">

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

    /* SIDEBAR  */
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
    /*CONTENT*/
    .main-content{
        flex:1;
        margin-left:260px;
        padding:30px;
    }

    .topbar{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:25px;
    }

    .page-title h1{
        font-size:36px;
        color:#222;
        margin-bottom:10px;
    }

    .page-title p{
        color:#777;
        margin-top:8px;
    }

    .back-btn{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:14px 22px;
        background:#ff4fa3;
        color:#fff;
        text-decoration:none;
        border-radius:12px;
    }

    .back-btn:hover{
        opacity:.9;
    }

    /* PROFILE */
    .profile-wrapper{
        display:flex;
        gap:25px;
        align-items:flex-start;
    }

    .profile-card{
        width:320px;
        background:#fff;
        border-radius:22px;
        padding:30px;
        text-align:center;
        box-shadow:0 5px 18px rgba(0,0,0,.05);
    }

    .avatar{
        width:140px;
        height:140px;
        border-radius:50%;
        object-fit:cover;
        border:6px solid #ffe1ef;
        margin-bottom:18px;
    }

    .profile-card h2{
        font-size:24px;
        color:#333;
        margin-bottom:12px;
    }

    .badge{
        display:inline-block;
        padding:8px 16px;
        border-radius:30px;
        font-size:13px;
        font-weight:bold;
        margin-bottom:25px;
    }

    .badge.active{
        background:#e7fff1;
        color:#20b96d;
    }

    .badge.banned{
        background:#ffe7ea;
        color:#ff4d6d;
    }

    .info-table{
        width:100%;
        border-collapse:collapse;
        text-align:left;
    }

    .info-table td{
        padding:12px 0;
        border-bottom:1px solid #f3f3f3;
        font-size:14px;
    }

    .info-table td:first-child{
        width:95px;
        color:#888;
        font-weight:bold;
    }

    .info-table td:last-child{
        color:#333;
        word-break:break-word;
    }

    /* RIGHT */
    .profile-content{
        flex:1;
    }

    /* STATISTIC */
    .stats-grid{
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:20px;
        margin-bottom:25px;
    }

    .stat-card{
        background:#fff;
        border-radius:22px;
        padding:28px;
        text-align:center;
        box-shadow:0 5px 18px rgba(0,0,0,.05);
    }

    .stat-card i{
        font-size:28px;
        color:#ff4fa3;
        margin-bottom:15px;
    }

    .stat-card h2{
        font-size:30px;
        color:#333;
        margin-bottom:8px;
    }

    .stat-card p{
        color:#888;
        font-size:14px;
    }

    /* ORDER */
    .order-card{
        background:#fff;
        border-radius:22px;
        padding:25px;
        box-shadow:0 5px 18px rgba(0,0,0,.05);
    }

    .card-title{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:20px;
    }

    .card-title h2{
        font-size:24px;
        color:#333;
    }

    .order-table{
        width:100%;
        border-collapse:collapse;
    }

    .order-table th{
        text-align:left;
        color:#999;
        padding:16px 12px;
        font-size:14px;
    }

    .order-table td{
        padding:16px 12px;
        border-top:1px solid #f2f2f2;
        font-size:14px;
        vertical-align:middle;
    }

    .order-table tr:hover{
        background:#fff8fb;
    }

    /* STATUS */
    .status{
        display:inline-block;
        padding:7px 14px;
        border-radius:30px;
        font-size:12px;
        font-weight:bold;
    }

    .pending{
        background:#fff6de;
        color:#d49800;
    }

    .confirmed{
        background:#e8f2ff;
        color:#2274ff;
    }

    .shipping{
        background:#eef6ff;
        color:#0f86ff;
    }

    .delivered{
        background:#e8fff2;
        color:#19aa61;
    }

    .cancelled{
        background:#ffe7ea;
        color:#ff4d6d;
    }

    /* RESPONSIVE */
    @media(max-width:1300px){
        .stats-grid{
            grid-template-columns:repeat(2,1fr);
        }
    }

    @media(max-width:1000px){
        .profile-wrapper{
            flex-direction:column;
        }
        .profile-card{
            width:100%;
        }
    }

    @media(max-width:700px){
        .stats-grid{
            grid-template-columns:1fr;
        }
        .order-card{
            overflow-x:auto;
        }
        .order-table{
            min-width:700px;
        }
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

                    <a href="user_account.php" class="active">
                        <i class="fa-solid fa-user"></i>
                        Tài khoản người dùng
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

                    <a href="collections.php">
                        <i class="fa-regular fa-images"></i>
                        Bộ sưu tập
                    </a>

                    <a href="notifications.php" class="sidebar-item">
                        <i class="fa-regular fa-bell"></i>
                        Thông báo
                    </a>

                    <a href="reviews.php">
                        <i class="fa-regular fa-star"></i>
                        Đánh giá
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

                    <a href="activity_logs.php">
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
                    <h1> Chi tiết tài khoản</h1>
                    <p> Thông tin chi tiết người dùng và lịch sử mua hàng</p>
                </div>

                <a
                    href="user_account.php"
                    class="back-btn">
                    <i class="fa fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>

            <!--  THÔNG TIN NGƯỜI DÙNG -->
            <div class="profile-wrapper">
                <!-- LEFT -->
                <div class="profile-card">
                    <?php
                    $avatar = !empty($user['avatar'])
                        ? $user['avatar']
                        : 'https://ui-avatars.com/api/?background=ff4fa3&color=fff&size=200&name='.urlencode($user['name']); ?>

                    <img
                        src="<?php echo htmlspecialchars($avatar); ?>"
                        class="avatar">

                    <h2>
                        <?php echo htmlspecialchars($user['name']); ?>
                    </h2>

                    <?php if ($user['status'] == 1) { ?>

                        <span class="badge active">
                            Hoạt động
                        </span>

                    <?php } else { ?>
                        <span class="badge banned">
                            Bị khóa
                        </span>
                    <?php } ?>

                    <table class="info-table">
                        <tr>
                            <td>Email</td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                        </tr>

                        <tr>
                            <td>SĐT</td>
                            <td>
                                <?php
                                    echo !empty($user['phone'])
                                    ? htmlspecialchars($user['phone'])
                                    : '-'; ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Địa chỉ</td>
                            <td>
                                <?php
                                    echo !empty($user['address'])
                                    ? htmlspecialchars($user['address'])
                                    : '-'; ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Vai trò</td>
                            <td>
                                <?php
                                    echo $user['role'] == 1
                                    ? 'Quản trị viên'
                                    : 'Khách hàng'; ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Ngày tạo</td>
                            <td>
                                <?php
                                    echo date(
                                        'd/m/Y H:i',
                                        strtotime($user['created_at'])
                                    ); ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- RIGHT -->
                <div class="profile-content">
                    <!-- THỐNG KÊ -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <h2>
                                <?php echo $totalOrders; ?>
                            </h2>
                            <p>Tổng đơn hàng</p>
                        </div>

                        <div class="stat-card">
                            <i class="fa-solid fa-circle-check"></i>
                            <h2>
                                <?php echo $completedOrders; ?>
                            </h2>
                            <p>Đơn hoàn thành</p>
                        </div>

                        <div class="stat-card">
                            <i class="fa-solid fa-ban"></i>
                            <h2>
                                <?php echo $cancelOrders; ?>

                            </h2>
                            <p>Đơn đã hủy</p>
                        </div>

                        <div class="stat-card">
                            <i class="fa-solid fa-wallet"></i>
                            <h2>
                                <?php echo number_format($totalSpent, 0, ',', '.'); ?>đ
                            </h2>
                            <p>Tổng chi tiêu</p>
                        </div>
                    </div>

                    <!-- ĐƠN HÀNG -->
                    <div class="order-card">
                        <div class="card-title">
                            <h2> 10 đơn hàng gần nhất </h2>
                        </div>

                        <table class="order-table">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái</th>
                            </tr>

                            <?php if (count($orders) == 0) { ?>
                            <tr>
                                <td colspan="5"
                                    style="text-align:center;padding:30px;color:#999;">
                                    Người dùng chưa có đơn hàng
                                </td>
                            </tr>

                            <?php } ?>
                            <?php foreach ($orders as $o) { ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($o['order_code']); ?>
                                </td>

                                <td>
                                    <?php echo date(
                                        'd/m/Y',
                                        strtotime($o['created_at'])
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo number_format(
                                        $o['total_price'],
                                        0,
                                        ',',
                                        '.'
                                    ); ?>đ
                                </td>

                                <td>
                                    <?php echo $o['payment_method']; ?>
                                </td>

                                <td>
                                    <span class="status <?php echo statusClass($o['status']); ?>">
                                        <?php echo orderStatus($o['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Avatar mặc định nếu ảnh lỗi
        document.querySelectorAll(".avatar").forEach(function(img){
            img.onerror=function(){
                this.src="https://ui-avatars.com/api/?background=ff4fa3&color=fff&size=300&name=User";
            };
        });

        // Hiệu ứng hover cho các card
        document.querySelectorAll(".stat-card").forEach(function(card){
            card.addEventListener("mouseenter",function(){
                this.style.transform="translateY(-4px)";
                this.style.transition=".25s";
            });
            card.addEventListener("mouseleave",function(){
                this.style.transform="translateY(0)";
            });
        });
    </script>
</body>
</html>

