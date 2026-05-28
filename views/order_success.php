<?php
session_start();
require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

/* Lấy order_id từ URL */
$order_id = $_GET['order_id'] ?? 0;

if (!$order_id) {
    echo 'Không tìm thấy đơn hàng';
    exit;
}

/* Lấy thông tin đơn hàng */
$stmt = $conn->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    echo 'Đơn hàng không tồn tại';
    exit;
}

/* Lấy chi tiết đơn hàng */
$stmt = $conn->prepare('
    SELECT od.*, p.name 
    FROM order_details od
    JOIN products p ON p.id = od.product_id
    WHERE od.order_id = ?
');
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đặt hàng thành công</title>

<style>
body{
    font-family:Arial;
    background:#fff7fb;
}

.box{
    width:800px;
    margin:50px auto;
    background:white;
    padding:30px;
    border-radius:20px;
    box-shadow:0 5px 20px rgba(0,0,0,0.1);
}

h1{
    color:#ff4fa3;
    text-align:center;
}

.item{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid #eee;
}

.total{
    text-align:right;
    font-size:20px;
    font-weight:bold;
    color:#ff4fa3;
    margin-top:20px;
}

.btn{
    display:block;
    text-align:center;
    margin-top:20px;
    padding:12px;
    background:#ff4fa3;
    color:white;
    text-decoration:none;
    border-radius:12px;
}
</style>
</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="box">

    <h1>🎉 Đặt hàng thành công!</h1>

    <p><b>Mã đơn hàng:</b> #<?php echo $order['id']; ?></p>
    <p><b>Họ tên:</b> <?php echo htmlspecialchars($order['name']); ?></p>
    <p><b>Số điện thoại:</b> <?php echo $order['phone']; ?></p>
    <p><b>Địa chỉ:</b> <?php echo $order['address']; ?></p>

    <hr>

    <h3>Chi tiết đơn hàng</h3>

    <?php foreach ($items as $it) { ?>
        <div class="item">
            <div>
                <?php echo $it['name']; ?> x <?php echo $it['qty']; ?>
            </div>

            <div>
                <?php echo number_format($it['price'] * $it['qty']); ?>₫
            </div>
        </div>
    <?php } ?>

    <div class="total">
        Tổng tiền: <?php echo number_format($order['total']); ?>₫
    </div>

    <a class="btn" href="index.php">Tiếp tục mua sắm</a>

</div>
<?php include 'layout/footer.php'; ?>
</body>
</html>