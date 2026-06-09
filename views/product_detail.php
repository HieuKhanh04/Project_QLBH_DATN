<?php
session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

$id = $_GET['id'] ?? 0;

/* PRODUCT */
$product = $productModel->getProductById($id);
if (!$product) {
    echo 'Không tìm thấy sản phẩm';
    exit;
}

/* IMAGES */
$stmt = $conn->prepare('
SELECT * FROM product_images
WHERE product_id=?
ORDER BY is_main DESC, id ASC
');
$stmt->execute([$id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* VARIANTS */
$stmt = $conn->prepare('
SELECT * FROM product_variants
WHERE product_id=?
');
$stmt->execute([$id]);
$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* CATEGORY */
$stmt = $conn->prepare('
SELECT name FROM categories
WHERE category_id=?
');
$stmt->execute([$product['category_id']]);
$category = $stmt->fetchColumn();

/* CHECK VARIANTS */
$hasSize = false;
$hasColor = false;

foreach ($variants as $v) {
    if (!empty($v['size'])) {
        $hasSize = true;
    }
    if (!empty($v['color'])) {
        $hasColor = true;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($product['name']); ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

#mainProductImage{
    width:100%;
    max-height:500px;
    object-fit:cover;
    border-radius:18px;
}

.thumb-img{
    width:80px;
    height:80px;
    object-fit:cover;
    border-radius:12px;
    cursor:pointer;
    border:2px solid transparent;
    transition:.2s;
}

.thumb-img:hover{
    border-color:var(--pink);
}

.product-title{
    font-size:26px;
    font-weight:700;
}

.product-price{
    color:var(--pink);
    font-size:24px;
    font-weight:700;
}

.variant-btn{
    border:2px solid #ddd;
    background:#fff;
    border-radius:10px;
    padding:6px 14px;
    margin-right:6px;
    margin-bottom:6px;
    font-weight:600;
    transition:.2s;
}

.variant-btn:hover{
    border-color:var(--pink);
    color:var(--pink);
}

.variant-btn.active{
    background:var(--pink)!important;
    color:#fff!important;
    border-color:var(--pink)!important;
}

#quantity{
    width:120px;
    border-radius:12px;
    border:2px solid #eee;
}

.btn-pink{
    background:var(--pink);
    border:2px solid var(--pink);
    color:#fff;
    border-radius:12px;
    font-weight:700;
    padding:10px 16px;
}

.btn-pink:hover{
    background:var(--pink-dark);
}

.btn-outline-pink{
    background:#fff;
    border:2px solid var(--pink);
    color:var(--pink);
    border-radius:12px;
    font-weight:700;
    padding:10px 16px;
}

.btn-outline-pink:hover{
    background:var(--pink);
    color:#fff;
}
</style>
</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container py-4">

<div class="card shadow-sm p-4">

<div class="row g-4">

<!-- THUMB -->
<div class="col-md-1 d-flex flex-md-column gap-2">
<?php foreach ($images as $img) { ?>
    <img src="../<?php echo $img['image_url']; ?>"
         class="thumb-img"
         onclick="changeImage(this.src)">
<?php } ?>
</div>

<!-- MAIN IMAGE -->
<div class="col-md-5 text-center">
<?php
$mainImage = !empty($images)
    ? '../'.$images[0]['image_url']
    : '../uploads/no-image.jpg';
?>
<img id="mainProductImage" src="<?php echo $mainImage; ?>">
</div>

<!-- INFO -->
<div class="col-md-6">

<div class="product-title">
    <?php echo htmlspecialchars($product['name']); ?>
</div>

<div class="product-price my-3">
    <?php echo number_format($product['price']); ?>₫
</div>

<p><b>Danh mục:</b> <?php echo $category; ?></p>

<p>
<b>Tình trạng:</b>
<?php echo $product['status']
    ? '<span class="text-success">Còn hàng</span>'
    : '<span class="text-danger">Hết hàng</span>'; ?>
</p>

<!-- SIZE -->
<?php if ($hasSize) { ?>
<div class="mb-3">
    <b>Size</b><br>
    <?php
    $sizes = [];
    foreach ($variants as $v) {
        if (!empty($v['size']) && !in_array($v['size'], $sizes)) {
            $sizes[] = $v['size'];
            ?>
        <button class="variant-btn size-btn"
                data-size="<?php echo $v['size']; ?>">
            <?php echo $v['size']; ?>
        </button>
    <?php }
        } ?>
</div>
<?php } ?>

<!-- COLOR -->
<?php if ($hasColor) { ?>
<div class="mb-3">
    <b>Màu sắc</b><br>
    <?php
        $colors = [];
    foreach ($variants as $v) {
        if (!empty($v['color']) && !in_array($v['color'], $colors)) {
            $colors[] = $v['color'];
            ?>
        <button class="variant-btn color-btn"
                data-color="<?php echo $v['color']; ?>">
            <?php echo $v['color']; ?>
        </button>
    <?php }
        } ?>
</div>
<?php } ?>

<!-- QTY -->
<div class="mb-3">
    <b>Số lượng</b><br>

    <div class="d-flex align-items-center gap-2 mt-2">

        <button type="button" class="btn btn-outline-secondary" onclick="changeQty(-1)">-</button>

        <input type="number" id="quantity" value="1" min="1" class="form-control text-center">

        <button type="button" class="btn btn-outline-secondary" onclick="changeQty(1)">+</button>

    </div>
</div>

<input type="hidden" id="selectedSize">
<input type="hidden" id="selectedColor">

<!-- ACTION -->
<div class="d-flex gap-2">

<button class="btn btn-outline-pink w-50" onclick="addToCart()">
<i class="fa fa-cart-plus me-2"></i> Thêm giỏ
</button>

<button class="btn btn-pink w-50" onclick="buyNow()">
Mua ngay
</button>

</div>

</div>
</div>

<hr class="my-4">

<h5 class="fw-bold">Mô tả sản phẩm</h5>
<p class="text-muted">
<?php echo nl2br($product['description']); ?>
</p>

</div>
</div>

<?php include 'layout/footer.php'; ?>

<script>
function changeImage(src){
    document.getElementById('mainProductImage').src = src;
}

/* SIZE */
document.querySelectorAll('.size-btn').forEach(btn=>{
    btn.onclick = function(){
        document.querySelectorAll('.size-btn').forEach(b=>b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('selectedSize').value = this.dataset.size;
    }
});

/* COLOR */
document.querySelectorAll('.color-btn').forEach(btn=>{
    btn.onclick = function(){
        document.querySelectorAll('.color-btn').forEach(b=>b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('selectedColor').value = this.dataset.color;
    }
});

/* ADD TO CART */
function addToCart(){
    let size = document.getElementById('selectedSize').value;
    let color = document.getElementById('selectedColor').value;
    let qty = document.getElementById('quantity').value;

    let hasVariant = <?php echo ($hasSize || $hasColor) ? 'true' : 'false'; ?>;

    if (hasVariant && (!size && !color)) {
        alert('Vui lòng chọn biến thể');
        return;
    }

    let url = '../controllers/CartController.php?action=add'
        + '&id=<?php echo $product['product_id']; ?>'
        + '&size=' + encodeURIComponent(size)
        + '&color=' + encodeURIComponent(color)
        + '&quantity=' + qty
        + '&ajax=1';

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Đã thêm vào giỏ');
                updateCartCount(data.count);
            }
        });
}

/* BUY NOW - FIXED */
function buyNow(){

    let size = document.getElementById('selectedSize').value;
    let color = document.getElementById('selectedColor').value;
    let qty = document.getElementById('quantity').value;

    let hasVariant =
        <?php echo ($hasSize || $hasColor) ? 'true' : 'false'; ?>;

    if(hasVariant && (!size && !color)){
        alert('Vui lòng chọn biến thể');
        return;
    }

    window.location =
        '../controllers/CartController.php?action=buy_now'
        + '&id=<?php echo $product['product_id']; ?>'
        + '&size=' + encodeURIComponent(size)
        + '&color=' + encodeURIComponent(color)
        + '&quantity=' + qty;
}

/* QTY */
function changeQty(val){
    let input = document.getElementById('quantity');
    let current = parseInt(input.value) || 1;
    current += val;
    if (current < 1) current = 1;
    input.value = current;
}

/* CART COUNT */
function updateCartCount(count){
    let badge = document.querySelector('.cart-count');

    if (!badge) {
        let cartIcon = document.querySelector('a[href="cart.php"]');
        if (!cartIcon) return;
        badge = document.createElement('span');
        badge.className = 'cart-count';
        cartIcon.appendChild(badge);
    }

    badge.innerText = count;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>