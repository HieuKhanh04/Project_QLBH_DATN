<?php
session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

/* CATEGORY */
$stmt = $conn->query('SELECT * FROM categories');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* FILTER */
$category_id = $_GET['category'] ?? 0;

if ($category_id > 0) {
    $stmt = $conn->prepare('SELECT * FROM products WHERE category_id = ?');
    $stmt->execute([$category_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = $productModel->getAllProducts();
}

$activeCategory = (int) $category_id;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Sản phẩm</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600;700&display=swap" rel="stylesheet">

<style>
:root{
    --pink:#ff4fa3;
    --pink-hover:#e63d8d;
}

body{
    font-family:'Quicksand', sans-serif;
    background:#fff7fb;
}

/* TITLE */
.title{
    font-size:32px;
    font-weight:700;
    color:var(--pink);
    text-align:center;
    margin:20px 0;
}

/* CATEGORY */
.category-card{
    border:none;
    border-radius:14px;
    text-decoration:none;
    color:#333;
    background:#fff;
    transition:.25s;
    display:block;
}

.category-card:hover{
    transform:translateY(-4px);
    box-shadow:0 8px 20px rgba(0,0,0,.1);
}

.category-card.active{
    border:2px solid var(--pink);
}

.category-card img{
    width:100%;
    height:110px;
    object-fit:cover;
    border-radius:10px;
}

.category-name{
    margin-top:8px;
    font-weight:600;
}

/* PRODUCT CARD */
.product-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    transition:.3s;
    background:#fff;
}

.product-card:hover{
    transform:translateY(-6px);
    box-shadow:0 10px 25px rgba(0,0,0,.12);
}

.product-card img{
    width:100%;
    height:280px;
    object-fit:cover;
    object-position:center;
    display:block;
}

.product-name{
    font-weight:700;
    min-height:45px;
    color:#333;
}

.product-price{
    color:var(--pink);
    font-weight:700;
    font-size:18px;
}

/* =========================
   BUTTON SYSTEM (GIỐNG INDEX 100%)
========================= */

.btn-pink,
.btn-outline-pink{
    height:44px;
    padding:0 16px;
    font-size:15px;
    font-weight:700;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:.25s;
}

/* MUA NGAY */
.btn-pink{
    background:var(--pink);
    border:2px solid var(--pink);
    color:#fff;
}

.btn-pink:hover{
    background:var(--pink-hover);
    border-color:var(--pink-hover);
    color:#fff;
}

/* THÊM GIỎ */
.btn-outline-pink{
    background:#fff;
    border:2px solid var(--pink);
    color:var(--pink);
}

.btn-outline-pink:hover{
    background:var(--pink);
    color:#fff;
}
</style>
</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container py-3">

    <h2 class="title">Danh mục sản phẩm</h2>

    <!-- CATEGORY -->
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3 col-lg-2">
            <a href="products.php"
               class="category-card p-2 text-center <?php echo $activeCategory == 0 ? 'active' : ''; ?>">
                <img src="https://picsum.photos/200?random=1">
                <div class="category-name">Tất cả</div>
            </a>
        </div>

        <?php foreach ($categories as $c) { ?>
        <div class="col-6 col-md-3 col-lg-2">
            <a href="products.php?category=<?php echo $c['category_id']; ?>"
               class="category-card p-2 text-center <?php echo $activeCategory == $c['category_id'] ? 'active' : ''; ?>">
                <img src="https://picsum.photos/200?random=<?php echo $c['category_id']; ?>">
                <div class="category-name">
                    <?php echo htmlspecialchars($c['name']); ?>
                </div>
            </a>
        </div>
        <?php } ?>

    </div>

    <!-- PRODUCTS -->
    <div class="row g-4">

        <?php foreach ($products as $p) { ?>
        <div class="col-12 col-md-6 col-lg-3">

            <div class="card product-card shadow-sm">

                <a href="product_detail.php?id=<?php echo $p['product_id']; ?>"
                   class="text-decoration-none text-dark">

                    <img src="https://picsum.photos/400/500?random=<?php echo $p['product_id']; ?>">

                    <div class="card-body">
                        <div class="product-name">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </div>

                        <div class="product-price mt-2">
                            <?php echo number_format($p['price']); ?>₫
                        </div>
                    </div>

                </a>

                <!-- BUTTONS (GIỐNG INDEX 100%) -->
                <div class="card-footer bg-white border-0">

                    <div class="row g-2">

                        <div class="col-6">
                            <a href="../controllers/CartController.php?action=add&id=<?php echo $p['product_id']; ?>&redirect=products"
                               class="btn btn-outline-pink w-100">
                                Thêm giỏ
                            </a>
                        </div>

                        <div class="col-6">
                            <a href="checkout.php?ids=<?php echo $p['product_id']; ?>"
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

</body>
</html>