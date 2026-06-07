<?php
session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

/* COLLECTIONS */
$stmt = $conn->query('SELECT * FROM collections');
$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* FILTER */
$collection_id = $_GET['collection'] ?? 0;

if ($collection_id > 0) {
    $stmt = $conn->prepare('SELECT * FROM products WHERE collection_id = ?');
    $stmt->execute([$collection_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = $productModel->getAllProducts();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Bộ sưu tập</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">

<style>

:root{
    --pink:#ff4fa3;
    --pink-hover:#e63d8d;
}

/* BASE */
body{
    background:#fff7fb;
    font-family:'Quicksand', sans-serif;
    font-weight:600;
}

/* TITLE */
.page-title{
    text-align:center;
    font-size:32px;
    font-weight:700;
    color:var(--pink);
    margin:25px 0;
}

/* COLLECTION CARD */
.collection-card{
    border:none;
    border-radius:16px;
    background:#fff;
    transition:.25s ease;
    text-decoration:none;
    color:#333;
}

.collection-card:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 20px rgba(0,0,0,.12);
}

.collection-card img{
    width:100%;
    height:120px;
    object-fit:cover;
    border-radius:12px;
}

.collection-name{
    margin-top:10px;
    font-weight:600;
}

/* PRODUCT CARD */
.product-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    background:#fff;
    transition:.3s ease;
}

.product-card:hover{
    transform:translateY(-6px);
    box-shadow:0 12px 25px rgba(0,0,0,.12);
}

.product-img{
    width:100%;
    height:260px;
}

.product-img img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* TEXT */
.product-name{
    font-weight:700;
    color:#333;
    min-height:48px;
}

.price{
    color:var(--pink);
    font-weight:700;
    font-size:18px;
}

/* =========================
   BUTTON SYSTEM (SYNC INDEX)
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
    transition:.25s ease;
    letter-spacing:.2px;
}

/* BUY NOW */
.btn-pink{
    background:var(--pink);
    border:2px solid var(--pink);
    color:#fff;
    box-shadow:0 2px 6px rgba(255,79,163,.15);
}

.btn-pink:hover{
    background:var(--pink-hover);
    border-color:var(--pink-hover);
    transform:translateY(-2px);
    box-shadow:0 6px 16px rgba(255,79,163,.25);
}

/* ADD CART */
.btn-outline-pink{
    background:#fff;
    border:2px solid var(--pink);
    color:var(--pink);
    box-shadow:0 2px 6px rgba(255,79,163,.08);
}

.btn-outline-pink:hover{
    background:var(--pink);
    color:#fff;
    transform:translateY(-2px);
    box-shadow:0 6px 16px rgba(255,79,163,.25);
}

</style>

</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container py-4">

    <h2 class="page-title">Bộ sưu tập</h2>

    <!-- COLLECTION LIST -->
    <div class="row g-3 mb-5">

        <div class="col-6 col-md-3 col-lg-2">
            <a href="collections.php"
               class="collection-card d-block text-center p-2 shadow-sm">

                <img src="https://picsum.photos/200/200?random=1">
                <div class="collection-name">Tất cả</div>

            </a>
        </div>

        <?php foreach ($collections as $c) { ?>

        <div class="col-6 col-md-3 col-lg-2">

            <a href="collections.php?collection=<?php echo $c['collection_id']; ?>"
               class="collection-card d-block text-center p-2 shadow-sm">

                <img src="<?php echo $c['image']; ?>">
                <div class="collection-name">
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

                    <div class="product-img">
                        <img src="https://picsum.photos/400/500?random=<?php echo $p['product_id']; ?>">
                    </div>

                    <div class="card-body">

                        <div class="product-name">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </div>

                        <div class="price mt-2">
                            <?php echo number_format($p['price']); ?>₫
                        </div>

                    </div>

                </a>

                <!-- BUTTONS SYNC INDEX -->
                <div class="card-footer bg-white border-0">

                    <div class="row g-2">

                        <div class="col-6">

                            <a href="../controllers/CartController.php?action=add&id=<?php echo $p['product_id']; ?>&redirect=collections"
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