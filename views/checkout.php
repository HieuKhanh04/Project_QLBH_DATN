<!-- <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Thanh toán</title>
</head>

<style>
    body {
    margin: 0;
    font-family: Arial;
    background: #fff5f9;
    }

    /* CONTAINER CHÍNH */
    .checkout-container {
        max-width: 800px;
        margin: 30px auto;
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* TITLE */
    h2 {
        text-align: center;
        color: #ff4fa3;
        margin-bottom: 20px;
    }

    /* ITEM */
    .checkout-item {
        display: flex;
        justify-content: space-between;
        align-items: center;

        padding: 12px;
        margin-bottom: 10px;

        border-bottom: 1px solid #eee;
    }

    /* LEFT INFO */
    .item-info {
        display: flex;
        flex-direction: column;
    }

    .item-info strong {
        font-size: 16px;
    }

    .item-info span {
        color: #666;
        font-size: 14px;
    }

    /* PRICE */
    .item-price {
        font-weight: bold;
        color: #e60073;
    }

    /* TOTAL BOX */
    .total-box {
        margin-top: 20px;
        text-align: right;
        font-size: 20px;
        font-weight: bold;
        color: #333;
    }

    /* BUTTON */
    .btn-pay {
        display: block;
        margin-top: 20px;
        width: 100%;
        padding: 12px;

        background: #ff4fa3;
        color: white;
        text-align: center;

        border-radius: 8px;
        text-decoration: none;

        font-size: 18px;
        font-weight: bold;
        transition: 0.2s;
    }

    .btn-pay:hover {
        background: #e60073;
    }
</style>
<body>

<div class="checkout-container">

    <h2>🧾 Đơn hàng của bạn</h2>

    <div id="list"></div>

    <div class="total-box" id="totalBox"></div>

    <a href="#" class="btn-pay">Đặt hàng</a>

</div>

    <script>
        let items = JSON.parse(sessionStorage.getItem("checkout_items")) || [];

        let html = "";
        let total = 0;

        items.forEach(i => {
            let sub = i.price * i.qty;
            total += sub;

            html += `
            <div class="checkout-item">
                <div class="item-info">
                    <strong>Sản phẩm #${i.name}</strong>
                    <span>Số lượng: ${i.qty}</span>
                </div>

                <div class="item-price">
                    ${sub.toLocaleString()} VND
                </div>
            </div>
            `;
        });

        document.getElementById("list").innerHTML = html;

        document.getElementById("totalBox").innerHTML =
            "Tổng tiền: " + total.toLocaleString() + " VND";
    </script>

</body>
</html> -->

<?php
session_start();
require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

// Lấy sản phẩm được chọn từ session
$items = $_SESSION['checkout'] ?? [];

$total = 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thanh toán</title>

<style>
body{
    font-family: Arial;
    background:#f6f6f6;
    margin:0;
}

.container{
    width:900px;
    margin:40px auto;
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
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
    margin-top:20px;
    color:#e11d48;
}

form{
    margin-top:20px;
}

input{
    width:100%;
    padding:10px;
    margin:8px 0;
    border:1px solid #ddd;
    border-radius:8px;
}

button{
    width:100%;
    padding:12px;
    background:#ff85c1;
    border:none;
    color:white;
    font-size:16px;
    border-radius:10px;
    cursor:pointer;
}

button:hover{
    background:#ff5fa8;
}
</style>

</head>

<body>

<div class="container">

<h2>🧾 Thanh toán đơn hàng</h2>

<?php foreach ($items as $it) {
    $p = $productModel->getProductById($it['id']);
    $sub = $p['price'] * $it['qty'];
    $total += $sub;
    ?>

<div class="item">
    <span><?php echo $p['name']; ?> (x<?php echo $it['qty']; ?>)</span>
    <span><?php echo number_format($sub); ?> VND</span>
</div>

<?php } ?>

<div class="total">
    Tổng tiền: <?php echo number_format($total); ?> VND
</div>

<form method="POST" action="../controllers/OrderController.php">

    <input type="text" name="name" placeholder="Họ tên người nhận" required>
    <input type="text" name="phone" placeholder="Số điện thoại" required>
    <input type="text" name="address" placeholder="Địa chỉ giao hàng" required>

    <button type="submit">Đặt hàng</button>

</form>

</div>

</body>
</html>