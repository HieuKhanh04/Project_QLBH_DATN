<?php

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

$id = $_GET['id'] ?? 0;
$product = $productModel->getProductById($id);

if (!$product) {
    echo 'Sản phẩm không tồn tại';
    exit;
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title><?php echo $product['name']; ?></title>

<style>
body{
    margin:0;
    font-family: Arial;
    background:#f6f6f6;
    color:#222;
}

/* WRAPPER */
.wrapper{
    width: 1100px;
    margin: 40px auto;
}

/* PRODUCT BOX */
.product-box{
    display:flex;
    gap:40px;
    background:#fff;
    padding:30px;
    border-radius:14px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

/* LEFT IMAGE */
.gallery{
    flex:1;
}

.gallery img{
    width:100%;
    border-radius:12px;
    object-fit:cover;
}

/* RIGHT INFO */
.info{
    flex:1;
}

.name{
    font-size:26px;
    font-weight:700;
    margin-bottom:10px;
}

.price{
    font-size:22px;
    color:#e11d48;
    font-weight:bold;
    margin:15px 0;
}

/* LABEL */
.label{
    font-size:14px;
    color:#666;
    margin-bottom:6px;
}

/* COLOR */
.color-group{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

.color-item{
    width:28px;
    height:28px;
    border-radius:50%;
    border:2px solid #ddd;
    cursor:pointer;
    transition:0.2s;
}

.color-item:hover{
    transform:scale(1.1);
    border-color:#999;
}

/* SIZE */
.size-group{
    display:flex;
    gap:10px;
    margin-bottom:25px;
}

.size{
    padding:8px 14px;
    border:1px solid #ddd;
    border-radius:8px;
    cursor:pointer;
    transition:0.2s;
}

.size:hover{
    border-color:#ff4fa3;
    color:#ff4fa3;
}

/* BUTTONS */
.actions{
    display:flex;
    gap:12px;
}

.btn{
    flex:1;
    padding:12px;
    border-radius:10px;
    text-align:center;
    text-decoration:none;
    font-weight:bold;
    transition:0.2s;
}

.btn-buy{
    background:#111;
    color:#fff;
}

.btn-buy:hover{
    background:#000;
}

.btn-cart{
    background:#ff85c1;
    color:#fff;
}

.btn-cart:hover{
    background:#ff5fa8;
}

/* DESCRIPTION */
.desc{
    margin-top:25px;
    background:#fff;
    padding:20px;
    border-radius:12px;
    line-height:1.6;
}

/* BACK */
.back{
    display:inline-block;
    margin-bottom:20px;
    text-decoration:none;
    color:#444;
}
.back:hover{
    color:#ff4fa3;
}

.topbar{
    background:#ff85c1;
    color:white;

    padding:15px 25px;

    display:flex;
    align-items:center;
    justify-content:space-between;

    font-weight:bold;

    position:sticky;
    top:0;
    z-index:1000;
}

/* BACK BUTTON */
.back-btn{
    color:white;
    text-decoration:none;
    font-size:16px;
}

/* TITLE */
.title{
    font-size:18px;
    font-weight:bold;
}
</style>

</head>

<body>

    <div class="topbar">

        <a href="index.php" class="back-btn">
            ← Quay lại
        </a>

        <div class="title">
            Chi tiết sản phẩm
        </div>

        <div style="width:80px;"></div>

    </div>


    <div class="wrapper">


        <div class="product-box">

            <!-- LEFT IMAGE -->
            <div class="gallery">
                <img src="https://via.placeholder.com/500">
            </div>

            <!-- RIGHT INFO -->
            <div class="info">

                <div class="name">
                    <?php echo $product['name']; ?>
                </div>

                <div class="price">
                    <?php echo number_format($product['price']); ?> VND
                </div>

                <!-- COLOR -->
                <div class="label">Màu sắc</div>
                <div class="color-group">
                    <div class="color-item" style="background:#1e3a8a;"></div>
                    <div class="color-item" style="background:#000;"></div>
                    <div class="color-item" style="background:#fff;"></div>
                </div>

                <!-- SIZE -->
                <div class="label">Kích thước</div>
                <div class="size-group">
                    <div class="size">S</div>
                    <div class="size">M</div>
                    <div class="size">L</div>
                    <div class="size">XL</div>
                </div>

                <!-- ACTIONS -->
                <div class="actions">

                    <a class="btn btn-buy" href="#">
                        Mua ngay
                    </a>

                    <a class="btn btn-cart"
                    href="../controllers/CartController.php?action=add&id=<?php echo $product['id']; ?>">
                        Thêm vào giỏ
                    </a>

                </div>

            </div>

        </div>

        <!-- DESCRIPTION -->
        <div class="desc">
            <h3>Mô tả sản phẩm</h3>
            <p>
                Đây là khu vực mô tả sản phẩm. Quý khách có thể tìm hiểu về chất liệu, form dáng, hướng dẫn bảo quản, v.v.
            </p>
        </div>

    </div>

</body>
</html>