<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/ProductModel.php';

$productModel = new ProductModel($conn);

/* ADD PRODUCT */
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];

    $size = $_POST['size'];
    $color = $_POST['color'];
    $quantity = $_POST['quantity'];

    $image = $_POST['image'];

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    /* PRODUCTS */
    $stmt = $conn->prepare('
        INSERT INTO products
        (name, slug, price, description, category_id, status)
        VALUES (?, ?, ?, ?, ?, 1)
    ');

    $stmt->execute([
        $name,
        $slug,
        $price,
        $description,
        $category_id,
    ]);

    $product_id = $conn->lastInsertId();

    /* VARIANT */
    $stmt = $conn->prepare('
        INSERT INTO product_variants
        (product_id, size, color, price, quantity)
        VALUES (?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $product_id,
        $size,
        $color,
        $price,
        $quantity,
    ]);

    /* IMAGE */
    $stmt = $conn->prepare('
        INSERT INTO product_images
        (product_id, image_url, is_main)
        VALUES (?, ?, 1)
    ');

    $stmt->execute([
        $product_id,
        $image,
    ]);

    header('Location: products.php');
    exit;
}

/* SEARCH */
$keyword = $_GET['keyword'] ?? '';

if ($keyword != '') {
    $stmt = $conn->prepare('SELECT * FROM products WHERE name LIKE ?');
    $stmt->execute(["%$keyword%"]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = $productModel->getAllProducts();
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare('DELETE FROM products WHERE product_id = ?');
    $stmt->execute([$id]);

    header('Location: products.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Quản lý sản phẩm</title>

<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* ================= RESET ================= */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#fff5f9;
}

/* ================= LAYOUT ================= */
.admin-container{
    display:flex;
}

/* ================= SIDEBAR ================= */
.sidebar{
    width:260px;
    background:white;
    border-right:1px solid #ffd9ea;
    position:fixed;
    top:0;
    left:0;
    height:100vh;
    display:flex;
    flex-direction:column;
    padding:30px 20px;
}

.sidebar-content{
    flex:1;
    overflow-y:auto;
    padding-right:5px;
}

.logo{
    font-family:'Great Vibes', cursive;
    font-size:42px;
    color:#ff4fa3;
    font-weight:bold;
    text-decoration:none;
}

.menu-title{
    margin:30px 0 15px;
    font-size:13px;
    color:#999;
    font-weight:bold;
}

.menu a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px 16px;
    border-radius:14px;
    text-decoration:none;
    color:#333;
    margin-bottom:10px;
    transition:0.2s;
}

.menu a:hover{
    background:#fff0f7;
    color:#ff4fa3;
}

.menu .active{
    background:#ff4fa3;
    color:white;
}

/* LOGOUT */
.logout-btn{
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px 18px;
    border-radius:14px;
    color:#ff4fa3;
    text-decoration:none;
    margin-top:15px;
}

/* ================= CONTENT ================= */
.main-content{
    flex:1;
    margin-left:260px;
    padding:30px;
}

/* TOPBAR */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

.page-title h1{
    font-size:36px;
    margin-bottom:10px;
}

.page-title p{
    color:#777;
}

/* ADMIN BOX */
.admin-box{
    display:flex;
    align-items:center;
    gap:15px;
}

.admin-box img{
    width:50px;
    height:50px;
    border-radius:50%;
}

/* ================= ACTION ================= */
.header-action{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

/* SEARCH */
.search-box{
    width:340px;
    height:50px;
    background:white;
    border-radius:14px;
    padding:0 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    border:1px solid #ffe3ef;
}

.search-box input{
    border:none;
    outline:none;
    background:transparent;
    flex:1;
    font-size:15px;
}

.search-btn{
    border:none;
    background:none;
    color:#ff4fa3;
    cursor:pointer;
}

/* ADD BTN */
.add-btn{
    background:#ff4fa3;
    color:white;
    border:none;
    padding:14px 22px;
    border-radius:14px;
    font-weight:bold;
    cursor:pointer;
}

/* ================= TABLE ================= */
.product-table{
    background:white;
    border-radius:24px;
    padding:25px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    text-align:left;
    padding-bottom:18px;
    color:#999;
}

table td{
    padding:18px 0;
    border-top:1px solid #f3f3f3;
}

/* PRODUCT INFO */
.product-info{
    display:flex;
    align-items:center;
    gap:15px;
}

.product-info img{
    width:70px;
    height:70px;
    border-radius:14px;
}

/* STOCK */
.stock{
    padding:8px 14px;
    border-radius:30px;
    font-size:13px;
    font-weight:bold;
}

.instock{
    background:#e7fff1;
    color:#1fa463;
}

/* ACTION */
.action-btns{
    display:flex;
    gap:10px;
}

.edit-btn,
.delete-btn{
    width:38px;
    height:38px;
    border:none;
    border-radius:12px;
    color:white;
    cursor:pointer;
}

.edit-btn{ background:#ffb400; }
.delete-btn{ background:#ff4d6d; }

/* MODAL */

.modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,.4);
    z-index:9999;

    justify-content:center;
    align-items:center;
}

.modal-content{
    width:700px;
    background:white;
    border-radius:24px;
    padding:30px;
}

.modal-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.close-btn{
    font-size:30px;
    cursor:pointer;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
    margin-bottom:15px;
}

.form-grid input,
textarea{
    width:100%;
    padding:14px;
    border:1px solid #eee;
    border-radius:12px;
    outline:none;
}

.save-btn{
    width:100%;
    border:none;
    background:#ff4fa3;
    color:white;
    padding:15px;
    border-radius:14px;
    margin-top:15px;
    cursor:pointer;
}

</style>

</head>

<body>

<div class="admin-container">

<!-- SIDEBAR -->
<div class="sidebar">

        <a href="admin_dashboard.php" class="logo">
            HAN STORE
        </a>
        <div class="sidebar-content">
            <div class="menu">

                <div class="menu-title">
                    MENU CHÍNH
                </div>

                <a href="admin_dashboard.php">
                    <i class="fa-solid fa-chart-line"></i>
                    Dashboard
                </a>

                <a href="products.php" class="active">
                    <i class="fa-solid fa-shirt"></i>
                    Sản phẩm
                </a>

                <a href="orders.php">
                    <i class="fa-solid fa-cart-shopping"></i>
                    Đơn hàng
                </a>

                <a href="customers.php">
                    <i class="fa-solid fa-users"></i>
                    Khách hàng
                </a>

                <a href="promotions.php">
                    <i class="fa-solid fa-tags"></i>
                    Khuyến mãi
                </a>

                <a href="reports.php">
                    <i class="fa-solid fa-chart-pie"></i>
                    Báo cáo
                </a>

                <div class="menu-title">
                    QUẢN LÝ NỘI DUNG
                </div>  
                <a href="#" class="sidebar-item">
                    <i class="fa-regular fa-folder"></i>
                    Danh mục
                </a>

                <a href="#" class="sidebar-item">
                    <i class="fa-regular fa-image"></i>
                    Banner
                </a>

                <a href="#" class="sidebar-item">
                    <i class="fa-regular fa-file-lines"></i>
                    Bài viết
                </a>

                <div class="menu-title">
                    HỆ THỐNG
                </div>

                <a href="#" class="sidebar-item">
                    <i class="fa-solid fa-gear"></i>
                    Cài đặt
                </a>

                <a href="#" class="sidebar-item">
                    <i class="fa-regular fa-user"></i>
                    Tài khoản
                </a>

                <a href="#" class="sidebar-item">
                    <i class="fa-regular fa-clock"></i>
                    Nhật ký hoạt động
                </a>

            </div>
        </div>
    
        <a href="../logout.php" class="logout-btn">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Đăng xuất
        </a>
    </div>
</div>

<!-- CONTENT -->
<div class="main-content">

    <!-- TOP -->
    <div class="topbar">

        <div class="page-title">
            <h1>Quản lý sản phẩm</h1>
            <p>Quản lý kho hàng và sản phẩm</p>
        </div>

        <!-- ADMIN ACCOUNT -->
        <div class="admin-box">
            <img src="https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg?semt=ais_hybrid&w=740&q=80">
            <div>
                <strong>Admin</strong><br>
                <small>Quản trị viên</small>
            </div>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
    </div>

    <!-- ACTION -->
    <div class="header-action">

        <form method="GET" class="search-box">
            <input type="text" name="keyword" placeholder="Tìm sản phẩm..." value="<?php echo $keyword; ?>">
            <button class="search-btn"><i class="fa fa-search"></i></button>
        </form>

        <button class="add-btn" onclick="openModal()">
            <i class="fa fa-plus"></i> Thêm sản phẩm
        </button>

    </div>

    <!-- TABLE -->
    <div class="product-table">

        <table>

            <tr>
                <th>Sản phẩm</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>

            <?php foreach ($products as $product) { ?>

            <tr>

                <td>
                    <div class="product-info">
                        <img src="https://picsum.photos/100?<?php echo $product['product_id']; ?>">
                        <div>
                            <strong><?php echo $product['name']; ?></strong><br>
                            <small>ID: #SP<?php echo $product['product_id']; ?></small>
                        </div>
                    </div>
                </td>

                <td><?php echo $product['category_id']; ?></td>

                <td><?php echo number_format($product['price']); ?>đ</td>

                <td>20</td>

                <td><span class="stock instock">Còn hàng</span></td>

                <td>
                    <div class="action-btns">

                        <a href="edit_product.php?id=<?php echo $product['product_id']; ?>">
                            <button type="button" class="edit-btn">
                                <i class="fa fa-pen"></i>
                            </button>
                        </a>

                        <a href="?delete=<?php echo $product['product_id']; ?>"
                           onclick="return confirm('Xóa sản phẩm?')">

                            <button type="button" class="delete-btn">
                                <i class="fa fa-trash"></i>
                            </button>

                        </a>

                    </div>
                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</div>
    <!-- MODAL ADD PRODUCT -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Thêm sản phẩm</h2>

                <span class="close-btn"
                    onclick="closeModal()">
                    &times;
                </span>
            </div>

            <form method="POST">

                <div class="form-grid">

                    <input
                        type="text"
                        name="name"
                        placeholder="Tên sản phẩm"
                        required>

                    <input
                        type="number"
                        name="price"
                        placeholder="Giá"
                        required>

                    <input
                        type="number"
                        name="category_id"
                        placeholder="ID Danh mục"
                        required>

                    <input
                        type="text"
                        name="size"
                        placeholder="Size (M,L,XL)"
                        required>

                    <input
                        type="text"
                        name="color"
                        placeholder="Màu sắc"
                        required>

                    <input
                        type="number"
                        name="quantity"
                        placeholder="Số lượng"
                        required>

                    <input
                        type="text"
                        name="image"
                        placeholder="URL ảnh"
                        required>

                </div>

                <textarea
                    name="description"
                    placeholder="Mô tả sản phẩm"
                    rows="4"></textarea>

                <button
                    type="submit"
                    name="add_product"
                    class="save-btn">

                    Lưu sản phẩm
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal()
        {
            document.getElementById('productModal').style.display='flex';
        }

        function closeModal()
        {
            document.getElementById('productModal').style.display='none';
        }

        window.onclick = function(e)
        {
            let modal = document.getElementById('productModal');

            if(e.target == modal)
            {
                closeModal();
            }
        }
    </script>
</body>
</html>