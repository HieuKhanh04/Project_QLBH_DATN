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

/* PRICE RANGE */
$priceRange = [
    'min' => null,
    'max' => null,
];

foreach ($variants as $v) {
    if (isset($v['price'])) {
        $priceRange['min'] = $priceRange['min'] === null
            ? $v['price']
            : min($priceRange['min'], $v['price']);

        $priceRange['max'] = $priceRange['max'] === null
            ? $v['price']
            : max($priceRange['max'], $v['price']);
    }
}

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
/* REVIEWS */
$stmt = $conn->prepare('
    SELECT *
    FROM product_reviews
    WHERE product_id = ?
    ORDER BY created_at DESC
');
$stmt->execute([$id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$avgRating = 0;
if (count($reviews) > 0) {
    $totalStar = 0;
    foreach ($reviews as $r) {
        $totalStar += $r['rating'];
    }
    $avgRating = round(
        $totalStar / count($reviews),
        1
    );
}

foreach ($variants as $v) {
    if (!empty($v['size'])) {
        $hasSize = true;
    }
    if (!empty($v['color'])) {
        $hasColor = true;
    }
}
?>

<?php if (count($reviews) > 0) { ?>

<div class="mb-3">

    <span class="text-warning">
        <i class="fa-solid fa-star"></i>
    </span>

    <b><?php echo $avgRating; ?>/5</b>

    <span class="text-muted">
        (<?php echo count($reviews); ?> đánh giá)
    </span>

</div>

<?php } ?>

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

    <!-- Ảnh chung -->
    <?php foreach ($images as $img) { ?>

        <img
            src="../<?php echo $img['image_url']; ?>"
            class="thumb-img"
            onclick="changeImage(this.src)">

    <?php } ?>

    <!-- Ảnh biến thể -->
    <?php
    $variantImages = [];

foreach ($variants as $variant) {
    if (
        !empty($variant['image'])
        && !in_array($variant['image'], $variantImages)
    ) {
        $variantImages[] = $variant['image'];
        ?>

            <img src="../<?php echo $variant['image']; ?>" class="thumb-img" onclick="changeImage(this.src)">
        <?php
    }
}
?>
</div>

<!-- MAIN IMAGE -->
<div class="col-md-5 text-center">
<?php
$mainImage = '../uploads/no-image.jpg';

foreach ($images as $img) {
    if (!empty($img['is_main']) && $img['is_main'] == 1) {
        $mainImage = '../'.$img['image_url'];
        break;
    }
}

// nếu không có is_main thì fallback ảnh đầu
if ($mainImage === '../uploads/no-image.jpg' && !empty($images)) {
    $mainImage = '../'.$images[0]['image_url'];
}
?>
<img id="mainProductImage" src="<?php echo $mainImage; ?>">
</div>

<!-- INFO -->
<div class="col-md-6">

<div class="product-title">
    <?php echo htmlspecialchars($product['name']); ?>
</div>

<div class="product-price my-3">
    <?php if ($priceRange['min'] && $priceRange['max'] && $priceRange['min'] != $priceRange['max']) { ?>
        <?php echo number_format($priceRange['min']); ?>₫
        -
        <?php echo number_format($priceRange['max']); ?>₫
    <?php } else { ?>
        <?php echo number_format($priceRange['min'] ?? $product['price']); ?>₫
    <?php } ?>
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

<hr class="my-4">

<h5 class="fw-bold mb-3">
    Đánh giá sản phẩm
</h5>
<div class="d-flex align-items-center gap-2 mb-4">

    <span class="fs-4 fw-bold text-warning">
        <?php echo $avgRating; ?>
    </span>

    <div>

        <?php
        $star = round($avgRating);

for ($i = 1; $i <= 5; ++$i) {
    if ($i <= $star) {
        echo '<i class="fa-solid fa-star text-warning"></i>';
    } else {
        echo '<i class="fa-regular fa-star text-warning"></i>';
    }
}
?>

    </div>

    <span class="text-muted">
        <?php echo count($reviews); ?> đánh giá
    </span>

</div>

<?php if (empty($reviews)) { ?>

    <div class="alert alert-light border">
        Chưa có đánh giá nào.
    </div>

<?php } else { ?>

    <?php foreach ($reviews as $review) { ?>

        <div class="card mb-3 border-0 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="fw-bold">
                            <?php
                    $name = $review['customer_name'];
        $nameLength = mb_strlen($name);
        echo mb_substr($name, 0, 1)
            .str_repeat('*', max($nameLength - 2, 0))
            .mb_substr($name, -1);
        ?>
                        </div>

                        <small class="text-muted">
                            <?php
        echo date(
            'd/m/Y',
            strtotime($review['created_at'])
        );
        ?>
                        </small>

                    </div>

                </div>

                <!-- SAO -->
                <div class="my-2">

                    <?php
                    for ($i = 1; $i <= 5; ++$i) {
                        if ($i <= $review['rating']) {
                            echo '<i class="fa-solid fa-star text-warning"></i>';
                        } else {
                            echo '<i class="fa-regular fa-star text-warning"></i>';
                        }
                    }
        ?>

                </div>

                <!-- BIẾN THỂ -->
                <div class="text-muted mb-2">

                    <?php if (!empty($review['size'])) { ?>
                        Size:
                        <b><?php echo htmlspecialchars($review['size']); ?></b>
                    <?php } ?>

                    <?php if (!empty($review['size']) && !empty($review['color'])) { ?>
                        |
                    <?php } ?>

                    <?php if (!empty($review['color'])) { ?>
                        Màu:
                        <b><?php echo htmlspecialchars($review['color']); ?></b>
                    <?php } ?>

                </div>

                <!-- NỘI DUNG -->
                <div>
                    <?php
                        echo !empty($review['comment'])
                            ? nl2br(htmlspecialchars($review['comment']))
                            : '<span class="text-muted">Không có nội dung</span>';
        ?>
                </div>

            </div>

        </div>

    <?php } ?>

<?php } ?>

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

        updateVariantUI(); // Add here
    }
});

