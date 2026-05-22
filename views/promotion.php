<?php
session_start();
require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

/* LẤY SẢN PHẨM GIẢM GIÁ (giả lập: price < 300k) */
$products = $productModel->getAllProducts();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Khuyến mãi</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    body{
        font-family: Arial;
        background:#fff7fb;
        margin:0;
    }

    /* HEADER */
    .header{
        background:white;
        padding:20px 50px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        box-shadow:0 2px 10px rgba(0,0,0,0.05);
    }

    /* LOGO */
    .logo{
        font-size:28px;
        color:#ff4fa3;
        font-weight:bold;
        text-decoration:none;
    }

    /* TITLE */
    .title{
        text-align:center;
        margin:40px 0;
        font-size:32px;
        color:#ff4fa3;
        font-weight:bold;
    }

    /* BANNER */
    .banner{
        width:95%;
        margin:20px auto;
        height:250px;
        border-radius:20px;
        background:#fff0f7;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#ff4fa3;
        font-size:40px;
        font-weight:bold;
        border:2px dashed #ff85c1;
    }

    /* GRID */
    .grid{
        width:95%;
        margin:40px auto;
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:25px;
    }

    /* CARD */
    .card{
        background:white;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 4px 15px rgba(0,0,0,0.08);
        transition:0.2s;

        display:flex;
        flex-direction:column;
    }

    .card:hover{
        transform:translateY(-6px);
    }

    /* IMG */
    .card img{
        width:100%;
        height:280px;
        object-fit:cover;
        display:block;
    }

    /* INFO */
    .info{
        padding:15px;
        display:flex;
        flex-direction:column;
        gap:10px;
    }

    .name{
        font-weight:bold;
        margin-bottom:10px;
        color:#333;
    }

    /* PRICE */
    .price{
        color:#ff4fa3;
        font-weight:bold;
        font-size:18px;
        margin-bottom:12px;
    }

    /* OLD PRICE */
    .old{
        color:#999;
        text-decoration:line-through;
        font-size:14px;
    }

    /* BUTTON (ĐỒNG BỘ PRODUCT) */
    .btn{
        display:block;
        text-align:center;
        padding:12px;
        background:#ff85c1;
        color:#fff !important;
        text-decoration:none;
        border-radius:12px;
        transition:0.2s;
        font-weight:bold;

        width:100%;
        box-sizing:border-box;
    }

    .btn:hover{
        background:#ff4fa3;
        transform:translateY(-2px);
    }

    /* DISCOUNT TAG */
    .discount{
        position:absolute;
        top:10px;
        left:10px;
        background:#ff4fa3;
        color:white;
        padding:5px 10px;
        border-radius:12px;
        font-size:12px;
    }

    .banner{
        width:95%;
        margin:20px auto;
        height:300px;
        border-radius:20px;

        background:
            linear-gradient(rgba(255,255,255,0.85), rgba(255,255,255,0.85)),
            url('https://images.unsplash.com/photo-1521335629791-ce4aec67dd49?q=80&w=1600');

        background-size:cover;
        background-position:center;

        display:flex;
        align-items:center;
        justify-content:center;
        text-align:center;

        box-shadow:0 4px 15px rgba(0,0,0,0.05);
    }

    /* TEXT */
    .banner-content h1{
        font-size:42px;
        margin-bottom:10px;
    }

    .banner-content p{
        font-size:18px;
        margin-bottom:20px;
    }

    /* BUTTON */
    .banner-btn{
        display:inline-block;
        padding:12px 24px;
        background:#ff4fa3;
        color:white;
        text-decoration:none;
        border-radius:12px;
        font-weight:bold;
        transition:0.2s;
    }

    .banner-btn:hover{
        background:#e63d8d;
        transform:translateY(-2px);
    }
</style>
</head>

<body>

<!-- HEADER -->
<?php include 'layout/header.php'; ?>

<!-- BANNER -->
<div class="banner">
    <div class="banner-content">
        <h1>🔥 DEAL SHOCK -  GIẢM GIÁ CỰC LỚN </h1>
        <p>Giảm đến 70% cho hàng trăm sản phẩm thời trang</p>
    </div>
</div>

<!-- TITLE -->
<div class="title">
    Sản phẩm khuyến mãi
</div>

<!-- PRODUCTS -->
<div class="grid">

<?php foreach ($products as $p) { ?>

    <?php if ($p['price'] < 300000) { ?>

    <div class="card">

        <div class="discount">-20%</div>

        <img src="https://picsum.photos/400/400">

        <div class="info">

            <div class="name">
                <?php echo htmlspecialchars($p['name']); ?>
            </div>

            <div class="old">
                <?php echo number_format($p['price'] + 80000); ?>₫
            </div>

            <div class="price">
                <?php echo number_format($p['price']); ?>₫
            </div>

            <a class="btn"
               href="../controllers/CartController.php?action=add&id=<?php echo $p['product_id']; ?>&redirect=promotion">
                    Thêm vào giỏ
            </a>

        </div>

    </div>

    <?php } ?>

<?php } ?>

</div>

</body>
</html>