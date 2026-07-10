<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('Đánh giá không hợp lệ');
}

/* LẤY REVIEW */
$stmt = $conn->prepare('
    SELECT
        r.*,
        p.name AS product_name,
        pi.image_url
    FROM product_reviews r
    INNER JOIN products p
        ON r.product_id = p.product_id
    LEFT JOIN product_images pi
        ON p.product_id = pi.product_id
        AND pi.is_main = 1
    WHERE r.review_id = ?
');

$stmt->execute([$id]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    exit('Không tìm thấy đánh giá');
}

function renderStars($rating)
{
    $html = '';
    for ($i = 1; $i <= 5; ++$i) {
        $html .= '<i class="fa fa-star '.
            ($i <= $rating ? 'star' : 'empty').
            '"></i>';
    }

    return $html;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Chi tiết đánh giá</title>

<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

/* SIDEBAR */
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

/* CARD */
.card{
    background:#fff;
    border-radius:20px;
    padding:30px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

/* PRODUCT */
.product-box{
    display:flex;
    gap:20px;
    align-items:center;
    margin-bottom:25px;
}

.product-box img{
    width:90px;
    height:90px;
    border-radius:14px;
    object-fit:cover;
}

/* STAR */
.star{
    color:#ffb400;
}

.empty{
    color:#ddd;
}

/* INFO */
.info{
    margin-bottom:20px;
    line-height:1.8;
}

.label{
    font-weight:bold;
    color:#555;
}

/* COMMENT */
.comment{
    background:#fff0f7;
    padding:20px;
    border-radius:14px;
    white-space:pre-line;
}

/* BACK */
.back{
    display:inline-block;
    margin-top:20px;
    padding:10px 18px;
    background:#ff4fa3;
    color:white;
    border-radius:10px;
    text-decoration:none;
}

.back:hover{
    background:#ff2d91;
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

                <a href="products.php" class="active">
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

                <a href="user_account.php">
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
                <a href="categories.php" class="sidebar-item">
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

                <a href="activity_logs.php" class="sidebar-item">
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

        <div class="card">

            <!-- PRODUCT -->
            <div class="product-box">

                <img src="../../<?php echo $review['image_url'] ?? 'uploads/no-image.png'; ?>">

                <div>
                    <h2><?php echo htmlspecialchars($review['product_name']); ?></h2>
                    <small>#SP<?php echo $review['product_id']; ?></small>
                </div>

            </div>

            <!-- INFO -->
            <div class="info">

                <div>
                    <span class="label">Khách hàng:</span>
                    <?php echo htmlspecialchars($review['customer_name']); ?>
                </div>

                <?php if (!empty($review['size']) || !empty($review['color'])) { ?>
                    <div>
                        <span class="label">Biến thể:</span>
                        <?php echo $review['size'] ?? ''; ?>
                        <?php echo $review['color'] ? ' / '.$review['color'] : ''; ?>
                    </div>
                <?php } ?>

                <div>
                    <span class="label">Đánh giá:</span>
                    <?php echo renderStars($review['rating']); ?>
                </div>

                <div>
                    <span class="label">Thời gian:</span>
                    <?php echo $review['created_at']; ?>
                </div>

            </div>

            <!-- COMMENT -->
            <div class="comment">
                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
            </div>

            <!-- BACK -->
            <a href="reviews.php" class="back">
                ← Quay lại
            </a>

        </div>

    </div>

</div>

</body>
</html>