/* COLOR */
document.querySelectorAll('.color-btn').forEach(btn=>{
    btn.onclick = function(){
        document.querySelectorAll('.color-btn').forEach(b=>b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('selectedColor').value = this.dataset.color;

        updateVariantUI(); // Adđ here
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

let variants = <?php echo json_encode($variants); ?>;
console.log(variants);

function updateVariantPrice() {
    let size = document.getElementById('selectedSize').value;
    let color = document.getElementById('selectedColor').value;

    let match = variants.find(v => {
        let okSize = !size || (v.size && v.size == size);
        let okColor = !color || (v.color && v.color == color);
        return okSize && okColor;
    });

    if (match && match.price) {
        document.querySelector('.product-price').innerText =
            Number(match.price).toLocaleString() + '₫';
    } else {
        resetPrice();
    }
}

function resetPrice(){
    let firstVariant = variants[0];
    if(
        firstVariant &&
        firstVariant.discount_price &&
        Number(firstVariant.discount_price) < Number(firstVariant.price)
    ){
        document.querySelector('.product-price').innerHTML = `
            <span class="text-decoration-line-through text-muted fs-6">
                ${Number(firstVariant.price).toLocaleString()}₫
            </span>

            <span class="ms-2 text-danger fw-bold">
                ${Number(firstVariant.discount_price).toLocaleString()}₫
            </span>
        `;
    }else{
        document.querySelector('.product-price').innerHTML =

            Number(firstVariant?.price ?? <?php echo $product['price']; ?>)
            .toLocaleString()
            + '₫';
    }
}

function updateVariantUI() {
    let size = document.getElementById('selectedSize').value;
    let color = document.getElementById('selectedColor').value;

    let hasSize = <?php echo $hasSize ? 'true' : 'false'; ?>;
    let hasColor = <?php echo $hasColor ? 'true' : 'false'; ?>;
   
    // HÀM HIỂN THỊ GIÁ
    function showPrice(match){
        if(!match){
            resetPrice();
            return;
        }
        let priceHTML = '';

        if(
            match.discount_price &&
            Number(match.discount_price) < Number(match.price)
        ){
            priceHTML = `
                <span class="text-decoration-line-through text-muted fs-6">
                    ${Number(match.price).toLocaleString()}₫
                </span>

                <span class="ms-2 text-danger fw-bold">
                    ${Number(match.discount_price).toLocaleString()}
                </span>
            `;
        }else{
            priceHTML = `
                ${Number(match.price).toLocaleString()}₫
            `;
        }
        document.querySelector(".product-price").innerHTML = priceHTML;
    }

    // HÀM ĐỔI ẢNH
    function changeVariantImage(match){
        if(match && match.image){
            document.getElementById("mainProductImage").src =
                "../" + match.image;
        }
    }
   
    // CASE 1:Có cả SIZE + MÀU
    if(hasSize && hasColor){
        // Chưa chọn đủ
        if(!size || !color){
            resetImageAndPrice();
            return;
        }

        let match = variants.find(v =>
            v.size == size &&
            v.color == color
        );

        if(match){
            changeVariantImage(match);
            showPrice(match);
        }else{
            resetImageAndPrice();
        }
        return;
    }

    // CASE 2:Chỉ có MÀU
    if(!hasSize && hasColor){
        if(!color){
            resetImageAndPrice();
            return;
        }

        let match = variants.find(v =>
            v.color == color
        );

        if(match){
            changeVariantImage(match);
            showPrice(match);
        }else{
            resetImageAndPrice();
        }
        return;
    }

    // CASE 3:Chỉ có SIZE
    if(hasSize && !hasColor){
        if(!size){
            resetImageAndPrice();
            return;
        }

        let match = variants.find(v =>
            v.size == size
        );

        if(match){
            changeVariantImage(match);
            showPrice(match);
        }else{
            resetImageAndPrice();
        }
        return;

    }
    // CASE 4:Sản phẩm không có biến thể
    resetPrice();
}

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>