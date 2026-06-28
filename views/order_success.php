<?php
session_start();

require_once '../config/database.php';

$orderId = $_GET['order_id'] ?? 0;

if (!$orderId) {
    exit('Không tìm thấy đơn hàng');
}

/* =========================
   ORDER
========================= */

$stmt = $conn->prepare('
    SELECT *
    FROM orders
    WHERE order_id = ?
');

$stmt->execute([$orderId]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    exit('Đơn hàng không tồn tại');
}

/* =========================
   ORDER DETAILS
========================= */

$stmt = $conn->prepare('
    SELECT *
    FROM order_details
    WHERE order_id = ?
    ORDER BY id DESC
');

$stmt->execute([$orderId]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <title>Đặt hàng thành công</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap"
        rel="stylesheet">

    <style>

        :root{
            --pink:#ff4fa3;
            --pink-dark:#e63d8d;
            --bg:#fff7fb;
        }

        body{
            background:var(--bg);
            font-family:'Quicksand',sans-serif;
        }

        .success-card{
            border:none;
            border-radius:20px;
            box-shadow:0 6px 24px rgba(0,0,0,.08);
        }

        .success-icon{
            font-size:70px;
        }

        .success-title{
            color:var(--pink);
            font-weight:700;
        }

        .product-image{
            width:80px;
            height:95px;
            object-fit:cover;
            border-radius:12px;
            border:1px solid #eee;
        }

        .product-name{
            font-weight:700;
        }

        .pink-text{
            color:var(--pink);
        }

        .btn-pink{
            background:var(--pink);
            color:#fff;
            border:none;
            border-radius:12px;
            font-weight:700;
            padding:12px;
        }

        .btn-pink:hover{
            background:var(--pink-dark);
            color:#fff;
        }

        .info-box{
            background:#fff7fb;
            border:1px solid #ffd3e7;
            border-radius:12px;
            padding:15px;
        }

    </style>

</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-9">

            <div class="card success-card">

                <div class="card-body p-4 p-lg-5">

                    <!-- SUCCESS -->

                    <div class="text-center mb-4">

                        <div class="success-icon">
                            🎉
                        </div>

                        <h2 class="success-title mt-2">
                            Đặt hàng thành công!
                        </h2>

                        <p class="text-muted mb-0">
                            Cảm ơn bạn đã mua sắm tại cửa hàng.
                        </p>

                    </div>

                    <!-- ORDER INFO -->

                    <div class="info-box mb-4">

                        <div class="row">

                            <div class="col-md-6 mb-2">

                                <strong>Mã đơn hàng:</strong>

                                <span class="pink-text">
                                    <?php echo htmlspecialchars($order['order_code']); ?>
                                </span>

                            </div>

                            <div class="col-md-6 mb-2">

                                <strong>Ngày đặt:</strong>

                                <?php echo date(
                                    'd/m/Y H:i',
                                    strtotime($order['created_at'])
                                ); ?>

                            </div>

                            <div class="col-md-6 mb-2">

                                <strong>Người nhận:</strong>

                                <?php echo htmlspecialchars($order['receiver_name']); ?>

                            </div>

                            <div class="col-md-6 mb-2">

                                <strong>SĐT:</strong>

                                <?php echo htmlspecialchars($order['receiver_phone']); ?>

                            </div>

                            <div class="col-md-12">

                                <strong>Địa chỉ:</strong>

                                <?php echo htmlspecialchars($order['receiver_address']); ?>

                            </div>

                        </div>

                    </div>

                    <!-- PRODUCTS -->

                    <h5 class="fw-bold mb-3">
                        Chi tiết đơn hàng
                    </h5>

                    <?php foreach ($items as $item) { ?>

                        <div class="d-flex gap-3 border-bottom py-3">

                            <img
                                src="../<?php echo htmlspecialchars($item['image']); ?>"
                                class="product-image">

                            <div class="flex-grow-1">

                                <div class="product-name">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </div>

                                <div class="text-muted small mt-1">

                                    <?php if (!empty($item['size'])) { ?>
                                        Size:
                                        <?php echo htmlspecialchars($item['size']); ?>
                                        <br>
                                    <?php } ?>

                                    <?php if (!empty($item['color'])) { ?>
                                        Màu:
                                        <?php echo htmlspecialchars($item['color']); ?>
                                        <br>
                                    <?php } ?>

                                    Số lượng:
                                    <?php echo $item['quantity']; ?>

                                </div>

                            </div>

                            <div class="fw-bold pink-text">

                                <?php echo number_format($item['subtotal']); ?>₫

                            </div>

                        </div>

                    <?php } ?>

                    <!-- TOTAL -->

                    <div class="d-flex justify-content-between mt-4 fs-5 fw-bold">

                        <span>Tổng thanh toán</span>

                        <span class="pink-text">

                            <?php echo number_format($order['total_price']); ?>₫

                        </span>

                    </div>

                    <!-- BUTTONS -->

                    <div class="d-grid gap-2 mt-4">

                        <a
                            href="index.php"
                            class="btn btn-pink">

                            Tiếp tục mua sắm

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>