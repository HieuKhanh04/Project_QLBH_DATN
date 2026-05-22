<?php
session_start();
require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);
$cart = $_SESSION['cart'] ?? [];

$total = 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">

    <title>Giỏ hàng</title>

    <style>
        body {
            font-family: Arial;
            background: #fff5f9;
            margin: 0;
        }

        h2 { padding: 20px; }

        .cart-header {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            background: white;
            position: relative;
        }

        .home-icon {
            font-size: 22px;
            color: #ff4fa3;
            text-decoration: none;
        }

        .cart-title {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-family: 'Great Vibes', cursive;
            font-size: 34px;
            color: #ff4fa3;
        }

        .spacer { width: 24px; }

        .cart-item {
            display: flex;
            align-items: center;
            background: white;
            margin: 15px 20px;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }

        .cart-info {
            flex: 1;
            margin-left: 15px;
        }

        .cart-info h3 {
            margin: 0;
        }

        .cart-info p {
            color: #e05297;
            font-weight: bold;
        }

        .cart-action {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .qty-btn {
            padding: 5px 10px;
            background: #ff85c1;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .qty-number {
            font-weight: bold;
        }

        .remove-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #ffe5e5;
            color: red;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .total-box {
            text-align: right;
            padding: 20px;
            font-size: 20px;
            font-weight: bold;
        }

        .checkout-btn {
            margin-top: 10px;
            padding: 10px 20px;
            background: #ff85c1;
            color: white;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }

        .item-check {
            width: 20px;
            height: 20px;
            margin-right: 15px;
        }
    </style>
</head>

<body>

<?php include 'layout/header.php'; ?>

    <div class="cart-header">
        <div class="cart-title" style="position:static; transform:none;">
            🛒 Giỏ hàng
        </div>
        <div class="spacer"></div>

    </div>

<?php foreach ($cart as $productId => $qty) {
    $product = $productModel->getProductById($productId);

    // 🔥 FIX: tránh lỗi null product
    if (!$product) {
        continue;
    }

    $price = $product['price'] ?? 0;
    $subtotal = $price * $qty;
    $total += $subtotal;
    ?>

<div class="cart-item">

    <input type="checkbox"
        class="item-check"
        id="check<?php echo $productId; ?>"
        data-id="<?php echo $productId; ?>"
        data-price="<?php echo $price; ?>"
        data-qty="<?php echo $qty; ?>"
        onchange="calcTotal()">

    <img src="https://via.placeholder.com/100">

    <div class="cart-info">
        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
        <p><?php echo number_format($price); ?> VND</p>
    </div>

    <div class="cart-action">

        <a class="qty-btn"
           href="../controllers/CartController.php?action=decrease&id=<?php echo $productId; ?>&redirect=cart">−</a>

        <span class="qty-number"><?php echo $qty; ?></span>

        <a class="qty-btn"
           href="../controllers/CartController.php?action=increase&id=<?php echo $productId; ?>&redirect=cart">+</a>

        <a class="remove-btn"
           href="../controllers/CartController.php?action=remove&id=<?php echo $productId; ?>&redirect=cart"
           onclick="return confirm('Xóa sản phẩm?')">
            <i class="fa-solid fa-trash"></i>
        </a>

    </div>

</div>

<?php } ?>

<div class="total-box">
    Tổng tiền: <span id="total"><?php echo number_format($total); ?></span> VND <br>

    <button class="checkout-btn" onclick="goCheckout()">
        Thanh toán
    </button>
</div>

<script>
function calcTotal() {

    let checkboxes = document.querySelectorAll(".item-check");
    let total = 0;
    let checkedItems = [];

    checkboxes.forEach(cb => {
        if (cb.checked) {
            checkedItems.push(cb.dataset.id);

            let price = parseInt(cb.dataset.price);
            let qty = parseInt(cb.dataset.qty);

            total += price * qty;
        }
    });

    localStorage.setItem("checkedItems", JSON.stringify(checkedItems));

    document.getElementById("total").innerText =
        total.toLocaleString();
}

window.onload = function () {

    let saved = JSON.parse(localStorage.getItem("checkedItems")) || [];

    saved.forEach(id => {
        let checkbox = document.getElementById("check" + id);
        if (checkbox) checkbox.checked = true;
    });

    calcTotal();
}

function goCheckout() {

    let checked = document.querySelectorAll(".item-check:checked");

    if (checked.length === 0) {
        alert("Vui lòng chọn sản phẩm!");
        return;
    }

    let ids = [];
    checked.forEach(cb => ids.push(cb.dataset.id));

    window.location.href = "checkout.php?ids=" + ids.join(",");
}
</script>

</body>
</html>