<?php

session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

$keyword = $_GET['keyword'] ?? '';

if (!empty($keyword)) {
    $products = $productModel->searchProducts($keyword);
} else {
    $products = $productModel->getAllProducts();
}

$count = 0;

if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $count += (int) $qty;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<title>HAN STORE</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap"
rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#fff7fb;
}

/* TOP BAR */
.top-bar{

    background:#ff85c1;

    color:white;

    text-align:center;

    padding:10px;

    font-size:14px;

    letter-spacing:1px;
}

/* HEADER */
.header{

    background:white;

    height:90px;

    display:flex;

    align-items:center;

    justify-content:space-between;

    padding:0 50px;

    box-shadow:0 2px 10px rgba(0,0,0,0.05);

    position:sticky;

    top:0;

    z-index:1000;
}

/* LOGO */
.logo{

    font-size:34px;

    color:#ff4fa3;

    font-family:'Pacifico', cursive;
}

/* MENU */
.menu{

    display:flex;

    gap:35px;
}

.menu a{

    text-decoration:none;

    color:#333;

    font-size:16px;

    font-weight:bold;

    transition:0.2s;
}

.menu a:hover{

    color:#ff4fa3;
}

/* ICONS */
.header-icons{

    display:flex;

    gap:15px;
}

.icon-box{

    width:45px;
    height:45px;

    border-radius:50%;

    background:#fff0f7;

    display:flex;

    align-items:center;
    justify-content:center;

    text-decoration:none;

    position:relative;

    transition:0.2s;
}

.icon-box:hover{

    background:#ffd4ea;

    transform:scale(1.08);
}

.icon-box i{

    color:#ff4fa3;

    font-size:18px;
}

/* CART COUNT */
.cart-count{

    position:absolute;

    top:-5px;
    right:-5px;

    background:red;

    color:white;

    min-width:18px;
    height:18px;

    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:11px;
}

/* BANNER */
.banner{

    margin:25px auto;

    width:95%;

    height:450px;

    border-radius:25px;

    overflow:hidden;

    position:relative;

    background:
    linear-gradient(rgba(255,255,255,0.15),
    rgba(255,255,255,0.15)),

    url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=1600');

    background-size:cover;
    background-position:center;

    display:flex;

    align-items:center;

    padding-left:80px;
}

/* BANNER TEXT */
.banner-content h1{

    font-size:70px;

    color:white;

    text-shadow:0 4px 10px rgba(0,0,0,0.3);
}

.banner-content p{

    margin-top:15px;

    font-size:24px;

    color:white;
}

.banner-btn{

    display:inline-block;

    margin-top:25px;

    padding:14px 28px;

    background:#ff4fa3;

    color:white;

    border-radius:12px;

    text-decoration:none;

    font-weight:bold;

    transition:0.2s;
}

.banner-btn:hover{

    background:#e63d8d;
}

/* SECTION TITLE */
.section-title{

    text-align:center;

    margin:50px 0 30px;

    font-size:32px;

    color:#ff4fa3;
}

/* PRODUCTS */
.product-list{

    width:95%;

    margin:40px auto;

    display:grid;

    grid-template-columns:repeat(4, 1fr);

    gap:25px;
}

/* CARD */
.product-card{

    background:white;

    border-radius:20px;

    overflow:hidden;

    box-shadow:0 4px 15px rgba(0,0,0,0.08);

    transition:0.25s;

    display:flex;

    flex-direction:column;
}

.product-link{

    text-decoration:none;

    color:inherit;

    display:block;
}

.product-card:hover{

    transform:translateY(-8px);
}

/* IMAGE */
.product-card img{

    width:100%;

    height:320px;

    object-fit:cover;
}

/* INFO */
.product-info{

    padding:18px;

    display:flex;

    flex-direction:column;

    gap:12px;
}

.product-info h3{

    font-size:18px;

    margin-bottom:12px;

    color:#333;
}

.price{

    color:#ff4fa3;

    font-size:22px;

    font-weight:bold;

    margin-bottom:15px;
}

/* BUTTON */
.add-btn{

    display:block;

    text-align:center;

    padding:12px;

    background:#ff85c1;

    color:white;

    text-decoration:none;

    border-radius:12px;

    transition:0.2s;

    width:100%;
}

.add-btn:hover{

    background:#ff4fa3;
}

/* SEARCH */
.search-form{

    display:flex;

    align-items:center;

    background:#fff0f7;

    border-radius:30px;

    overflow:hidden;

    margin-right:15px;
}

.search-form input{

    border:none;

    outline:none;

    padding:10px 15px;

    width:220px;

    background:transparent;
}

.search-form button{

    border:none;

    background:none;

    padding:0 15px;

    cursor:pointer;

    color:#ff4fa3;

    font-size:16px;
}

.logo{
    text-decoration:none;
}

</style>

</head>

<body>

<!-- HEADER -->
<?php include 'layout/header.php'; ?>

<!-- BANNER -->
<div class="banner">
    <div class="banner-content">
        <h1>Summer Pink</h1>
        <p>Bộ sưu tập mới tone trắng hồng cực xinh</p>
        <a href="#" class="banner-btn">Mua ngay</a>
    </div>
</div>

<!-- TITLE -->
<h2 class="section-title">Sản phẩm nổi bật</h2>

<!-- PRODUCTS -->
<div class="product-list">

<?php foreach ($products as $p) { ?>

    <div class="product-card">

        <!-- DETAIL -->
        <a href="product_detail.php?id=<?php echo $p['product_id']; ?>"
           class="product-link">

            <img src="https://picsum.photos/400/500">

            <div class="product-info">

                <h3><?php echo htmlspecialchars($p['name']); ?></h3>

                <p style="color:#666;font-size:14px;">
                    Size: <?php echo $p['size'] ?? 'Free size'; ?>
                </p>

                <p style="color:#666;font-size:14px;">
                    Màu: <?php echo $p['color'] ?? 'Nhiều màu'; ?>
                </p>

                <div class="price">
                    <?php echo number_format($p['price']); ?>đ
                </div>

            </div>

        </a>

        <!-- ADD CART -->
        <a class="add-btn"
           href="../controllers/CartController.php?action=add&id=<?php echo $p['product_id']; ?>&redirect=index">

            Thêm vào giỏ
        </a>

    </div>

<?php } ?>

</div>

</body>
</html>