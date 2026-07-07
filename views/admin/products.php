<?php
session_start();
require_once '../../config/database.php';
require_once '../../models/ProductModel.php';
require_once '../../includes/activity_log.php';

$productModel = new ProductModel($conn);

/* ADD PRODUCT */
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    // $price = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];

    // $size = $_POST['size'];
    // $color = $_POST['color'];
    // $quantity = $_POST['quantity'];

    //    UPLOAD IMAGE
    $imageName = null;

    if (!empty($_FILES['image']['name'])) {
        $fileName = basename($_FILES['image']['name']);
        $imageName = time().'_'.$fileName;

        $uploadDir = __DIR__.'/../../uploads/products/';
        $uploadPath = $uploadDir.$imageName;

        // tạo thư mục nếu chưa có
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // check upload hợp lệ
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                exit('Upload ảnh thất bại');
            }
        } else {
            exit('File upload không hợp lệ');
        }
    }

    // SLUG
    // $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $slug = (string) preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower(trim($name)));

    /* PRODUCTS */
    $stmt = $conn->prepare('
        INSERT INTO products
        (name, slug, description, category_id, status)
        VALUES (?, ?, ?, ?, 1)
    ');

    $stmt->execute([
        $name,
        $slug,
        $description,
        $category_id,
    ]);

    $product_id = $conn->lastInsertId();
    writeLog(
        $conn,
        'CREATE',
        'Sản phẩm',
        'Thêm sản phẩm #'.$product_id.' - '.$name
    );

    // /* VARIANT */
    // $stmt = $conn->prepare('
    //     INSERT INTO product_variants
    //     (product_id, size, color, price, quantity)
    //     VALUES (?, ?, ?, ?, ?)
    // ');

    // $stmt->execute([
    //     $product_id,
    //     $size,
    //     $color,
    //     $price,
    //     $quantity,
    // ]);

    /* IMAGE */
    $stmt = $conn->prepare('
        INSERT INTO product_images
        (product_id, image_url, is_main)
        VALUES (?, ?, 1)
    ');

    $stmt->execute([
        $product_id,
        'uploads/products/'.$imageName,
    ]);

    header('Location: products.php');
    exit;
}

/* SEARCH */
$keyword = $_GET['keyword'] ?? '';

if ($keyword != '') {
    $stmt = $conn->prepare('
        SELECT
            p.*,
            COALESCE(SUM(v.quantity),0) AS total_quantity
        FROM products p
        LEFT JOIN product_variants v
            ON p.product_id = v.product_id
        WHERE p.name LIKE ?
        GROUP BY p.product_id
        ');
    $stmt->execute(["%$keyword%"]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = $productModel->getAllProducts();
}

/* DELETE */
$deleted = isset($_GET['deleted']) ? true : false;
if (isset($_POST['delete'])) {
    $id = $_POST['delete'];

    $stmt = $conn->prepare('SELECT name FROM products WHERE product_id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Xóa ảnh vật lý của sản phẩm
    $stmt = $conn->prepare('
        SELECT image_url
        FROM product_images
        WHERE product_id = ?
    ');
    $stmt->execute([$id]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $img) {
        $file = '../../'.$img['image_url'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // Xóa ảnh của biến thể
    $stmt = $conn->prepare('
        SELECT image
        FROM product_variants
        WHERE product_id = ?
    ');
    $stmt->execute([$id]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $variant) {
        if (!empty($variant['image'])) {
            $file = '../../'.$variant['image'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    // Xóa product_images
    $stmt = $conn->prepare('
        DELETE FROM product_images
        WHERE product_id = ?
    ');
    $stmt->execute([$id]);

    // Xóa product_variants
    $stmt = $conn->prepare('
        DELETE FROM product_variants
        WHERE product_id = ?
    ');
    $stmt->execute([$id]);

    // Cuối cùng xóa products
    $stmt = $conn->prepare('
        DELETE FROM products
        WHERE product_id = ?
    ');
    $stmt->execute([$id]);

    writeLog(
        $conn,
        'DELETE',
        'Sản phẩm',
        'Xóa sản phẩm #'.$id.' - '.($product['name'] ?? '')
    );

    header('Location: products.php?deleted=success');
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

/* RESET */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#fff5f9;
}

/* LAYOUT */
.admin-container{
    display:flex;
}

/* SIDEBAR */
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

/* CONTENT */
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

/* ACTION */
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

/* TABLE */
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

/*MODAL*/
.modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,.45);
    justify-content:center;
    align-items:center;
    z-index:9999;
}

.modal-content{
    width:520px;
    background:#fff;
    border-radius:24px;
    padding:30px;
}

.modal-header{
    text-align:center;
    margin-bottom:20px;
}

.modal-icon{
    font-size:60px;
    color:#dc3545;
    margin-bottom:10px;
}

.success-icon{
    color:#23b26d;
}

.form-group{
    margin-bottom:18px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:bold;
}

.form-group input,
.form-group select{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:12px;
    font-size:14px;
}

.modal-actions{
    display:flex;
    justify-content:flex-end;
    gap:12px;
    margin-top:25px;
}

.cancel-btn{
    background:#eee;
    border:none;
    padding:12px 22px;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
}

.save-btn{
    background:#ff4fa3;
    color:white;
    border:none;
    padding:12px 22px;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
}

.save-btn:hover{
    background:#ff2d91;
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

                <a href="user_account.php">
                    <i class="fa-solid fa-user"></i>
                    Tài khoản người dùng
                </a>

                <a href="reports.php">
                    <i class="fa-solid fa-chart-pie"></i>
                    Báo cáo
                </a>

                <div class="menu-title">
                    QUẢN LÝ NỘI DUNG
                </div>  
                <a href="categories.php" class="sidebar-item">
                    <i class="fa-regular fa-folder"></i>
                    Danh mục
                </a>

                <a href="collections.php">
                    <i class="fa-regular fa-images"></i>
                    Bộ sưu tập
                </a>

                <a href="notifications.php" class="sidebar-item">
                    <i class="fa-regular fa-bell"></i>
                    Thông báo
                </a>

                <a href="reviews.php">
                    <i class="fa-regular fa-star"></i>
                    Đánh giá
                </a>

                <div class="menu-title">
                    HỆ THỐNG
                </div>

                <a href="#" class="sidebar-item">
                    <i class="fa-solid fa-gear"></i>
                    Cài đặt
                </a>

                <a href="account.php" class="sidebar-item">
                    <i class="fa-regular fa-user"></i>
                    Tài khoản
                </a>

                <a href="activity_logs.php" class="sidebar-item">
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
            <!-- <i class="fa-solid fa-chevron-down"></i> -->
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
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>

            <?php foreach ($products as $product) { ?>

            <tr>

                <td>
                    <div class="product-info">
                        <!-- <img src="https://picsum.photos/100?<?php echo $product['product_id']; ?>"> -->
                        <?php if (!empty($product['image_url'])) { ?>
                        <!-- <pre>
                        <?php var_dump($product['image_url']); ?>
                        </pre> -->
                            <img src="../../<?php echo $product['image_url']; ?>">
                        <?php } else { ?>
                            <img src="../../uploads/no-image.png">
                        <?php } ?>
                        <div>
                            <strong><?php echo $product['name']; ?></strong><br>
                            <small>ID: #SP<?php echo $product['product_id']; ?></small>
                        </div>
                    </div>
                </td>

                <td><?php echo $product['category_id']; ?></td>

                <td><?php echo $product['total_quantity']; ?></td>

                <td>
                    <?php if ($product['total_quantity'] > 0) { ?>
                        <span class="stock instock">Còn hàng</span>
                    <?php } else { ?>
                        <span class="stock" style="background:#ffe5e5;color:#d60000;">
                            Hết hàng
                        </span>
                    <?php } ?>
                    </td>

                <td>
                    <div class="action-btns">

                        <a href="edit_product.php?id=<?php echo $product['product_id']; ?>">
                            <button type="button" class="edit-btn">
                                <i class="fa fa-pen"></i>
                            </button>
                        </a>

                        <button type="button"
                                class="delete-btn"
                                onclick="openDeleteModal(<?php echo $product['product_id']; ?>)">
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
                <h2>
                    <i class="fa fa-plus"></i>
                    Thêm sản phẩm
                </h2>

                <span class="close-btn"
                    onclick="closeModal()">
                    &times;
                </span>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <!-- TÊN -->
                    <div>
                        <label>Tên sản phẩm</label>
                        <input
                            type="text"
                            name="name"
                            placeholder="Nhập tên sản phẩm"
                            required>
                    </div>

                    <!-- GIÁ
                    <div>
                        <label>Giá sản phẩm</label>
                        <input
                            type="number"
                            name="price"
                            placeholder="VD: 500000"
                            required>
                    </div> -->

                    <!-- CATEGORY -->
                    <div>
                        <label>Danh mục</label>
                        <select
                            name="category_id"
                            required
                            class="form-control">
                            <option value="">
                                -- Chọn danh mục --
                            </option>

                            <?php
                            $cats = $conn->query(
                                'SELECT * FROM categories'
                            )->fetchAll(PDO::FETCH_ASSOC);
foreach ($cats as $cat) {
    ?>
                            <option value="<?php echo $cat['category_id']; ?>">
                                <?php echo $cat['name']; ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- SIZE
                    <div>
                        <label>Kích thước</label>
                        <input
                            type="text"
                            name="size"
                            placeholder="M, L, XL"
                            required>
                    </div>

                    COLOR
                    <div>
                        <label>Màu sắc</label>
                        <input
                            type="text"
                            name="color"
                            placeholder="Đen, Trắng..."
                            required>
                    </div> -->

                    <!-- QUANTITY
                    <div>
                        <label>Số lượng</label>
                        <input
                            type="number"
                            name="quantity"
                            min="0"
                            required>
                    </div> -->
                </div>

                <!-- IMAGE -->
                <div class="mb-3">
                    <label> Ảnh sản phẩm </label>
                    <input
                        type="file"
                        name="image"
                        accept="image/*"
                        class="form-control"
                        required>
                </div>

                <!-- DESCRIPTION -->
                <div>
                    <label> Mô tả </label>

                    <textarea
                        name="description"
                        rows="4"
                        placeholder="Mô tả sản phẩm..."
                        required></textarea>
                </div>

                <button
                    type="submit"
                    name="add_product"
                    class="save-btn">
                    <i class="fa fa-save"></i>
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
    </script>

    <div id="deleteModal" class="modal">
        <div class="modal-content" style="width:520px;">
            <div class="modal-header">
                <i class="fa-solid fa-circle-xmark modal-icon"></i>
                <h2>Xóa sản phẩm</h2>
            </div>

            <form method="POST">
                <input
                    type="hidden"
                    name="delete"
                    id="deleteId">

                <p style="text-align:center;">
                    Bạn có chắc muốn xóa sản phẩm này?
                </p>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="cancel-btn"
                        onclick="closeDeleteModal()">
                        Hủy
                    </button>

                    <button
                        type="submit"
                        class="save-btn">
                        Xóa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="successModal" class="modal">
        <div class="modal-content" style="width:520px;">
            <div class="modal-header">
                <i class="fa-solid fa-circle-check modal-icon success-icon"></i>
                <h2 id="successText">
                    Xóa sản phẩm thành công
                </h2>
            </div>

            <div class="modal-actions">
                <button
                    class="save-btn"
                    onclick="closeSuccessModal()">
                    OK
                </button>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(id){
            document.getElementById("deleteId").value=id;
            document.getElementById("deleteModal").style.display="flex";
        }
        function closeDeleteModal(){
            document.getElementById("deleteModal").style.display="none";
        }
        function closeSuccessModal(){
            document.getElementById("successModal").style.display="none";
        }
        window.onclick=function(e){
            if(e.target==document.getElementById("productModal")){
                closeModal();
            }
            if(e.target==document.getElementById("deleteModal")){
                closeDeleteModal();
            }
        }
    </script>

    <?php if (isset($_GET['deleted'])) { ?>
        <script>
        window.onload=function(){
            document.getElementById("successText").innerHTML =
                "Xóa sản phẩm thành công";
            document.getElementById("successModal").style.display="flex";
        }
        </script>
    <?php } ?>
</body>
</html>