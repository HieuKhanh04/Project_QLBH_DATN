<?php
session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

/* ===== GET CATEGORY ===== */
$stmt = $conn->query('SELECT * FROM categories');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== FILTER ===== */
$category_id = $_GET['category'] ?? 0;

if ($category_id > 0) {
    $stmt = $conn->prepare('SELECT * FROM products WHERE category_id = ?');
    $stmt->execute([$category_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = $productModel->getAllProducts();
}

$activeCategory = $_GET['category'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sản phẩm</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>

        body{
            font-family:Arial;
            background:#fff7fb;
            margin:0;
        }

        /* CATEGORY */
        .category-box{
            display:flex;
            gap:12px;
            flex-wrap:wrap;
            padding:20px 30px;
        }

        .cate-item{
            padding:10px 16px;
            background:#fff0f7;
            border-radius:20px;
            text-decoration:none;
            color:#333;
            font-weight:bold;
            transition:0.2s;
        }

        .cate-item:hover{
            background:#ff4fa3;
            color:white;
        }

        /* PRODUCT GRID */
        .product-list{
            width:95%;
            margin:20px auto;
            display:grid;
            grid-template-columns:repeat(4, 1fr);
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

        .product-card a{
            text-decoration:none;
            color:inherit;
        }

        .menu a{
            text-decoration:none;
        }

        .product-name{
            font-weight:bold;
            margin-bottom:10px;
            color:#333;
        }

        .price{
            color:#ff4fa3;
            font-weight:bold;
            font-size:18px;
            margin-bottom:12px;
        }

        .add-btn{
            display:block;
            text-align:center;
            padding:10px;
            background:#ff85c1;
            color:#fff !important;
            text-decoration:none;
            border-radius:12px;
            transition:0.2s;
        }

        .add-btn:hover{
            background:#ff4fa3;
        }

        .category-section{
            width:95%;
            margin:30px auto;
        }

        .category-title{
            font-size:28px;
            color:#ff4fa3;
            margin-bottom:20px;
            font-weight:bold;
        }

        .category-grid{
            display:grid;
            grid-template-columns:repeat(6, 1fr);
            gap:20px;
        }

        .category-item{
            text-align:center;
            text-decoration:none;
            color:#333;
            transition:0.2s;
        }

        .category-item img{
            width:80px;
            height:80px;
            object-fit:cover;
            border-radius:20px;
            background:#fff0f7;
            padding:10px;
            transition:0.2s;
        }

        .category-item:hover img{
            transform:scale(1.1);
            background:#ff4fa3;
        }

        .cate-name{
            margin-top:8px;
            font-weight:bold;
            font-size:14px;
        }

        .category-item{
            text-align:center;
            text-decoration:none;
            color:#333;
            transition:0.25s;
            padding:10px;
            border-radius:16px;
        }

        /* hover nhẹ */
        .category-item:hover{
            transform:translateY(-3px);
        }

        /* ACTIVE (đang chọn) */
        .category-item.active{
            background:#ff4fa3;
            color:white;
            box-shadow:0 6px 15px rgba(255,79,163,0.3);
            transform:scale(1.05);
        }

        .category-item.active img{
            background:white;
        }

        .btn-group{
    display:flex;
    gap:10px;
    padding:12px 15px 15px;
}

/* CHUNG 2 NÚT */
.add-btn,
.buy-now-btn{
    flex:1;
    text-align:center;
    padding:10px 0;
    border-radius:10px;
    font-size:14px;
    font-weight:bold;
    text-decoration:none;
    transition:0.2s;
}

/* THÊM VÀO GIỎ */
.add-btn{
    background:#ff85c1;
    color:white !important;
}

.add-btn:hover{
    background:#ff4fa3;
}

/* MUA NGAY */
.buy-now-btn{
    background:white;
    border:2px solid #ff4fa3;
    color:#ff4fa3;
}

.buy-now-btn:hover{
    background:#ff4fa3;
    color:white;
}

    </style>
</head>

<body>

<?php include 'layout/header.php'; ?>

<!-- CATEGORY -->
<div class="category-section">

    <h2 class="category-title">Danh mục</h2>

    <div class="category-grid">

        <!-- TẤT CẢ -->
        <a href="products.php"
            class="category-item <?php echo ($activeCategory == 0) ? 'active' : ''; ?>">
                <img src="https://cdn-icons-png.flaticon.com/512/869/869636.png">
                <div class="cate-name">Tất cả</div>
        </a>

        <?php foreach ($categories as $c) { ?>

            <a href="products.php?category=<?php echo $c['category_id']; ?>"
                class="category-item <?php echo ($activeCategory == $c['category_id']) ? 'active' : ''; ?>">
                    <img src="https://cdn-icons-png.flaticon.com/512/3081/3081559.png">

                    <div class="cate-name">
                        <?php echo htmlspecialchars($c['name']); ?>
                    </div>
            </a>
        <?php } ?>

    </div>

</div>

<!-- PRODUCTS -->
<div class="product-list">

    <?php foreach ($products as $p) { ?>

        <div class="product-card">

        <!-- DETAIL -->
        <a href="product_detail.php?id=<?php echo $p['product_id']; ?>" class="product-link">

            <img src="https://picsum.photos/400/500">

            <div class="product-info">

                <div class="product-name">
                    <?php echo htmlspecialchars($p['name']); ?>
                </div>

                <div class="price">
                    <?php echo number_format($p['price']); ?>đ
                </div>

            </div>
        </a>

        <!-- BUTTON GROUP (GIỐNG INDEX) -->
        <div class="btn-group">

            <a class="add-btn"
            href="../controllers/CartController.php?action=add&id=<?php echo $p['product_id']; ?>&redirect=products">
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