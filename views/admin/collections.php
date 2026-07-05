<?php
session_start();

require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

/* THÊM BỘ SƯU TẬP */
if (isset($_POST['addCollection'])) {
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $folder = '../../uploads/collections/';
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        $filename = time().'_'.basename($_FILES['image']['name']);
        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $folder.$filename
        );
        $image = 'uploads/collections/'.$filename;
    }
    $stmt = $conn->prepare('
        INSERT INTO collections(
            name,
            slug,
            image,
            description,
            status,)
        VALUES( ?,?,?,?,?,?)
    ');
    $stmt->execute([
        $_POST['name'],
        $_POST['slug'],
        $image,
        $_POST['description'],
        $_POST['status'],
    ]);
    $collectionId = $conn->lastInsertId();
    writeLog(
        $conn,
        'CREATE',
        'Bộ sưu tập',
        'Thêm bộ sưu tập #'.$collectionId.' - '.$_POST['name']
    );

    header('Location: collections.php?success=add');
    exit;
}

/* SỬA */
if (isset($_POST['updateCollection'])) {
    $stmt = $conn->prepare('
        SELECT image
        FROM collections
        WHERE collection_id=?
    ');

    $stmt->execute([
        $_POST['collection_id'],
    ]);

    $old = $stmt->fetch(PDO::FETCH_ASSOC);

    $image = $old['image'];

    if (!empty($_FILES['image']['name'])) {
        $folder = '../../uploads/collections/';

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if (!empty($old['image'])) {
            $oldFile = '../../'.$old['image'];

            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $filename = time().'_'.basename($_FILES['image']['name']);

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $folder.$filename
        );

        $image = 'uploads/collections/'.$filename;
    }

    $stmt = $conn->prepare('
        UPDATE collections
        SET
            name=?,
            slug=?,
            image=?,
            description=?,
            status=?
        WHERE collection_id=?
    ');
    $stmt->execute([
        $_POST['name'],
        $_POST['slug'],
        $image,
        $_POST['description'],
        $_POST['status'],
        $_POST['collection_id'],
    ]);
    writeLog(
        $conn,
        'UPDATE',
        'Bộ sưu tập',
        'Sửa bộ sưu tập #'.$_POST['collection_id'].' - '.$_POST['name']
    );
    header('Location: collections.php?success=edit');
    exit;
}

/* XÓA */
if (isset($_POST['deleteCollection'])) {
    $stmt = $conn->prepare('
        SELECT *
        FROM collections
        WHERE collection_id=?
    ');

    $stmt->execute([
        $_POST['delete_id'],
    ]);

    $collection = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($collection) {
        if (!empty($collection['image'])) {
            $file = '../../'.$collection['image'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $stmt = $conn->prepare('
            UPDATE products
            SET collection_id=NULL
            WHERE collection_id=?
        ');
        $stmt->execute([
            $_POST['delete_id'],
        ]);
        $stmt = $conn->prepare('
            DELETE
            FROM collections
            WHERE collection_id=?
        ');
        $stmt->execute([
            $_POST['delete_id'],
        ]);
        writeLog(
            $conn,
            'DELETE',
            'Bộ sưu tập',
            'Xóa bộ sưu tập #'.$_POST['delete_id'].' - '.$collection['name']
        );
    }
    header('Location: collections.php?success=delete');
    exit;
}

/* LẤY DỮ LIỆU */

$stmt = $conn->query('
SELECT
    c.*,
    COUNT(p.product_id) product_count
FROM collections c

LEFT JOIN products p
ON c.collection_id=p.collection_id

GROUP BY c.collection_id

ORDER BY
c.collection_id DESC
');

$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <title>
    Quản lý bộ sưu tập
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

        /*  SIDEBAR  */

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
            color:#fff;
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

        /*  CONTENT  */

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
            margin-bottom:8px;
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
            color:white;
            border:none;
            padding:14px 22px;
            border-radius:14px;
            cursor:pointer;
            font-weight:bold;
        }

        .table-box{
            background:white;
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

        .collection-info{
            display:flex;
            align-items:center;
            gap:15px;
        }

        .collection-info img{
            width:70px;
            height:70px;
            object-fit:cover;
            border-radius:14px;
        }

        /* POPUP */
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
            width:650px;
            max-height:90vh;
            overflow-y:auto;
            background:white;
            padding:30px;
            border-radius:24px;
        }

        .modal-header{
            text-align:center;
            margin-bottom:25px;
        }

        .modal-header h2{
            margin-top:10px;
        }

        .modal-icon{
            font-size:60px;
            color:#ff4fa3;
        }

        .form-group{
            margin-bottom:16px;
        }

        .form-group label{
            display:block;
            font-weight:bold;
            margin-bottom:8px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select{
            width:100%;
            padding:12px;
            border:1px solid #ddd;
            border-radius:12px;
            outline:none;
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
        }

        .save-btn{
            background:#ff4fa3;
            color:white;
            border:none;
            padding:12px 22px;
            border-radius:12px;
            cursor:pointer;
        }

        .save-btn:hover{
            background:#ff2f92;
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

    <!--  CONTENT  -->
    <div class="main-content">
        <div class="topbar">
            <div class="page-title">
                <h1>
                    Quản lý bộ sưu tập
                </h1>

                <p>
                    Quản lý các bộ sưu tập sản phẩm
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

        <div class="header">
            <h2>
                Danh sách bộ sưu tập
            </h2>

            <button
                class="add-btn"
                onclick="openAddModal()">

                <i class="fa-solid fa-plus"></i>
                Thêm bộ sưu tập
            </button>
        </div>

        <div class="table-box">
            <table>
                <tr>
                    <th>Bộ sưu tập</th>
                    <th>Slug</th>
                    <th>Sản phẩm</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>

                <?php foreach ($collections as $c) { ?>
                <tr>
                    <td>
                        <div class="collection-info">
                            <?php if (!empty($c['image'])) { ?>
                                <img src="../../<?php echo $c['image']; ?>">
                            <?php } else { ?>
                                <img src="../../uploads/no-image.png">
                            <?php } ?>

                            <div>
                                <strong>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </strong>

                                <br>
                                <small>
                                    <?php echo htmlspecialchars($c['description']); ?>
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php echo $c['slug']; ?>
                    </td>

                    <td>
                        <?php echo $c['product_count']; ?>
                        sản phẩm
                    </td>

                    <td>
                        <?php if ($c['status'] == 1) { ?>
                            <span
                            style="
                            background:#e7fff1;
                            color:#18a558;
                            padding:8px 14px;
                            border-radius:30px;
                            font-size:13px;">
                            Hoạt động
                            </span>

                        <?php } else { ?>
                            <span
                            style="
                            background:#ffe8e8;
                            color:#d80027;
                            padding:8px 14px;
                            border-radius:30px;
                            font-size:13px;">
                            Ẩn
                            </span>
                        <?php } ?>
                    </td>

                    <td>
                        <button
                        onclick="location.href='collection_products.php?id=<?php echo $c['collection_id']; ?>'"
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
                        onclick='openEditModal(<?php echo htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8'); ?>)'
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
                        onclick="openDeleteModal(<?php echo $c['collection_id']; ?>)"
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

    <!--  ADD POPUP  -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa-solid fa-images modal-icon"></i>
                <h2>Thêm bộ sưu tập</h2>
            </div>

            <form
                method="POST"
                enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tên bộ sưu tập</label>
                    <input
                        type="text"
                        name="name"
                        required>
                </div>

                <div class="form-group">
                    <label>Slug</label>
                    <input
                        type="text"
                        name="slug">
                </div>

                <div class="form-group">
                    <label>Ảnh bộ sưu tập</label>
                    <input
                        type="file"
                        name="image"
                        accept="image/*">
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea
                        name="description"
                        rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="status">
                        <option value="1">
                            Hoạt động
                        </option>

                        <option value="0">
                            Ẩn
                        </option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="cancel-btn"
                        onclick="closeAddModal()">
                        Hủy
                    </button>

                    <button
                        type="submit"
                        name="addCollection"
                        class="save-btn">
                        Thêm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!--  EDIT POPUP  -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa-solid fa-pen modal-icon"></i>
                <h2>Sửa bộ sưu tập</h2>
            </div>

            <form
                method="POST"
                enctype="multipart/form-data">

                <input
                    type="hidden"
                    id="edit_id"
                    name="collection_id">

                <div class="form-group">
                    <label>Tên bộ sưu tập</label>
                    <input
                        type="text"
                        id="edit_name"
                        name="name"
                        required>
                </div>

                <div class="form-group">
                    <label>Slug</label>
                    <input
                        type="text"
                        id="edit_slug"
                    name="slug">

                </div>

                <div class="form-group">
                    <label>Ảnh hiện tại</label>
                    <br>
                    <img
                        id="edit_preview"
                        src="../../uploads/no-image.png"
                        style="
                        width:110px;
                        height:110px;
                        object-fit:cover;
                        border-radius:12px;
                        margin-bottom:10px;">

                    <input
                        type="file"
                        name="image"
                        accept="image/*">
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea
                        id="edit_description"
                        name="description"
                        rows="4"></textarea>

                </div>

                <div class="form-group">
                    <label>Trạng thái</label>
                    <select
                        id="edit_status"
                        name="status">

                        <option value="1">
                            Hoạt động
                        </option>

                        <option value="0">
                            Ẩn
                        </option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="cancel-btn"
                        onclick="closeEditModal()">
                        Hủy
                    </button>

                    <button
                        type="submit"
                        name="updateCollection"
                        class="save-btn">
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!--  DELETE  -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa-solid fa-circle-xmark modal-icon"></i>
                <h2>Xóa bộ sưu tập</h2>
            </div>

            <form method="POST">
                <input
                    type="hidden"
                    id="delete_id"
                    name="delete_id">

                <p style="
                    text-align:center;
                    margin:20px 0;">
                    Bạn có chắc muốn xóa bộ sưu tập này?

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
                        name="deleteCollection"
                        class="save-btn">
                        Xóa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!--  SUCCESS  -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i
                class="fa-solid fa-circle-check modal-icon"
                style="color:#23b26d;"></i>
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
        /* ADD*/
        function openAddModal() {
            document.getElementById("addModal").style.display = "flex";
        }
        function closeAddModal() {
            document.getElementById("addModal").style.display = "none";
        }

        /*  EDIT */
        function openEditModal(data) {
            document.getElementById("editModal").style.display = "flex";
            document.getElementById("edit_id").value = data.collection_id;
            document.getElementById("edit_name").value = data.name;
            document.getElementById("edit_slug").value = data.slug ?? "";
            document.getElementById("edit_description").value = data.description ?? "";
            document.getElementById("edit_status").value = data.status;
            if (data.image && data.image !== "") {
                document.getElementById("edit_preview").src =
                    "../../" + data.image;
            } else {
                document.getElementById("edit_preview").src =
                    "../../uploads/no-image.png";
            }
        }

        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }

        /* DELETE*/
        function openDeleteModal(id) {
            document.getElementById("deleteModal").style.display = "flex";
            document.getElementById("delete_id").value = id;
        }
        function closeDeleteModal() {
            document.getElementById("deleteModal").style.display = "none";
        }

        /*  SUCCESS  */
        function closeSuccess() {
            document.getElementById("successModal").style.display = "none";
        }

        /*  CLICK OUTSIDE MODAL*/
        window.onclick = function(e) {
            const addModal = document.getElementById("addModal");
            const editModal = document.getElementById("editModal");
            const deleteModal = document.getElementById("deleteModal");
            const successModal = document.getElementById("successModal");
            if (e.target === addModal) {
                closeAddModal();
            }
            if (e.target === editModal) {
                closeEditModal();
            }
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
            if (e.target === successModal) {
                closeSuccess();
            }
        };

        /* SUCCESS MESSAGE  */
        <?php if (isset($_GET['success'])) { ?>
            window.onload = function () {
                let text = "";
                switch ("<?php echo $_GET['success']; ?>") {

                    case "add":
                        text = "Thêm bộ sưu tập thành công";
                        break;

                    case "edit":
                        text = "Cập nhật bộ sưu tập thành công";
                        break;

                    case "delete":
                        text = "Xóa bộ sưu tập thành công";
                        break;

                }
                document.getElementById("successText").innerHTML = text;
                document.getElementById("successModal").style.display = "flex";
            };
        <?php } ?>

        document.querySelector('input[name="image"]').addEventListener("change",function(e){
            if(e.target.files.length){
                document.getElementById("edit_preview").src=
                    URL.createObjectURL(e.target.files[0]);
            }
        });

    </script>

</body>
</html>