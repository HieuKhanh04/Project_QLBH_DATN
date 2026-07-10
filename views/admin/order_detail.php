<?php
require_once '../../config/database.php';

$orderId = (int) ($_GET['id'] ?? 0);

if ($orderId <= 0) {
    exit('Đơn hàng không tồn tại.');
}

/* Lấy thông tin đơn hàng */
$stmt = $conn->prepare('
SELECT *
FROM orders
WHERE order_id = ?
');

$stmt->execute([$orderId]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    exit('Không tìm thấy đơn hàng.');
}

/* Lấy danh sách sản phẩm */
$stmt = $conn->prepare('
SELECT
    od.*,
    pv.image AS variant_image
FROM order_details od
LEFT JOIN product_variants pv
    ON od.product_id = pv.product_id
    AND od.size = pv.size
    AND od.color = pv.color
WHERE od.order_id = ?
ORDER BY od.id ASC
');

$stmt->execute([$orderId]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

function statusText($status)
{
    return match ($status) {
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
        default => 'Không xác định'
    };
}

function statusClass($status)
{
    return match ($status) {
        'pending' => 'processing',
        'confirmed' => 'confirmed',
        'shipping' => 'shipping',
        'delivered' => 'success',
        'cancelled' => 'cancel',
        default => 'cancel'
    };
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<title>Chi tiết đơn hàng</title>

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

.admin-container{
    display:flex;
}

/* Sidebar giữ nguyên như orders.php */

.sidebar{
    width:260px;
    background:#fff;
    border-right:1px solid #ffd9ea;
    position:fixed;
    left:0;
    top:0;
    height:100vh;
    display:flex;
    flex-direction:column;
    padding:30px 20px;
}

.sidebar-content{
    flex:1;
    overflow:auto;
}

.logo{
    font-family:'Great Vibes',cursive;
    font-size:42px;
    color:#ff4fa3;
    text-decoration:none;
    font-weight:bold;
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
    align-items:center;
    padding:14px 16px;
    border-radius:14px;
    color:#333;
    text-decoration:none;
    margin-bottom:10px;
}

.menu a:hover{
    background:#fff0f7;
}

.menu .active{
    background:#ff4fa3;
    color:white;
}

.logout-btn{
    display:flex;
    gap:12px;
    padding:14px 16px;
    color:#ff4fa3;
    text-decoration:none;
}

.main-content{
    margin-left:260px;
    width:calc(100% - 260px);
    padding:30px;
}

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

.page-title h1{
    font-size:35px;
    margin-bottom:10px;
}

.page-title p{
    color:#777;
}

.admin-box{
    display:flex;
    gap:15px;
    align-items:center;
}

.admin-box img{
    width:50px;
    height:50px;
    border-radius:50%;
}

.content-box{
    background:#fff;
    border-radius:24px;
    padding:30px;
    box-shadow:0 4px 15px rgba(0,0,0,.05);
}

.section-title{
    font-size:24px;
    margin-bottom:25px;
}

.info-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:25px;
    margin-bottom:30px;
}

.info-card{
    background:#fff8fc;
    border:1px solid #ffe3f0;
    border-radius:18px;
    padding:22px;
}

.info-card h3{
    color:#ff4fa3;
    margin-bottom:18px;
}

.info-card p{
    margin:10px 0;
    line-height:1.8;
}

.status{
    padding:8px 16px;
    border-radius:30px;
    font-weight:bold;
    font-size:13px;
}

.processing{
    background:#fff0d9;
    color:#ff9800;
}

.confirmed{
    background:#e7f1ff;
    color:#007bff;
}

.shipping{
    background:#dff5ff;
    color:#00a0d2;
}

.success{
    background:#e4fff0;
    color:#23b26d;
}

.cancel{
    background:#ffe4e4;
    color:#dc3545;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

th{
    text-align:left;
    color:#888;
    padding-bottom:15px;
}

td{
    padding:18px 0;
    border-top:1px solid #f3f3f3;
    vertical-align:middle;
}

.product{
    display:flex;
    align-items:center;
    gap:15px;
}

.product img{
    width:70px;
    height:70px;
    border-radius:12px;
    object-fit:cover;
}

.total{
    margin-top:30px;
    text-align:right;
    font-size:24px;
    font-weight:bold;
    color:#ff4fa3;
}

.back-btn{
    display:inline-block;
    margin-top:30px;
    background:#ff4fa3;
    color:#fff;
    text-decoration:none;
    padding:12px 25px;
    border-radius:12px;
}

.back-btn:hover{
    background:#ff2d91;
}

</style>
</head>

<body>
<div class="admin-container">

<!--SIDEBAR-->

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

            <a href="customers.php">
                <i class="fa-solid fa-users"></i>
                Khách hàng
            </a>

            <a href="promotions.php">
                <i class="fa-solid fa-tags"></i>
                Khuyến mãi
            </a>

            <a href="user_account.php">
                <i class="fa-solid fa-user"></i>
                Tài khoản người dùng
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

            <a href="collections.php">
                <i class="fa-solid fa-layer-group"></i>
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

            <div class="menu-title">HỆ THỐNG</div>

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
        <h1>Chi tiết đơn hàng</h1>
        <p>Thông tin chi tiết đơn hàng #<?php echo $order['order_id']; ?></p>
    </div>

    <div class="admin-box">

        <img src="https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg?semt=ais_hybrid&w=740&q=80">

        <div>
            <strong>Admin</strong><br>
            <small>Quản trị viên</small>
        </div>
    </div>
</div>

<div class="content-box">
    <!-- THÔNG TIN ĐƠN HÀNG -->

    <div class="info-grid">
        <div class="info-card">
            <h3>
                <i class="fa-solid fa-file-invoice"></i>
                Thông tin đơn hàng
            </h3>

            <p>
                <strong>Mã đơn:</strong>
                #<?php echo $order['order_id']; ?>
            </p>

            <p>
                <strong>Mã đặt hàng:</strong>
                <?php echo htmlspecialchars($order['order_code']); ?>
            </p>

            <p>
                <strong>Ngày đặt:</strong>
                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
            </p>

            <p>
                <strong>Thanh toán:</strong>
                <?php echo htmlspecialchars($order['payment_method']); ?>
            </p>

            <p>
                <strong>Trạng thái:</strong>

                <span class="status <?php echo statusClass($order['status']); ?>">
                    <?php echo statusText($order['status']); ?>
                </span>
            </p>

            <?php if (
                $order['status'] == 'cancelled'
                && !empty($order['cancelled_by'])
            ) { ?>

            <p style="margin-top:10px;color:#dc3545;">
                <strong>Hủy bởi:</strong>
                <?php echo htmlspecialchars($order['cancelled_by']); ?>
            </p>

            <?php } ?>
        </div>

        <div class="info-card">

            <h3>
                <i class="fa-solid fa-user"></i>
                Người nhận
            </h3>

            <p>
                <strong>Họ tên:</strong>
                <?php echo htmlspecialchars($order['receiver_name']); ?>
            </p>

            <p>
                <strong>SĐT:</strong>
                <?php echo htmlspecialchars($order['receiver_phone']); ?>
            </p>

            <p>
                <strong>Địa chỉ:</strong>
                <?php echo htmlspecialchars($order['receiver_address']); ?>
            </p>

            <?php if (!empty($order['note'])) { ?>

                <p>
                    <strong>Ghi chú:</strong>
                    <?php echo htmlspecialchars($order['note']); ?>
                </p>

            <?php } ?>

            <?php if ($order['status'] == 'cancelled') { ?>

                <p style="color:#dc3545;">
                    <strong>Lý do hủy:</strong>
                    <?php echo htmlspecialchars($order['cancel_reason']); ?>
                </p>
            <?php } ?>
        </div>
    </div>

    <!-- DANH SÁCH SẢN PHẨM -->

    <h2 class="section-title">
        Danh sách sản phẩm
    </h2>

    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Phân loại</th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($items as $item) { ?>
                <tr>
                    <td>
                        <div class="product">
                            <img src="../../<?php
                                echo !empty($item['variant_image'])
                                    ? ltrim($item['variant_image'], '/')
                                    : 'uploads/no-image.jpg'; ?>">
                            <div>
                                <strong>
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </strong>
                            </div>
                        </div>
                    </td>

                    <td>
                        Màu:
                        <b>
                            <?php echo htmlspecialchars($item['color']); ?>
                        </b>

                        <br>
                        Size:
                        <b>
                            <?php echo htmlspecialchars($item['size']); ?>
                        </b>
                    </td>

                    <td>
                        <?php
                            $price = $item['discount_price'] > 0
                ? $item['discount_price']
                : $item['price'];

                echo number_format($price);
                ?> đ
                    </td>

                    <td>
                        <?php echo $item['quantity']; ?>
                    </td>

                    <td>
                        <?php echo number_format($item['subtotal']); ?> đ
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="total">
        Tổng thanh toán:
        <?php echo number_format($order['total_price']); ?> đ
    </div>

    <a href="orders.php" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại danh sách đơn hàng
    </a>
</div>
</div>

</body>

</html>
