<?php
require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

/* THÊM */
if (isset($_POST['addCategory'])) {
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $folder = '../../uploads/categories/';
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        $filename = time().'_'.basename($_FILES['image']['name']);

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $folder.$filename
        );

        $image = 'uploads/categories/'.$filename;
    }

    $stmt = $conn->prepare('
        INSERT INTO categories(
            name,
            slug,
            description,
            image,
            status,
            sort_order
        )
        VALUES(?,?,?,?,?,?)
    ');

    $stmt->execute([
        $_POST['name'],
        $_POST['slug'],
        $_POST['description'],
        $image,
        $_POST['status'],
        $_POST['sort_order'],
    ]);
    $categoryId = $conn->lastInsertId();
    writeLog(
        $conn,
        'CREATE',
        'Danh mục',
        'Thêm danh mục #'.$categoryId.' - '.$_POST['name']
    );

    header('Location: categories.php?success=Thêm');
    exit;
}

/* SỬA */
if (isset($_POST['updateCategory'])) {
    /* lấy ảnh cũ */
    $stmt = $conn->prepare('
    SELECT image
    FROM categories
    WHERE category_id = ?
    ');

    $stmt->execute([
        $_POST['category_id'],
    ]);

    $old = $stmt->fetch(PDO::FETCH_ASSOC);

    $image = $old['image'];

    if (!empty($_FILES['image']['name'])) {
        $folder = '../../uploads/categories/';

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $filename = time().'_'.basename($_FILES['image']['name']);

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $folder.$filename
        );

        // xóa ảnh cũ
        if (!empty($old['image'])) {
            $oldFile = '../../'.$old['image'];

            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $image = 'uploads/categories/'.$filename;
    }

    $stmt = $conn->prepare('
        UPDATE categories SET
            name=?,
            slug=?,
            description=?,
            image=?,
            status=?,
            sort_order=?
        WHERE category_id=?
    ');

    $stmt->execute([
        $_POST['name'],
        $_POST['slug'],
        $_POST['description'],
        $image,
        $_POST['status'],
        $_POST['sort_order'],
        $_POST['category_id'],
    ]);

    writeLog(
        $conn,
        'UPDATE',
        'Danh mục',
        'Sửa danh mục #'.$_POST['category_id'].' - '.$_POST['name']
    );

    header('Location: categories.php?success=Sửa');
    exit;
}

/* XÓA */
if (isset($_POST['deleteCategory'])) {
    $stmt = $conn->prepare('
        SELECT name
        FROM categories
        WHERE category_id=?
    ');

    $stmt->execute([
        $_POST['delete_id'],
    ]);

    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        header('Location: categories.php');
        exit;
    }

    $stmt = $conn->prepare('
        DELETE FROM categories
        WHERE category_id=?
    ');

    if ($stmt->execute([
        $_POST['delete_id'],
    ])) {
        writeLog(
            $conn,
            'DELETE',
            'Danh mục',
            'Xóa danh mục #'.$_POST['delete_id'].' - '.$category['name']
        );
    }

    header(
        'Location: categories.php?success=Xóa'
    );
    exit;
}

/* LẤY DỮ LIỆU */
$stmt = $conn->query('
    SELECT 
        categories.*,
        COUNT(products.product_id) AS product_count
    FROM categories
    LEFT JOIN products
    ON categories.category_id = products.category_id
    GROUP BY categories.category_id
    ORDER BY sort_order ASC, category_id DESC
');

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title> Quản lý danh mục </title>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

/* SIDEBAR*/
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

.main-content{
    margin-left:260px;
    width:100%;
    padding:30px;
}

.topbar{
    display:flex;
    justify-content:space-between;
}

.page-title h1{
    font-size:36px;
}

.page-title p{
    color:#777;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:25px 0;
}

.add-btn{
    background:#ff4fa3;
    color:white;
    border:none;
    padding:12px 18px;
    border-radius:12px;
    cursor:pointer;
}

.table-box{
    background:white;
    border-radius:22px;
    padding:25px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    text-align:left;
    color:#999;
    padding:15px;
}

td{
    padding:15px;
    border-top:1px solid #eee;
}

.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:13px;
}

.status-active{
    background:#e4fff0;
    color:#23b26d;
}

.hidden{
    background:#ffe4e4;
    color:#ff4d4d;
}

.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.45);
    justify-content:center;
    align-items:center;
    z-index:9999;
}

.modal-content{
    width:520px;
    background:white;
    padding:30px;
    border-radius:24px;
}

.modal-header{
    text-align:center;
    margin-bottom:20px;
}

.modal-icon{
    font-size:60px;
    color:#ff4fa3;
}

.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    font-weight:bold;
    margin-bottom:7px;
}

