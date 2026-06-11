<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['user_id'];
$orderId = (int) ($_GET['id'] ?? 0);

if ($orderId <= 0) {
    exit('Đơn hàng không tồn tại');
}

/* THÔNG TIN ĐƠN HÀNG */
$stmt = $conn->prepare('
    SELECT *
    FROM orders
    WHERE order_id = ?
    AND user_id = ?
');

$stmt->execute([
    $orderId,
    $userId,
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    exit('Không tìm thấy đơn hàng');
}

/* CHI TIẾT SẢN PHẨM */
$stmt = $conn->prepare('
    SELECT od.*,
           p.name,
           od.product_image AS image
    FROM order_details od
    LEFT JOIN products p
        ON od.product_id = p.product_id
    WHERE od.order_id = ?
');

$stmt->execute([$orderId]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

function statusBadge($status)
{
    switch ($status) {
        case 'pending':
            return 'warning';

        case 'processing':
            return 'info';

        case 'shipping':
            return 'primary';

        case 'completed':
            return 'success';

        case 'cancelled':
            return 'danger';

        default:
            return 'secondary';
    }
}
?>

<!DOCTYPE html>

<html lang="vi">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Chi tiết đơn hàng</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap"
rel="stylesheet">

<style>

body{
    background:#fff7fb;
    font-family:'Quicksand',sans-serif;
}

.card-custom{
    border:none;
    border-radius:20px;
    box-shadow:0 8px 25px rgba(0,0,0,.08);
}

.section-title{
    color:#ff4fa3;
    font-weight:700;
}

.product-img{
    width:80px;
    height:80px;
    object-fit:cover;
    border-radius:12px;
}

.total-price{
    color:#ff4fa3;
    font-size:24px;
    font-weight:700;
}

.btn-pink{
    background:#ff4fa3;
    color:#fff;
    border:none;
    border-radius:12px;
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

<div class="card card-custom p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h3 class="section-title mb-0">
            Chi tiết đơn hàng
        </h3>

        <span class="badge bg-<?php echo statusBadge($order['status']); ?>">
            <?php echo strtoupper($order['status']); ?>
        </span>

    </div>

    <div class="row mb-4">

        <div class="col-md-6">

            <p>
                <b>Mã đơn:</b>
                <?php echo $order['order_code']; ?>
            </p>

            <p>
                <b>Ngày đặt:</b>
                <?php echo date(
                    'd/m/Y H:i',
                    strtotime($order['created_at'])
                ); ?>
            </p>

            <p>
                <b>Phương thức thanh toán:</b>
                <?php echo $order['payment_method']; ?>
            </p>

        </div>

        <div class="col-md-6">

            <p>
                <b>Người nhận:</b>
                <?php echo htmlspecialchars($order['receiver_name']); ?>
            </p>

            <p>
                <b>Số điện thoại:</b>
                <?php echo htmlspecialchars($order['receiver_phone']); ?>
            </p>

            <p>
                <b>Địa chỉ:</b>
                <?php echo htmlspecialchars($order['receiver_address']); ?>
            </p>

        </div>

    </div>

    <hr>

    <h5 class="section-title mb-3">
        Sản phẩm đã đặt
    </h5>

    <div class="table-responsive">

        <table class="table align-middle">

            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>SL</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($items as $item) { ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img 
                                src="../uploads/products/<?php echo htmlspecialchars($item['image'] ?? 'default.png'); ?>" 
                                class="product-img"
                            >

                            <div>
                                <?php echo htmlspecialchars($item['name']); ?>
                            </div>
                        </div>
                    </td>

                    <td>
                        <?php echo number_format($item['price'], 0, ',', '.'); ?> đ
                    </td>

                    <td>
                        <?php echo $item['quantity']; ?>
                    </td>

                    <td>

                        <?php
                        echo number_format(
                            $item['price'] * $item['quantity'],
                            0,
                            ',',
                            '.'
                        );
                    ?> đ

                    </td>

                </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>

    <hr>

    <div class="text-end">

        <div class="total-price">

            Tổng cộng:
            <?php
            echo number_format(
                $order['total_price'],
                0,
                ',',
                '.'
            );
?> đ

        </div>

    </div>

    <div class="mt-4">

        <a href="profile.php?tab=orders"
           class="btn btn-pink">

            ← Quay lại đơn mua

        </a>

    </div>

</div>
</div>

<?php include 'layout/footer.php'; ?>

</body>
</html>
