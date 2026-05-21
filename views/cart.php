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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet"><meta charset="UTF-8">
<title>Giỏ hàng</title>

<style>
    body {
        font-family: Arial;
        background: #fff5f9;
        margin: 0;
    }

    /* TITLE */
    h2 {
        padding: 20px;
    }

    .cart-header {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    background: white;
    position: relative;
}

    /* icon trang chủ bên trái */
    .home-icon {
        font-size: 22px;
       color: #ff4fa3;
        text-decoration: none;
    }

    /* tiêu đề ở giữa */
    .cart-title {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        font-family: 'Great Vibes', cursive;
        font-size: 34px;
        color: #ff4fa3;
        font-weight: bold;
        text-shadow: 0 2px 6px rgba(255, 79, 163, 0.3);
            }

    /* giữ layout cân */
    .spacer {
        width: 24px;
    }

    /* CART ITEM */
    .cart-item {
        display: flex;
        align-items: center;
        background: white;
        margin: 15px 20px;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);

    }

    /* IMAGE */
    .cart-item img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 10px;
        gap: 10px;
    }

    /* INFO */
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

    /* ACTION RIGHT */
    .cart-action {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    /* QUANTITY */
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

    /* REMOVE */
    .remove-btn {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 35px;
        height: 35px;
        border-radius: 50%;

        background: #ffe5e5;
        color: red;
        text-decoration: none;
        font-size: 16px;

        transition: 0.2s;
    }

    .remove-btn:hover {
        background: red;
        color: white;
    }

    /* TOTAL */
    .total-box {
        text-align: right;
        padding: 20px;
        font-size: 20px;
        font-weight: bold;
    }

    .checkout-btn {
        display: inline-block;
        margin-top: 10px;
        padding: 10px 20px;
        background: #ff85c1;
        color: white;
        border-radius: 8px;
        text-decoration: none;
    }

    /* CHECK-BOX */
    .item-check {
        width: 22px;
        height: 22px;
        cursor: pointer;
        transform: scale(1.3);
        margin-right: 20px;
    }
</style>
</head>

<body>
    <?php include 'layout/header.php'; ?>

    <div class="cart-header">
        <a href="index.php" class="home-icon">
            <i class="fa-solid fa-house"></i>
        </a>
        <div class="cart-title">
            🛒 Giỏ hàng
    </div>

    <div class="spacer"></div>
</div>

    <?php foreach ($cart as $id => $qty) {
        $product = $productModel->getProductById($id);
        $subtotal = $product['price'] * $qty;
        // $total += $subtotal;
        ?>

    <div class="cart-item">

        <!-- CHECKBOX -->
        <input type="checkbox"
            class="item-check"
            id="check<?php echo $id; ?>"
            data-id="<?php echo $id; ?>"
            data-price="<?php echo $product['price']; ?>"
            data-qty="<?php echo $qty; ?>"
            onchange="calcTotal()">


        <!-- IMAGE -->
        <img src="https://via.placeholder.com/100">

        <!-- INFO -->
        <div class="cart-info">
            <h3><?php echo $product['name']; ?></h3>
            <p><?php echo number_format($product['price']); ?> VND</p>
        </div>

        <!-- ACTION -->
        <div class="cart-action">

            <!-- QUANTITY -->
            <a class="qty-btn" href="../controllers/CartController.php?action=decrease&id=<?php echo $id; ?>&redirect=cart">−</a>
            <span class="qty-number"><?php echo $qty; ?></span>
            <a class="qty-btn" href="../controllers/CartController.php?action=increase&id=<?php echo $id; ?>&redirect=cart">+</a>

            <!-- REMOVE -->
            <a class="remove-btn" 
                href="../controllers/CartController.php?action=remove&id=<?php echo $id; ?>&redirect=cart"
                onclick="return confirm('Xóa sản phẩm?')">
                    <i class="fa-solid fa-trash"></i>
            </a>
        </div>

    </div>

    <?php } ?>

    <!-- TOTAL -->
    <div class="total-box">
        Tổng tiền: <span id="total">0</span> VND <br>
        <a href="checkout.php" class="checkout-btn" onclick="return goCheckout()">
            Thanh toán
        </a>
    </div>

    <script>

        function calcTotal() {

            let checkboxes =
                document.querySelectorAll(".item-check");

            let total = 0;

            let checkedItems = [];

            checkboxes.forEach(cb => {

                // lưu trạng thái checked
                if(cb.checked){

                    checkedItems.push(cb.dataset.id);

                    let price = parseInt(cb.dataset.price);
                    let qty = parseInt(cb.dataset.qty);

                    total += price * qty;
                }
            });

            // lưu vào localStorage
            localStorage.setItem(
                "checkedItems",
                JSON.stringify(checkedItems)
            );

            // hiển thị tổng tiền
            document.getElementById("total")
                .innerText = total.toLocaleString();
        }

        /* LOAD LẠI CHECKBOX */
        window.onload = function(){

            let saved =
                JSON.parse(localStorage.getItem("checkedItems"))
                || [];

            saved.forEach(id => {

                let checkbox =
                    document.getElementById("check" + id);

                if(checkbox){
                    checkbox.checked = true;
                }
            });

            calcTotal();
        }
    </script>

    <script>
        function goCheckout() {
            let checked = document.querySelectorAll(".item-check:checked");

            if (checked.length === 0) {
                alert("Vui lòng chọn sản phẩm!");
                return false;
            }

            let items = [];

            checked.forEach(cb => {
                items.push({
                    id: cb.dataset.id,
                    price: cb.dataset.price,
                    qty: cb.dataset.qty
                });
            });

            sessionStorage.setItem("checkout_items", JSON.stringify(items));

            return true;
        }
    </script>

</body>
</html>