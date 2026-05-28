<?php
session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

/* LẤY COLLECTIONS */
$stmt = $conn->query('SELECT * FROM collections');
$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* FILTER COLLECTION */
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
<title>Bộ sưu tập</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body{
    font-family:Arial;
    background:#fff7fb;
    margin:0;
}

/* TITLE */
.title{
    text-align:center;
    font-size:32px;
    color:#ff4fa3;
    font-weight:bold;
    margin:30px 0;
}

/* COLLECTION GRID */
.collection-box{
    width:95%;
    margin:20px auto;
}

.collection-title{
    font-size:24px;
    font-weight:bold;
    color:#333;
    margin-bottom:15px;
}

/* GRID */
.collection-grid{
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:20px;
}

/* ITEM */
.collection-item{
    text-align:center;
    text-decoration:none;
    color:#333;
    transition:0.2s;
    border-radius:16px;
    padding:10px;
}

.collection-item img{
    width:100%;
    height:140px;
    object-fit:cover;
    border-radius:16px;
    transition:0.2s;
}

.collection-item:hover img{
    transform:scale(1.05);
}

.collection-item.active{
    background:#ff4fa3;
    color:white;
}

/* PRODUCTS */
.product-list{
    width:95%;
    margin:40px auto;
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:25px;
}

.product-card{
    background:white;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
    transition:0.2s;
}

.product-card:hover{
    transform:translateY(-6px);
}

.product-card img{
    width:100%;
    height:280px;
    object-fit:cover;
}

.product-info{
    padding:15px;
}

.product-name{
    font-weight:bold;
    margin-bottom:10px;
}

.price{
    color:#ff4fa3;
    font-weight:bold;
}

.add-btn{
    display:block;
    text-align:center;
    padding:10px;
    background:#ff85c1;
    color:white;
    text-decoration:none;
    border-radius:12px;
}

.add-btn:hover{
    background:#ff4fa3;
}

.buy-now-btn{
        display:block;
        text-align:center;
        padding:10px;
        margin-top:8px;

        background:#ff4fa3;
        color:white;
        text-decoration:none;
        border-radius:12px;

        font-weight:bold;
        transition:0.2s;
    }

    .buy-now-btn:hover{
        background:#e63d8d;
    }

</style>
</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="title">Bộ sưu tập</div>

<!-- COLLECTIONS -->
<div class="collection-box">
    <div class="collection-title">Danh sách bộ sưu tập</div>

    <div class="collection-grid">

        <a href="collections.php"
           class="collection-item">
            <img src="https://picsum.photos/200/200?random=1">
            <div>Tất cả</div>
        </a>

        <?php foreach ($collections as $c) { ?>
            <a href="collections.php?collection=<?php echo $c['collection_id']; ?>"
               class="collection-item">
                <img src="<?php echo $c['image']; ?>">
                <div><?php echo htmlspecialchars($c['name']); ?></div>
            </a>
        <?php } ?>

    </div>
</div>

<!-- PRODUCTS -->
<div class="product-list">

    <?php foreach ($products as $p) { ?>

        <div class="product-card">

            <img src="https://picsum.photos/400/500">

            <div class="product-info">

                <div class="product-name">
                    <?php echo htmlspecialchars($p['name']); ?>
                </div>

                <div class="price">
                    <?php echo number_format($p['price']); ?>₫
                </div>

                <a class="add-btn"
                   href="../controllers/CartController.php?action=add&id=<?php echo $p['product_id']; ?>&redirect=collections">
                    Thêm vào giỏ
                </a>

                <a class="buy-now-btn"
                    href="../views/checkout.php?ids=<?php echo $p['product_id']; ?>">
                        Mua ngay
                    </a>
            </div>

        </div>

    <?php } ?>

</div>
<?php include 'layout/footer.php'; ?>
</body>
</html>