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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<title>Giỏ hàng</title>

<style>

:root{
    --pink:#ff4fa3;
    --pink-dark:#e63d8d;
    --bg:#fff7fb;
}

body{
    background:var(--bg);
    font-family:'Quicksand', sans-serif;
    font-weight:600;
}

/* TITLE */
.cart-title{
    color:var(--pink);
    font-size:28px;
    font-weight:700;
}

/* CART CARD */
.cart-item{
    border:none;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 4px 14px rgba(0,0,0,0.06);
    transition:.2s;
    background:#fff;
}

.cart-item:hover{
    transform:translateY(-2px);
}

/* IMAGE */
.cart-item img{
    width:90px;
    height:90px;
    object-fit:cover;
    border-radius:12px;
}

/* PRODUCT NAME */
.item-name{
    font-size:16px;
    font-weight:700;
    color:#333;
}

/* META */
.item-meta{
    font-size:13px;
    color:#888;
}

/* PRICE */
.item-price{
    color:var(--pink);
    font-weight:700;
    font-size:15px;
}

/* QTY BUTTON */
.qty-btn{
    border:2px solid #eee;
    background:#fff;
    width:32px;
    height:32px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
    color:#555;
    text-decoration:none;
}

.qty-btn:hover{
    border-color:var(--pink);
    color:var(--pink);
}

/* DELETE */
.delete-btn{
    color:#ff4fa3;
    font-size:18px;
    transition:.2s;
}

.delete-btn:hover{
    color:#e63d8d;
}

/* TOTAL BOX */
.total-box{
    background:#fff;
    border-radius:18px;
    padding:20px;
    box-shadow:0 4px 14px rgba(0,0,0,0.06);
}

.btn-pink{
    background:var(--pink);
    border:none;
    color:#fff;
    font-weight:700;
    border-radius:12px;
    padding:10px 20px;
}

.btn-pink:hover{
    background:var(--pink-dark);
    color:#fff;
}

/* CHECKBOX */
.form-check-input:checked{
    background-color:var(--pink);
    border-color:var(--pink);
}

</style>

</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container py-4">

    <!-- TITLE -->
    <div class="text-center mb-4">
        <h2 class="cart-title">🛒 Giỏ hàng</h2>
    </div>

    <?php if (empty($cart)) { ?>
        <div class="alert alert-warning text-center">
            Giỏ hàng đang trống
        </div>
    <?php } ?>

    <?php foreach ($cart as $cartKey => $item) { ?>

    <?php
        if (!is_array($item)) {
            continue;
        }

        $productId = $item['product_id'] ?? 0;
        $qty = $item['quantity'] ?? 1;
        $size = $item['size'] ?? '';
        $color = $item['color'] ?? '';

        $product = $productModel->getProductById($productId);
        if (!$product) {
            continue;
        }

        $stmt = $conn->prepare('
            SELECT image_url 
            FROM product_images 
            WHERE product_id=? 
            ORDER BY is_main DESC, id ASC 
            LIMIT 1
        ');
        $stmt->execute([$productId]);
        $image = $stmt->fetchColumn();

        $price = $product['price'] ?? 0;
        $subtotal = $price * $qty;
        $total += $subtotal;
        ?>

    <!-- ITEM -->
    <div class="card cart-item mb-3">

        <div class="card-body d-flex align-items-center gap-3">

            <!-- checkbox -->
            <input type="checkbox"
                   class="form-check-input item-check"
                   id="check<?php echo md5($cartKey); ?>"
                   data-id="<?php echo md5($cartKey); ?>"
                   data-cartkey="<?php echo htmlspecialchars($cartKey); ?>"
                   data-price="<?php echo $price; ?>"
                   data-qty="<?php echo $qty; ?>"
                   onchange="calcTotal()">

            <!-- image -->
            <img src="../<?php echo $image ?: 'uploads/no-image.jpg'; ?>">

            <!-- info -->
            <div class="flex-grow-1">

                <div class="item-name">
                    <?php echo htmlspecialchars($product['name']); ?>
                </div>

                <div class="item-meta">
                    Size: <?php echo htmlspecialchars($size); ?> |
                    Màu: <?php echo htmlspecialchars($color); ?>
                </div>

                <div class="item-price mt-1">
                    <?php echo number_format($price); ?> VND
                </div>

            </div>

            <!-- qty -->
            <div class="d-flex align-items-center gap-2">

                <a class="qty-btn"
                   href="../controllers/CartController.php?action=decrease&key=<?php echo urlencode($cartKey); ?>&redirect=cart">
                    −
                </a>

                <span class="fw-bold"><?php echo $qty; ?></span>

                <a class="qty-btn"
                   href="../controllers/CartController.php?action=increase&key=<?php echo urlencode($cartKey); ?>&redirect=cart">
                    +
                </a>

            </div>

            <!-- delete -->
            <a class="delete-btn"
               href="../controllers/CartController.php?action=remove&key=<?php echo urlencode($cartKey); ?>&redirect=cart"
               onclick="return confirm('Xóa sản phẩm?')">
                <i class="fa fa-trash"></i>
            </a>

        </div>

    </div>

    <?php } ?>

    <!-- TOTAL -->
    <div class="total-box mt-4 text-end">

        <h5>
            Tổng tiền:
            <span class="text-danger fw-bold" id="total">
                <?php echo number_format($total); ?>
            </span> VND
        </h5>

        <button class="btn btn-pink mt-2" onclick="goCheckout()">
            Thanh toán
        </button>

    </div>

</div>

<script>
function calcTotal() {
    let checkboxes = document.querySelectorAll(".item-check");
    let total = 0;
    let checkedItems = [];

    checkboxes.forEach(cb => {
        if (cb.checked) {
            checkedItems.push(cb.dataset.id);
            total += Number(cb.dataset.price) * Number(cb.dataset.qty);
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
    checked.forEach(cb => ids.push(cb.dataset.cartkey));

    window.location.href = "checkout.php?ids=" + ids.join(",");
}
</script>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>