<?php
session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

/* LẤY SẢN PHẨM */
$products = $productModel->getAllProducts();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Khuyến mãi</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --pink:#ff4fa3;
    --pink-hover:#e63d8d;
}

body{
    font-family:'Quicksand', sans-serif;
    background:#fff7fb;
}

.page-title{
    text-align:center;
    font-size:32px;
    font-weight:700;
    color:var(--pink);
    margin:25px 0;
}

/* BANNER */
.banner{
    height:320px;
    border-radius:24px;
    overflow:hidden;
    background:
        linear-gradient(rgba(255,255,255,.85),rgba(255,255,255,.85)),
        url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRrcQqVN2Pymgd5XpR24FNMuJTVDsN7pjYUI5lDDYAqag&s=10');
    background-size:cover;
    background-position:center;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    box-shadow:0 6px 18px rgba(0,0,0,.08);
}

.banner h1{ color:var(--pink); font-weight:700; }
.banner p{ color:#555; }

/* PRODUCT */
.product-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    background:#fff;
    transition:.3s;
}

.product-card{
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover{
    transform:translateY(-6px);
    box-shadow:0 12px 25px rgba(0,0,0,.12);
}

.product-img{
    width: 100%;
    height: 260px;   /*cố định */
    overflow: hidden;
    flex-shrink: 0;
}

.product-img img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* PRICE */
.price{
    color:var(--pink);
    font-weight:700;
    font-size:18px;
}

.old-price{
    color:#999;
    text-decoration:line-through;
    font-size:14px;
}

/* DISCOUNT */
.discount{
    position:absolute;
    top:12px;
    left:12px;
    background:var(--pink);
    color:#fff;
    padding:5px 10px;
    font-size:12px;
    border-radius:20px;
    font-weight:600;
}

/* BUTTON */
.btn-pink,
.btn-outline-pink{
    width:100%;
    height:44px;
    font-size:15px;
    font-weight:700;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.btn-pink{
    background:var(--pink);
    border:2px solid var(--pink);
    color:#fff;
}

.btn-pink:hover{
    background:var(--pink-hover);
    border-color:var(--pink-hover);
}

.btn-outline-pink{
    background:#fff;
    border:2px solid var(--pink);
    color:var(--pink);
}

.btn-outline-pink:hover{
    background:var(--pink);
    color:#fff;
}

.product-name{
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}

</style>
</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container my-4">
    <div class="banner">
        <div>
            <h1>🔥 DEAL SHOCK - SALE LỚN</h1>
            <p>Giảm giá cực mạnh - Thời trang hot trend</p>
        </div>
    </div>
</div>

<h2 class="page-title">Sản phẩm khuyến mãi</h2>

<div class="container pb-5">
<div class="row g-4">

<?php foreach ($products as $p) { ?>

<?php
    // FINAL PRICE (đã áp dụng promotion)
    $priceRange = $productModel->getFinalPriceRange($p['product_id']);

    $minPrice = (float) $priceRange['min_price'];
    $maxPrice = (float) $priceRange['max_price'];

    // fallback nếu lỗi
    if ($minPrice <= 0 && $maxPrice <= 0) {
        continue;
    }

    $variants = $productModel->getVariantsByProduct($p['product_id']);

    $sizes = [];
    $colors = [];

    foreach ($variants as $v) {
        if (!empty($v['size'])) {
            $sizes[] = $v['size'];
        }
        if (!empty($v['color'])) {
            $colors[] = $v['color'];
        }
    }

    $sizes = array_unique($sizes);
    $colors = array_unique($colors);

    // OLD PRICE giả lập (có thể thay bằng field DB sau)
    $oldPrice = $maxPrice + 80000;

    // discount %
    $discount = 0;
    if ($oldPrice > 0 && $maxPrice > 0) {
        $discount = round((($oldPrice - $maxPrice) / $oldPrice) * 100);
    }
    ?>

<div class="col-12 col-md-6 col-lg-3">

    <div class="card product-card shadow-sm position-relative">

        <?php if ($discount > 0) { ?>
            <div class="discount">-<?php echo $discount; ?>%</div>
        <?php } ?>

        <a href="product_detail.php?id=<?php echo $p['product_id']; ?>" class="text-decoration-none text-dark">

            <div class="product-img">
                <img src="<?php echo !empty($p['image_url'])
                        ? '../'.ltrim($p['image_url'], '/')
                        : '../assets/no-image.png'; ?>">
            </div>

            <div class="card-body">

                <h6 class="fw-bold product-name" title="<?php echo htmlspecialchars($p['name']); ?>">
                    <?php echo htmlspecialchars($p['name']); ?>
                </h6>

                <div class="old-price">
                    <?php echo number_format($oldPrice); ?>₫
                </div>

                <div class="price">
                    <?php if ($minPrice == $maxPrice) { ?>
                        <?php echo number_format($minPrice); ?>₫
                    <?php } else { ?>
                        <?php echo number_format($minPrice); ?>₫ - <?php echo number_format($maxPrice); ?>₫
                    <?php } ?>
                </div>

            </div>
        </a>

        <div class="card-footer bg-white border-0">
            <div class="row g-2">

                <div class="col-6">
                    <button class="btn btn-outline-pink w-100"
                        onclick="openCartModal(this)"
                        data-id="<?php echo $p['product_id']; ?>"
                        data-name="<?php echo htmlspecialchars($p['name']); ?>"
                        data-price="<?php echo $minPrice; ?>"
                        data-image="<?php echo !empty($p['image_url'])
                                ? '../'.ltrim($p['image_url'], '/')
                                : '../assets/no-image.png'; ?>"
                        data-has-variant="<?php echo $productModel->hasVariant($p['product_id']) ? 1 : 0; ?>"
                        data-sizes='<?php echo json_encode(array_values($sizes)); ?>'
                        data-colors='<?php echo json_encode(array_values($colors)); ?>'>
                        Thêm giỏ
                    </button>
                </div>

                <div class="col-6">
                    <a href="product_detail.php?id=<?php echo $p['product_id']; ?>"
                       class="btn btn-pink w-100">
                        Mua ngay
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>

<?php } ?>

</div>
</div>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function addToCart(id){
    fetch('../controllers/CartController.php?action=add&id=' + id + '&quantity=1&ajax=1')
    .then(r => r.json())
    .then(d => {
        if(d.success){
            alert('Đã thêm vào giỏ hàng');
            const cart = document.querySelector('.cart-count');
            if(cart) cart.innerText = d.count;
        }
    });
}
</script>

</body>
</html>