input,
textarea,
select{
    width:100%;
    padding:12px;
    border-radius:12px;
    border:1px solid #ddd;
}

.modal-actions{

    display:flex;
    justify-content:flex-end;
    gap:12px;
    margin-top:20px;
}

.cancel-btn{
    background:#eee;
    border:none;
    padding:12px 20px;
    border-radius:12px;
}

.save-btn{
    background:#ff4fa3;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:12px;
}

.success-icon{
    color:#23b26d;
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

/* LOGOUT */
.logout-btn{
    flex-shrink:0;
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px 18px;
    margin-top:15px;
    border-radius:14px;
    color:#ff4fa3;
    text-decoration:none;
    /* background:#fff0f7; */
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

            <a href="categories.php" class="active">
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

<!-- CONTENT -->
<div class="main-content">
    <div class="topbar">
        <div class="page-title">
            <h1>
                Quản lý danh mục
            </h1>

            <p>
                Quản lý danh mục sản phẩm
            </p>
        </div>

        <div class="admin-box">
            <img src="https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg?semt=ais_hybrid&w=740&q=80" class="admin-avatar">
            <div class="admin-info">
                <strong>Admin</strong><br>
                <div class="admin-role">Quản trị viên</div>
            </div>
        </div>
    </div>

    <div class="header">
        <h2>
            Danh sách danh mục
        </h2>

        <button
            class="add-btn"
            onclick="openAddModal()">
            + Thêm danh mục
        </button>
    </div>

    <div class="table-box">
        <table>
            <tr>
                <th>Tên</th>
                <th>Slug</th>
                <th>Mô tả</th>
                <th>Trạng thái</th>
                <th>Thứ tự</th>
                <th>Ảnh</th>
                <th>Thao tác</th>
            </tr>

            <?php foreach ($categories as $c) { ?>
            <tr>
                <td>
                    <strong>
                        <?php echo $c['name']; ?>
                    </strong>
                </td>

                <td>
                    <?php echo $c['slug']; ?>
                </td>

                <td>
                    <?php echo $c['description']; ?>
                </td>

                <td>
                    <?php if ($c['status'] == 1) { ?>
                    <span class="badge status-active">
                        Hoạt động
                    </span>

                    <?php } else { ?>

                    <span class="badge hidden">
                        Ẩn
                    </span>
                    <?php } ?>

                </td>

                <td>
                    <?php echo $c['sort_order']; ?>
                </td>

                <td>
                    <img
                        src="../../<?php echo $c['image'] ?: 'assets/no-image.png'; ?>"
                        style="
                            width:70px;
                            height:70px;
                            object-fit:cover;
                            border-radius:10px;
                        ">
                </td>

                <td>
                    <button onclick="location.href='get_products_category.php?category_id=<?php echo $c['category_id']; ?>'"
                        style="
                        width:38px;
                        height:38px;
                        border:none;
                        border-radius:10px;
                        background:#23b26d;
                        color:white;
                        cursor:pointer;">

                        <i class="fa fa-eye"></i>
                    </button>

                    <button
                        onclick='openEditPopup(<?php echo htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8'); ?>)'
                        style="
                        width:38px;
                        height:38px;
                        border:none;
                        border-radius:10px;
                        background:#ffb400;
                        color:white;
                        cursor:pointer;">

                        <i class="fa fa-pen"></i>
                    </button>

                    <button
                        type="button"
                        onclick="openDeletePopup(<?php echo $c['category_id']; ?>)"
                        style="
                        width:38px;
                        height:38px;
                        border:none;
                        border-radius:10px;
                        background:#ff4d6d;
                        color:white;
                        cursor:pointer;">

                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
</div>

<!-- ADD POPUP -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-folder modal-icon"></i>
            <h2>
                Thêm danh mục
            </h2>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>
                    Tên danh mục
                </label>

                <input
                    name="name"
                    required>
            </div>

            <div class="form-group">
                <label>
                    Slug
                </label>

                <input
                    name="slug">
            </div>

            <div class="form-group">
                <label>
                    Mô tả
                </label>

                <textarea
                    name="description">
                </textarea>
            </div>

            <div class="form-group">
                <label>Ảnh danh mục</label>

                <input
                    type="file"
                    name="image"
                    accept="image/*">
            </div>

            <div class="form-group">
                <label>
                    Trạng thái
                </label>

                <select name="status">
                    <option value="1">
                        Hoạt động
                    </option>

                    <option value="0">
                        Ẩn
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    Thứ tự
                </label>
                <input type="number" name="sort_order" value="0">
            </div>

            <div class="modal-actions">
                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeAddModal()">
                    Hủy
                </button>

                <button
                    name="addCategory"
                    class="save-btn">
                    Thêm
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT POPUP -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-pen modal-icon"></i>
            <h2>
                Sửa danh mục
            </h2>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="category_id" id="edit_id">
            <div class="form-group">
                <label>
                    Tên danh mục
                </label>
                <input name="name" id="edit_name">
            </div>

            <div class="form-group">
                <label>
                    Slug
                </label>
                <input name="slug" id="edit_slug">
            </div>

            <div class="form-group">
                <label>
                    Mô tả
                </label>

                <textarea name="description" id="edit_description"></textarea>
            </div>

            <div class="form-group">
                <label>Ảnh danh mục</label>
                <input
                    type="file"
                    name="image"
                    accept="image/*">

                <div style="margin-top:10px;">
                    <img
                        id="edit_preview"
                        src=""
                        style="
                            width:120px;
                            height:120px;
                            object-fit:cover;
                            border-radius:12px;
                            border:1px solid #ddd;">
                </div>
            </div>

            <div class="form-group">
                <label>
                    Trạng thái
                </label>

                <select
                    name="status"
                    id="edit_status">

                    <option value="1">
                        Hoạt động
                    </option>

                    <option value="0">
                        Ẩn
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    Thứ tự
                </label>
                <input type="number" name="sort_order" id="edit_sort">
            </div>

            <div class="modal-actions">
                <button type="button" class="cancel-btn" onclick="closeEditPopup()">
                    Hủy
                </button>

                <button type="submit" name="updateCategory" class="save-btn">
                    Lưu
                </button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE POPUP -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-circle-xmark modal-icon"></i>
            <h2>
                Xóa danh mục
            </h2>
        </div>

        <form method="POST">
            <input
                type="hidden"
                name="delete_id"
                id="delete_id">

            <p style="text-align:center">
                Bạn có chắc muốn xóa danh mục này?
            </p>

            <div class="modal-actions">
                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeDeletePopup()">
                    Hủy
                </button>

                <button type="submit" name="deleteCategory" class="save-btn">
                    Xóa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- PRODUCT POPUP -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-shirt modal-icon"></i>
            <h2 id="productTitle">
            </h2>
        </div>

        <div id="productList"></div>

        <div class="modal-actions">
            <button
                class="cancel-btn"
                onclick="closeProductPopup()">
                Đóng
            </button>
        </div>
    </div>
</div>

<!-- SUCCESS POPUP -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-circle-check modal-icon success-icon"></i>
            <h2 id="successText"></h2>
        </div>

        <div class="modal-actions">
            <button
                class="save-btn"
                onclick="closeSuccess()">
                OK
            </button>
        </div>
    </div>
</div>

<script>
    function openAddModal(){
        document
        .getElementById("addModal")
        .style.display="flex";
    }

    function closeAddModal(){
        document
        .getElementById("addModal")
        .style.display="none";
    }

    function openEditPopup(data){
        document
        .getElementById("editModal")
        .style.display="flex";

        document.getElementById("edit_id")
        .value=data.category_id;

        document.getElementById("edit_name")
        .value=data.name;

        document.getElementById("edit_slug")
        .value=data.slug;

        document.getElementById("edit_description")
        .value=data.description;

        document.getElementById("edit_status")
        .value=data.status;

        document.getElementById("edit_sort")
        .value=data.sort_order;

        const preview = document.getElementById("edit_preview");
        preview.src = data.image
            ? "../../" + data.image
            : "../../assets/no-image.png";
    }

    function closeEditPopup(){
        document
        .getElementById("editModal")
        .style.display="none";
    }

    function openDeletePopup(id){
        document
        .getElementById("deleteModal")
        .style.display="flex";

        document
        .getElementById("delete_id")
        .value=id;
    }

    function closeDeletePopup(){
        document
        .getElementById("deleteModal")
        .style.display="none";
    }

    function closeSuccess(){
        document
        .getElementById("successModal")
        .style.display="none";
    }

    <?php if (isset($_GET['success'])) { ?>
        window.onload=function(){
            document
            .getElementById("successText")
            .innerHTML =
            "<?php echo $_GET['success']; ?> danh mục thành công";
            document
            .getElementById("successModal")
            .style.display="flex";
        }
    <?php } ?>
</script>

</body>
</html>