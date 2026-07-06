<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['customer'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['customer']['user_id'];

$productId = (int) ($_GET['product'] ?? 0);
$orderId = (int) ($_GET['order'] ?? 0);
$size = trim($_GET['size'] ?? '');
$color = trim($_GET['color'] ?? '');

if ($productId <= 0 || $orderId <= 0) {
    exit('Dữ liệu không hợp lệ.');
}

/* ==========================
   KIỂM TRA ĐƠN HÀNG
========================== */

$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE order_id = ?
    AND user_id = ?
    AND status = 'delivered'
");

$stmt->execute([
    $orderId,
    $userId,
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    exit('Bạn không có quyền đánh giá đơn hàng này.');
}

/* ==========================
   KIỂM TRA SẢN PHẨM THUỘC ĐƠN
========================== */

$stmt = $conn->prepare('
    SELECT
        od.*,
        p.name
    FROM order_details od
    JOIN products p
        ON od.product_id = p.product_id
    WHERE od.order_id = ?
    AND od.product_id = ?
    LIMIT 1
');

$stmt->execute([
    $orderId,
    $productId,
]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    exit('Không tìm thấy sản phẩm.');
}

/* ==========================
   KIỂM TRA ĐÃ ĐÁNH GIÁ CHƯA
========================== */

$stmt = $conn->prepare('
    SELECT review_id
    FROM product_reviews
    WHERE product_id = ?
    AND order_id = ?
    AND user_id = ?
');

$stmt->execute([
    $productId,
    $orderId,
    $userId,
]);

if ($stmt->fetch()) {
    exit('Bạn đã đánh giá sản phẩm này.');
}
?>

<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<title>Đánh giá sản phẩm</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap"
      rel="stylesheet">

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

:root{
    --pink:#ff4fa3;
    --pink-hover:#e63d8d;
}

body{
    background:#fff7fb;
    font-family:'Quicksand',sans-serif;
    font-weight:600;
}

.review-card{
    background:#fff;
    border:none;
    border-radius:22px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.title{
    color:var(--pink);
    font-weight:700;
}

.product-box{
    display:flex;
    gap:20px;
    align-items:center;
    padding:18px;
    border-radius:18px;
    background:#fffafd;
    border:1px solid #ffe2ef;
}

.product-img{
    width:120px;
    height:120px;
    object-fit:cover;
    border-radius:16px;
}

.product-name{
    font-size:22px;
    font-weight:700;
    color:#333;
}

.info{
    color:#666;
    margin-top:8px;
}

.price{
    color:#ff4fa3;
    font-size:24px;
    font-weight:700;
}

.btn-pink{
    background:#ff4fa3;
    color:#fff;
    border:none;
    border-radius:12px;
    padding:10px 28px;
    font-weight:700;
}

.btn-pink:hover{
    background:#e63d8d;
    color:#fff;
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

                    <i class="fa-solid fa-star me-2"></i>

                    Đánh giá sản phẩm

                </h2>

                <div class="product-box">

                    <img
                        src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>"
                        class="product-img">

                    <div class="flex-grow-1">

                        <div class="product-name">

                            <?php echo htmlspecialchars($product['name']); ?>

                        </div>

                        <div class="info">

                            Size:
                            <b><?php echo htmlspecialchars($size ?: '-'); ?></b>

                            &nbsp;&nbsp;&nbsp;

                            Màu:
                            <b><?php echo htmlspecialchars($color ?: '-'); ?></b>

                        </div>

                        <div class="info">

                            Số lượng:
                            <b><?php echo $product['quantity']; ?></b>

                        </div>

                        <div class="price mt-3">

                            <?php
                            echo number_format(
                                $product['price'],
                                0,
                                ',',
                                '.'
                            );
?>

                            đ

                        </div>

                    </div>

                </div>

                <hr class="my-4">
                                <form action="../controllers/ReviewController.php" method="POST">

                    <input
                        type="hidden"
                        name="product_id"
                        value="<?php echo $productId; ?>">

                    <input
                        type="hidden"
                        name="order_id"
                        value="<?php echo $orderId; ?>">

                    <input
                        type="hidden"
                        name="size"
                        value="<?php echo htmlspecialchars($size); ?>">

                    <input
                        type="hidden"
                        name="color"
                        value="<?php echo htmlspecialchars($color); ?>">

                    <h5 class="mb-3 fw-bold">
                        Chất lượng sản phẩm
                    </h5>

                    <div class="rating mb-4">

                        <?php for ($i = 5; $i >= 1; --$i) { ?>

                            <input
                                type="radio"
                                id="star<?php echo $i; ?>"
                                name="rating"
                                value="<?php echo $i; ?>"
                                <?php echo $i == 5 ? 'checked' : ''; ?>>

                            <label for="star<?php echo $i; ?>">
                                <i class="fa-solid fa-star"></i>
                            </label>

                        <?php } ?>

                    </div>

                    <div class="mb-4">

                        <label class="form-label fw-bold">
                            Nhận xét của bạn
                        </label>

                        <textarea
                            name="comment"
                            rows="6"
                            class="form-control"
                            placeholder="Hãy chia sẻ cảm nhận của bạn về sản phẩm..."
                            required></textarea>

                    </div>

                    <div class="d-flex justify-content-between">

                        <a
                            href="order_detail.php?id=<?php echo $orderId; ?>"
                            class="btn btn-outline-secondary">

                            <i class="fa-solid fa-arrow-left me-2"></i>

                            Quay lại

                        </a>

                        <button
                            type="submit"
                            class="btn btn-pink">

                            <i class="fa-solid fa-paper-plane me-2"></i>

                            Gửi đánh giá

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>

.rating{

    display:flex;
    flex-direction:row-reverse;
    justify-content:flex-end;
    gap:10px;

}

.rating input{

    display:none;

}

.rating label{

    font-size:36px;
    color:#ddd;
    cursor:pointer;
    transition:.2s;

}

.rating label:hover,
.rating label:hover ~ label{

    color:#ffc107;

}

.rating input:checked ~ label{

    color:#ffc107;

}

textarea.form-control{

    border-radius:15px;
    resize:none;
    border:2px solid #eee;

}

textarea.form-control:focus{

    border-color:#ff4fa3;
    box-shadow:0 0 0 .2rem rgba(255,79,163,.15);

}

</style>

</body>

</html>