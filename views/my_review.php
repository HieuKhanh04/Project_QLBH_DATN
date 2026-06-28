<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['user_id'];

$productId = (int) ($_GET['product'] ?? 0);
$orderId = (int) ($_GET['order'] ?? 0);

if ($productId <= 0 || $orderId <= 0) {
    exit('Dữ liệu không hợp lệ');
}

/* =========================
   LẤY THÔNG TIN ĐÁNH GIÁ
========================= */

$stmt = $conn->prepare('

    SELECT 
        pr.*,
        p.name,
        od.image

    FROM product_reviews pr

    JOIN products p
        ON pr.product_id = p.product_id

    LEFT JOIN order_details od
        ON pr.product_id = od.product_id
        AND pr.order_id = od.order_id

    WHERE pr.product_id = ?
    AND pr.order_id = ?
    AND pr.user_id = ?

');

$stmt->execute([
    $productId,
    $orderId,
    $userId,
]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$review) {
    exit('Không tìm thấy đánh giá');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Xem đánh giá</title>
<link 
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">
<link
href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap"
rel="stylesheet">

<style>
body {
    background: #fff7fb;
    font-family: 'Quicksand', sans-serif;
    font-weight: 600;
}

.review-card {
    background: white;
    border-radius: 22px;
    border: none;
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
}

.title {
    color: #ff4fa3;
    font-weight: 700;
}

.product-img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 15px;
}

.star {
    color: #ffc107;
    font-size: 30px;
}

.comment-box {
    background: #fffafd;
    padding: 20px;
    border-radius: 15px;
}

.btn-pink {
    background: #ff4fa3;
    color: white;
    border: none;
    border-radius: 12px;
    padding: 10px 25px;
    font-weight: 700;
}

.btn-pink:hover {
    background: #e63d8d;
    color: white;
}
</style>
</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="review-card p-4 p-lg-5">
                <h2 class="title mb-4">
                    <i class="fa-solid fa-star"></i>
                    Đánh giá của bạn
                </h2>

                <div class="d-flex gap-4 align-items-center mb-4">
                    <img
                        src="../uploads/products/<?php echo htmlspecialchars(
                            $review['image'] ?? 'default.png'
                        ); ?>"
                        class="product-img">
                    <div>
                        <h4 class="fw-bold">
                            <?php echo htmlspecialchars(
                                $review['name']
                            ); ?>
                        </h4>

                        <div class="star">
                            <?php
                            for ($i = 1; $i <= 5; ++$i) {
                                echo $i <= $review['rating']
                                    ? '★'
                                    : '☆';
                            } ?>
                        </div>
                    </div>
                </div>

                <div class="comment-box mb-4">
                    <h5 class="fw-bold">
                        Nhận xét của bạn
                    </h5>

                    <p>
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $review['comment']
                            )
                        ); ?>
                    </p>
                </div>

                <div class="text-muted">
                    Ngày đánh giá:

                    <?php
                    echo date(
                        'd/m/Y H:i',
                        strtotime(
                            $review['created_at']
                        )
                    ); ?>
                </div>

                <hr>
                <div class="text-end">
                    <a
                        href="order_detail.php?id=<?php echo $orderId; ?>"
                        class="btn btn-pink">
                        ← Quay lại đơn hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">

</script>
</body>
</html>