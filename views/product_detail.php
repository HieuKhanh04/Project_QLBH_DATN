<?php

session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

$id = $_GET['id'] ?? 0;

$product = $productModel->getProductById($id);

if (!$product) {
    echo 'Không tìm thấy sản phẩm';
    exit;
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<title>
    <?php echo $product['name']; ?>
</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

body{
    margin:0;
    font-family:Arial;
    background:#fff7fb;
}

.header{
    height:80px;
    background:white;

    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:0 40px;

    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

.logo{
    font-size:30px;
    color:#ff4fa3;
    font-weight:bold;
}

.back-btn{
    text-decoration:none;
    color:#ff4fa3;
    font-size:16px;
}

.detail-container{

    width:90%;

    margin:50px auto;

    display:flex;

    gap:50px;

    background:white;

    padding:30px;

    border-radius:25px;

    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.product-image img{

    width:450px;

    border-radius:20px;
}

.product-info{
    flex:1;
}

.product-info h1{

    font-size:40px;

    color:#333;
}

.price{

    margin:20px 0;

    font-size:35px;

    color:#ff4fa3;

    font-weight:bold;
}

.desc{

    color:#666;

    line-height:1.8;

    margin-top:20px;
}

.add-btn{

    display:inline-block;

    padding:12px 20px;

    background:#ff4fa3;

    color:white;

    border-radius:12px;

    text-decoration:none;

    font-weight:bold;

    transition:0.2s;
}

.add-btn:hover{

    background:#e63d8d;
}

</style>

</head>

<body>
<?php include 'layout/header.php'; ?>



<div class="detail-container">

    <div class="product-image">

        <img src="https://picsum.photos/500/600">

    </div>

    <div class="product-info">

        <h1>
            <?php echo $product['name']; ?>
        </h1>

        <div class="price">
            <?php echo number_format($product['price']); ?>đ
        </div>

        <div class="desc">
            Sản phẩm phong cách Hàn Quốc tone trắng hồng,
            thiết kế trẻ trung hiện đại phù hợp đi chơi,
            đi học và chụp ảnh.
        </div>

        <a class="add-btn"
            href="../controllers/CartController.php?action=add&id=<?php echo $product['product_id']; ?>&redirect=detail&id_product=<?php echo $product['product_id']; ?>">

                Thêm vào giỏ

        </a>

    </div>

</div>
<?php include 'layout/footer.php'; ?>
</body>
</html>