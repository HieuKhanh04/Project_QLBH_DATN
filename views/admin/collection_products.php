<?php

session_start();

require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

if (!isset($_GET['id'])) {
    header('Location: collections.php');
    exit;
}

$collectionId = (int) $_GET['id'];

/* LẤY THÔNG TIN BỘ SƯU TẬP */
$stmt = $conn->prepare('
    SELECT *
    FROM collections
    WHERE collection_id = ?
');
$stmt->execute([$collectionId]);

$collection = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$collection) {
    exit('Bộ sưu tập không tồn tại.');
}

/* THÊM SẢN PHẨM VÀO BST */
if (isset($_POST['addProducts'])) {
    if (empty($_POST['products'])) {
        header('Location: collection_products.php?id='.$collectionId);
        exit;
    }
    $stmt = $conn->prepare('
        UPDATE products
        SET collection_id = ?
        WHERE product_id = ?
        AND (collection_id IS NULL OR collection_id = 0)
    ');
    foreach ($_POST['products'] as $id) {
        $stmt->execute([
            $collectionId,
            $id,
        ]);
    }
    writeLog(
        $conn,
        'UPDATE',
        'Bộ sưu tập',
        'Thêm sản phẩm vào BST '.$collection['name']
    );
    header('Location: collection_products.php?id='.$collectionId.'&success=add');
    exit;
}

/* XÓA SẢN PHẨM KHỎI BST */
if (isset($_POST['removeProduct'])) {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $stmt = $conn->prepare('
        UPDATE products
        SET collection_id = NULL
        WHERE product_id = ?
        AND collection_id = ?
    ');
    $stmt->execute([
        $productId,
        $collectionId,
    ]);
    writeLog(
        $conn,
        'UPDATE',
        'Bộ sưu tập',
        'Xóa sản phẩm khỏi BST '.$collection['name']
    );
    header('Location: collection_products.php?id='.$collectionId.'&success=remove');
    exit;
}

/* SẢN PHẨM ĐANG THUỘC BST */
$stmt = $conn->prepare('
    SELECT
        p.*,
        c.name AS category_name,
        pi.image_url
    FROM products p

    LEFT JOIN categories c
    ON p.category_id = c.category_id

    LEFT JOIN product_images pi
    ON p.product_id = pi.product_id
    AND pi.is_main = 1
    WHERE p.collection_id = ?
    ORDER BY p.product_id DESC
');

$stmt->execute([$collectionId]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare('
    SELECT
        p.product_id,
        p.name
    FROM products p
    WHERE(
        p.collection_id IS NULL
        OR
        p.collection_id=0)
    ORDER BY p.name
');
$stmt->execute();
$availableProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>
        Quản lý sản phẩm trong bộ sưu tập
    </title>

    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial;
            background:#fff5f9;
        }

        .admin-container{
            display:flex;
        }

        /* ========= SIDEBAR ========= */

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
            font-family:'Great Vibes',cursive;
            font-size:42px;
            color:#ff4fa3;
            text-decoration:none;
            font-weight:bold;
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
            transition:.2s;
        }

        .menu a:hover{
            background:#fff0f7;
            color:#ff4fa3;
        }

        .menu .active{
            background:#ff4fa3;
            color:white;
        }

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

        /* ========= CONTENT ========= */

        .main-content{
            flex:1;
            margin-left:260px;
            padding:30px;
        }

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

        .header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:25px;
        }

        .add-btn{
            background:#ff4fa3;
            color:#fff;
            border:none;
            padding:14px 22px;
            border-radius:14px;
            cursor:pointer;
            font-weight:bold;
        }

        .table-box{
            background:#fff;
            border-radius:24px;
            padding:25px;
            box-shadow:0 4px 12px rgba(0,0,0,.05);
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th{
            text-align:left;
            color:#999;
            padding-bottom:18px;
        }

        td{
            padding:18px 0;
            border-top:1px solid #f3f3f3;
        }

        .product-info{
            display:flex;
            align-items:center;
            gap:15px;
        }

        .product-info img{
            width:70px;
            height:70px;
            border-radius:14px;
            object-fit:cover;
        }

        .status{
            padding:8px 14px;
            border-radius:30px;
            font-size:13px;
            font-weight:bold;
        }

        .active-status{
            background:#e7fff1;
            color:#1fa463;
        }

        .hide-status{
            background:#ffe8e8;
            color:#d60000;
        }

        .action-btn{
            width:38px;
            height:38px;
            border:none;
            border-radius:10px;
            color:#fff;
            cursor:pointer;
            background:#ff4d6d;
        }

        /*  MODAL*/
        .modal{
            display:none;
            position:fixed;
            left:0;
            top:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,.45);
            justify-content:center;
            align-items:center;
            z-index:9999;
        }

        .modal-content{
            width:600px;
            background:white;
            border-radius:24px;
            padding:30px;
        }

        .modal-header{
            text-align:center;
            margin-bottom:25px;
        }

        .modal-icon{
            font-size:60px;
            color:#ff4fa3;
            margin-bottom:10px;
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
        .form-group textarea,
        .form-group select{
            width:100%;
            padding:14px;
            border:1px solid #eee;
            border-radius:12px;
            outline:none;
        }

        .form-control{
            width:100%;
            padding:14px;
            border:1px solid #eee;
            border-radius:12px;
        }

        .modal-actions{
            display:flex;
            justify-content:flex-end;
            gap:12px;
            margin-top:25px;
        }

        .cancel-btn{
            background:#ddd;
            border:none;
            color:#333;
            padding:12px 20px;
            border-radius:12px;
            cursor:pointer;
        }

        .save-btn{
            background:#ff4fa3;
            color:white;
            border:none;
            padding:12px 20px;
            border-radius:12px;
            cursor:pointer;
        }

        .save-btn:disabled{
            background:#ccc;
            cursor:not-allowed;
        }

        .back-btn{
            display:inline-block;
            margin-bottom:20px;
            background:#ff4fa3;
            color:white;
            border-radius:12px;
            text-decoration:none;
            border:none;
            padding:14px 22px;
            border-radius:14px;
            cursor:pointer;
            margin-left:10px;
        }

        .bottom-actions{
            margin-top:20px;
            display:flex;
            justify-content:flex-start;
        }
    </style>
</head>

<body>
<div class="admin-container">
    <!--  SIDEBAR  -->
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

                <a href="products.php">
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

                <a href="categories.php">
                    <i class="fa-regular fa-folder"></i>
                    Danh mục
                </a>

                <a href="collections.php" class="active">
                    <i class="fa-solid fa-images"></i>
                    Bộ sưu tập
                </a>

                <a href="notifications.php">
                    <i class="fa-regular fa-bell"></i>
                    Thông báo
                </a>

                <div class="menu-title">
                    HỆ THỐNG
                </div>

                <a href="#">
                    <i class="fa-solid fa-gear"></i>
                    Cài đặt
                </a>

                <a href="account.php">
                    <i class="fa-regular fa-user"></i>
                    Tài khoản
                </a>

                <a href="activity_logs.php">
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

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="topbar">
            <div class="page-title">
                <h1>
                    <?php echo htmlspecialchars($collection['name']); ?>
                </h1>

                <p>
                    Quản lý sản phẩm trong bộ sưu tập
                </p>
            </div>

            <div class="admin-box">
                <img src="https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg?semt=ais_hybrid&w=740&q=80">
                <div>
                    <strong>Admin</strong><br>
                    <small>Quản trị viên</small>
                </div>
            </div>
        </div>

        <!-- ACTION -->
        <div class="header">
            <h2>
                Danh sách sản phẩm
            </h2>

            <div>
                <button
                    class="add-btn"
                    onclick="openAddProductModal()">
                    <i class="fa-solid fa-plus"></i>
                    Thêm sản phẩm
                </button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-box">
            <table>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>

                <?php if (count($products) > 0) { ?>
                    <?php foreach ($products as $product) { ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <?php if (!empty($product['image_url'])) { ?>
                                    <img
                                        src="../../<?php echo $product['image_url']; ?>"
                                        onerror="this.src='../../uploads/no-image.png'">
                                <?php } else { ?>
                                    <img src="../../uploads/no-image.png">
                                <?php } ?>

                                <div>
                                    <strong>
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </strong>

                                    <br>
                                    <small>
                                        #SP<?php echo $product['product_id']; ?>
                                    </small>
                                </div>
                            </div>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </td>

                        <td>
                            <?php if ($product['status'] == 1) { ?>
                                <span class="status active-status">
                                    Hiển thị
                                </span>

                            <?php } else { ?>
                                <span class="status hide-status">
                                    Ẩn
                                </span>
                            <?php } ?>
                        </td>

                        <td>
                            <button
                                class="action-btn"
                                onclick="openRemoveModal(<?php echo $product['product_id']; ?>)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td
                            colspan="5"
                            style="
                                text-align:center;
                                padding:45px;
                                color:#999;">
                            Chưa có sản phẩm nào trong bộ sưu tập.
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <div class="bottom-actions">
            <a href="collections.php" class="back-btn">
                <i class="fa fa-arrow-left"></i>
                Quay lại
            </a>
        </div>

        <!-- <div class="bottom-actions">
            <button
                class="back-btn"
                onclick="location.href='collections.php'">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại
            </button>
        </div> -->
    </div>

    <!-- POPUP THÊM SẢN PHẨM -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa-solid fa-plus modal-icon"></i>
                <h2>Thêm sản phẩm vào bộ sưu tập</h2>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Chọn sản phẩm</label>
                    <select
                        name="products[]"
                        multiple
                        size="12"
                        class="form-control">

                        <?php if (count($availableProducts) > 0) { ?>
                            <?php foreach ($availableProducts as $p) { ?>
                                <option
                                    value="<?php echo $p['product_id']; ?>">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </option>
                            <?php } ?>
                        <?php } else { ?>
                            <option disabled>
                                Không còn sản phẩm nào để thêm.
                            </option>
                        <?php } ?>
                    </select>

                    <small
                    style="
                    display:block;
                    margin-top:10px;
                    color:#888;">
                        Giữ phím Ctrl để chọn nhiều sản phẩm.
                    </small>
                </div>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="cancel-btn"
                        onclick="closeAddProductModal()">
                        Hủy
                    </button>

                    <button
                        type="submit"
                        name="addProducts"
                        class="save-btn"
                        <?php if (count($availableProducts) == 0) {
                            echo 'disabled';
                        } ?>>
                        Thêm vào bộ sưu tập
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- POPUP XÓA -->
    <div id="removeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i
                class="fa-solid fa-circle-xmark modal-icon"
                style="color:#ff4d6d;"></i>

                <h2>
                    Xóa sản phẩm
                </h2>
            </div>

            <form method="POST">
                <input
                    type="hidden"
                    id="remove_product_id"
                    name="product_id">
                <p
                style="
                text-align:center;
                margin:30px 0;
                font-size:17px;">
                    Bạn có chắc muốn xóa sản phẩm này khỏi bộ sưu tập?
                </p>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="cancel-btn"
                        onclick="closeRemoveModal()">
                        Hủy
                    </button>

                    <button
                        type="submit"
                        name="removeProduct"
                        class="save-btn">
                        Xóa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- POPUP THÀNH CÔNG -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i
                class="fa-solid fa-circle-check modal-icon"
                style="color:#22b46d;font-size:70px;"></i>
                <h2 id="successText"></h2>
            </div>

            <div class="modal-actions"
            style="justify-content:center;">
                <button
                    class="save-btn"
                    onclick="closeSuccess()">
                    OK
                </button>
            </div>
        </div>
    </div>

    <script>
        /* THÊM SẢN PHẨM*/
        function openAddProductModal(){
            document.getElementById("addProductModal").style.display="flex";
        }
        function closeAddProductModal(){
            document.getElementById("addProductModal").style.display="none";
        }

        /* XÓA SẢN PHẨM */
        function openRemoveModal(productId){
            document.getElementById("remove_product_id").value=productId;
            document.getElementById("removeModal").style.display="flex";
        }
        function closeRemoveModal(){
            document.getElementById("removeModal").style.display="none";
        }

        /* THÀNH CÔNG */
        function closeSuccess(){
            document.getElementById("successModal").style.display="none";
        }

        /* CLICK NGOÀI POPUP */
        window.onclick=function(e){
            let add=document.getElementById("addProductModal");
            let remove=document.getElementById("removeModal");
            let success=document.getElementById("successModal");
            if(e.target===add){
                closeAddProductModal();
            }
            if(e.target===remove){
                closeRemoveModal();
            }
            if(e.target===success){
                closeSuccess();
            }
        }

        /* POPUP THÀNH CÔNG */
        <?php if (isset($_GET['success'])) { ?>
            window.onload=function(){
                let text="";
                switch("<?php echo $_GET['success']; ?>"){
                    case "add":
                        text="Đã thêm sản phẩm vào bộ sưu tập.";
                        break;
                    case "remove":
                        text="Đã xóa sản phẩm khỏi bộ sưu tập.";
                        break;
                }
                document.getElementById("successText").innerHTML=text;
                document.getElementById("successModal").style.display="flex";
            };
        <?php } ?>

    </script>

</body>

</html>