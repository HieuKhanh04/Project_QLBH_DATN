<?php
require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

/* THÊM */
if (isset($_POST['addNotification'])) {
    $stmt = $conn->prepare('
        INSERT INTO notifications(
            title,
            content,
            type,
            status,
            start_date,
            end_date,
            is_pinned)
        VALUES(?,?,?,?,?,?,?)
    ');
    $stmt->execute([
        $_POST['title'],
        $_POST['content'],
        $_POST['type'],
        $_POST['status'],
        !empty($_POST['start_date']) ? $_POST['start_date'] : null,
        !empty($_POST['end_date']) ? $_POST['end_date'] : null,
        isset($_POST['is_pinned']) ? 1 : 0,
    ]);
    $notification_id = $conn->lastInsertId();
    writeLog(
        $conn,
        'CREATE',
        'Thông báo',
        'Thêm thông báo #'.$notification_id.' - '.$_POST['title']
    );
    header('Location: notifications.php?success=Thêm');
    exit;
}

/* SỬA */
if (isset($_POST['updateNotification'])) {
    $stmt = $conn->prepare('
        UPDATE notifications
        SET
            title=?,
            content=?,
            type=?,
            status=?,
            start_date=?,
            end_date=?,
            is_pinned=?,
            updated_at=NOW()
        WHERE notification_id=?
    ');

    $stmt->execute([
        $_POST['title'],
        $_POST['content'],
        $_POST['type'],
        $_POST['status'],
        !empty($_POST['start_date']) ? $_POST['start_date'] : null,
        !empty($_POST['end_date']) ? $_POST['end_date'] : null,
        isset($_POST['is_pinned']) ? 1 : 0,
        $_POST['notification_id'],
    ]);
    writeLog(
        $conn,
        'UPDATE',
        'Thông báo',
        'Cập nhật thông báo #'.$_POST['notification_id'].' - '.$_POST['title']
    );

    header('Location: notifications.php?success=Sửa');
    exit;
}

/* XÓA */
if (isset($_POST['deleteNotification'])) {
    $stmt = $conn->prepare('
        SELECT title
        FROM notifications
        WHERE notification_id = ?
    ');
    $stmt->execute([$_POST['delete_id']]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare('
        DELETE FROM notifications
        WHERE notification_id=?
    ');

    $stmt->execute([
        $_POST['delete_id'],
    ]);

    writeLog(
        $conn,
        'DELETE',
        'Thông báo',
        'Xóa thông báo #'.$_POST['delete_id'].' - '.($notification['title'] ?? '')
    );

    header('Location: notifications.php?success=Xóa');
    exit;
}

/* TÌM KIẾM */
$keyword = trim($_GET['keyword'] ?? '');

$sql = '
    SELECT *
    FROM notifications
    WHERE 1
';

$params = [];

if ($keyword != '') {
    $sql .= '
        AND (
            title LIKE ?
            OR content LIKE ?
            OR type LIKE ?
            OR status LIKE ?
        )
    ';

    $search = "%{$keyword}%";
    $params = [$search, $search, $search, $search];
}

$sql .= '
ORDER BY
    is_pinned DESC,
    created_at DESC
';

$stmt = $conn->prepare($sql);
$stmt->execute($params);

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Quản lý thông báo</title>

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

.logout-btn{
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px 18px;
    border-radius:14px;
    /* background:#fff0f7; */
    color:#ff4fa3;
    text-decoration:none;
    margin-top:15px;
}

.logout-btn:hover{
    background:#ff4fa3;
    color:white;
}

.main-content{
    flex:1;
    margin-left:260px;
    padding:30px;
}

.topbar{
    display:flex;
    justify-content:space-between;
    margin-bottom:30px;
}

.page-title h1{
    font-size:36px;
    margin-bottom:10px;
}

.page-title p{
    color:#777;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.search-group{
    display:flex;
    align-items:center;
    gap:12px;
}

.search-box{
    width:340px;
    height:48px;
    display:flex;
    align-items:center;
    background:#fff;
    border:1px solid #ffd9ea;
    border-radius:14px;
    overflow:hidden;
}

.search-box input{
    flex:1;
    border:none;
    outline:none;
    padding:0 15px;
    font-size:15px;
    background:transparent;
}

.search-box button{
    width:50px;
    height:100%;
    border:none;
    background:none;
    color:#ff4fa3;
    cursor:pointer;
    font-size:16px;
}

.search-box button:hover{
    background:#fff0f7;
}

.reset-btn{
    display:flex;
    align-items:center;
    justify-content:center;
    height:48px;
    padding:0 20px;
    border-radius:14px;
    background:#eee;
    color:#555;
    text-decoration:none;
    font-weight:bold;
    transition:.2s;
}

.reset-btn:hover{
    background:#ddd;
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

.table-box{
    background:white;
    border-radius:22px;
    padding:25px;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    text-align:left;
    color:#999;
    padding-bottom:15px;
}

table td{
    padding:18px 0;
    border-top:1px solid #f3f3f3;
}

.add-btn{
    background:#ff4fa3;
    color:white;
    border:none;
    padding:14px 22px;
    border-radius:14px;
    cursor:pointer;
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
.form-group textarea,
.form-group select{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:12px;
    font-size:14px;
}

.form-group textarea{
    resize:vertical;
    min-height:120px;
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

            <a href="categories.php">
                <i class="fa-regular fa-folder"></i>
                Danh mục
            </a>

            <a href="collections.php">
                <i class="fa-solid fa-layer-group"></i>
                Bộ sưu tập
            </a>

            <a href="notifications.php" class="active">
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
                Quản lý thông báo
            </h1>

            <p>
                Quản lý thông báo hệ thống
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
        <h2>Danh sách thông báo</h2>
        <div style="display:flex;align-items:center;gap:15px;">
            <div class="search-group">
                <form method="GET" class="search-box">
                    <input
                        type="text"
                        name="keyword"
                        placeholder="Tìm tiêu đề, nội dung, loại..."
                        value="<?php echo htmlspecialchars($keyword); ?>">
                    <button type="submit">
                        <i class="fa fa-search"></i>
                    </button>
                </form>

                <?php if ($keyword != '') { ?>
                    <a href="notifications.php" class="reset-btn">
                        Tất cả
                    </a>
                <?php } ?>
            </div>

            <button
                class="add-btn"
                onclick="openAddModal()">
                + Thêm thông báo
            </button>
        </div>
    </div>

    <div class="table-box">
        <table>
            <tr>
                <th>Tiêu đề</th>
                <th>Loại</th>
                <th>Ghim</th>
                <th>Hiệu lực</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>

            <?php foreach ($notifications as $n) { ?>

            <tr>
                <td>
                    <strong>
                        <?php echo htmlspecialchars($n['title']); ?>
                    </strong>
                </td>

                <td>
                    <?php
                    switch ($n['type']) {
                        case 'promotion':
                            echo 'Khuyến mãi';
                            break;

                        case 'voucher':
                            echo 'Voucher';
                            break;

                        case 'maintenance':
                            echo 'Bảo trì';
                            break;

                        default:
                            echo 'Hệ thống';
                    }
                ?>

                </td>

                <td>

                    <?php if ($n['is_pinned']) { ?>

                        <span class="badge active">
                            Đã ghim
                        </span>

                    <?php } else { ?>

                        <span class="badge expired">
                            Không
                        </span>

                    <?php } ?>
                </td>

                <td>
                    <?php
                echo $n['start_date']
                    ? date('d/m/Y', strtotime($n['start_date']))
                    : '-';
                ?>

                    →

                    <?php
                echo $n['end_date']
                    ? date('d/m/Y', strtotime($n['end_date']))
                    : '-';
                ?>
                </td>

                <td>

                    <?php if ($n['status']) { ?>
                        <span class="badge active">
                            Hiển thị
                        </span>

                    <?php } else { ?>
                        <span class="badge expired">
                            Ẩn
                        </span>

                    <?php } ?>

                </td>

                <td>
                    <?php echo date('d/m/Y', strtotime($n['created_at'])); ?>
                </td>

                <td>
                    <div style="display:flex;gap:10px;">
                        <!-- XEM -->
                        <button
                            onclick='openViewPopup(<?php echo htmlspecialchars(json_encode($n), ENT_QUOTES, 'UTF-8'); ?>)'
                            style="
                                width:38px;
                                height:38px;
                                border:none;
                                border-radius:10px;
                                background:#23b26d;
                                color:white;
                                cursor:pointer;">

                            <i class="fa-solid fa-eye"></i>
                        </button>
                
                        <button
                            onclick='openEditPopup(<?php echo json_encode($n); ?>)'
                            style="
                                width:38px;
                                height:38px;
                                border:none;
                                border-radius:10px;
                                background:#ffb400;
                                color:white;
                                cursor:pointer;">

                            <i class="fa-solid fa-pen"></i>

                        </button>

                        <button
                            onclick="openDeletePopup(<?php echo $n['notification_id']; ?>)"
                            style="
                                width:38px;
                                height:38px;
                                border:none;
                                border-radius:10px;
                                background:#ff4d6d;
                                color:white;
                                cursor:pointer;">

                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

</div>

<!-- ADD MODAL -->
<div id="addModal" class="modal">
    <div class="modal-content">

        <div class="modal-header">
            <i class="fa-solid fa-bell modal-icon"></i>

            <h2>
                Thêm thông báo
            </h2>
        </div>

        <form method="POST">

            <div class="form-group">
                <label>Tiêu đề</label>

                <input
                    type="text"
                    name="title"
                    required>
            </div>

            <div class="form-group">
                <label>Nội dung</label>

                <textarea
                    name="content"
                    required></textarea>
            </div>

            <div class="form-group">
                <label>Loại thông báo</label>

                <select name="type">
                    <option value="system">Hệ thống</option>
                    <option value="promotion">Khuyến mãi</option>
                    <option value="voucher">Voucher</option>
                    <option value="maintenance">Bảo trì</option>
                </select>
            </div>

            <div class="form-group">
                <label>Trạng thái</label>

                <select name="status">
                    <option value="1">Hiển thị</option>
                    <option value="0">Ẩn</option>
                </select>
            </div>

            <div class="form-group">
                <label>Bắt đầu</label>
                <input
                    type="datetime-local"
                    name="start_date">
            </div>

            <div class="form-group">
                <label>Kết thúc</label>

                <input
                    type="datetime-local"
                    name="end_date">
            </div>

            <div class="form-group">
                <label>
                    <input
                        type="checkbox"
                        name="is_pinned"
                        style="width:auto;margin-right:8px;">

                    Ghim thông báo
                </label>

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
                    name="addNotification"
                    class="save-btn">
                    Thêm
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-pen modal-icon"></i>

            <h2>
                Sửa thông báo
            </h2>
        </div>

        <form method="POST">

            <input
                type="hidden"
                name="notification_id"
                id="edit_id">

            <div class="form-group">
                <label>Tiêu đề</label>

                <input
                    type="text"
                    name="title"
                    id="edit_title"
                    required>
            </div>

            <div class="form-group">
                <label>Nội dung</label>

                <textarea
                    name="content"
                    id="edit_content"
                    required></textarea>
            </div>

            <div class="form-group">
                <label>Loại thông báo</label>
                <select
                    name="type"
                    id="edit_type">

                    <option value="system">Hệ thống</option>
                    <option value="promotion">Khuyến mãi</option>
                    <option value="voucher">Voucher</option>
                    <option value="maintenance">Bảo trì</option>

                </select>
            </div>

            <div class="form-group">
                <label>Trạng thái</label>
                <select
                    name="status"
                    id="edit_status">

                    <option value="1">Hiển thị</option>
                    <option value="0">Ẩn</option>
                </select>
            </div>

            <div class="form-group">
                <label>Bắt đầu</label>
                <input
                    type="datetime-local"
                    name="start_date"
                    id="edit_start">
            </div>

            <div class="form-group">
                <label>Kết thúc</label>

                <input
                    type="datetime-local"
                    name="end_date"
                    id="edit_end">
            </div>

            <div class="form-group">
                <label>
                    <input
                        type="checkbox"
                        name="is_pinned"
                        id="edit_pin"
                        style="width:auto;margin-right:8px;">
                    Ghim thông báo
                </label>
            </div>

            <div class="modal-actions">
                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeEditPopup()">
                    Hủy
                </button>

                <button
                    type="submit"
                    name="updateNotification"
                    class="save-btn">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- VIEW MODAL -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-bell modal-icon"></i>
            <h2>
                Chi tiết thông báo
            </h2>
        </div>

        <div class="form-group">
            <label>Tiêu đề</label>
            <input
                type="text"
                id="view_title"
                readonly>
        </div>

        <div class="form-group">
            <label>Loại</label>
            <input
                type="text"
                id="view_type"
                readonly>
        </div>

        <div class="form-group">
            <label>Nội dung</label>

            <textarea
                id="view_content"
                rows="8"
                readonly></textarea>
        </div>

        <div class="form-group">
            <label>Hiệu lực</label>
            <input
                type="text"
                id="view_time"
                readonly>
        </div>

        <div class="form-group">
            <label>Trạng thái</label>
            <input
                type="text"
                id="view_status"
                readonly>
        </div>

        <div class="form-group">
            <label>Ghim</label>
            <input
                type="text"
                id="view_pin"
                readonly>
        </div>

        <div class="modal-actions">
            <button
                class="save-btn"
                onclick="closeViewPopup()">
                Đóng
            </button>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-circle-xmark modal-icon"></i>

            <h2>
                Xóa thông báo
            </h2>
        </div>

        <form method="POST">
            <input
                type="hidden"
                name="delete_id"
                id="delete_id">

            <p style="text-align:center;">
                Bạn có chắc muốn xóa thông báo này?
            </p>

            <div class="modal-actions">
                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeDeletePopup()">
                    Hủy
                </button>

                <button
                    type="submit"
                    name="deleteNotification"
                    class="save-btn">
                    Xóa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SUCCESS MODAL -->
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

        document
        .getElementById("edit_id")
        .value=data.notification_id;

        document
        .getElementById("edit_title")
        .value=data.title;

        document
        .getElementById("edit_content")
        .value=data.content;

        document
        .getElementById("edit_type")
        .value=data.type;

        document
        .getElementById("edit_status")
        .value=data.status;

        document
        .getElementById("edit_pin")
        .checked=data.is_pinned==1;

        document
        .getElementById("edit_start")
        .value=data.start_date
            ? data.start_date.replace(" ","T")
            : "";

        document
        .getElementById("edit_end")
        .value=data.end_date
            ? data.end_date.replace(" ","T")
            : "";
    }

    function closeEditPopup(){
        document
        .getElementById("editModal")
        .style.display="none";
    }

    function openViewPopup(data){
        document
        .getElementById("viewModal")
        .style.display="flex";

        document
        .getElementById("view_title")
        .value=data.title;

        let type="Hệ thống";

        if(data.type=="promotion"){
            type="Khuyến mãi";
        }

        if(data.type=="voucher"){
            type="Voucher";
        }

        if(data.type=="maintenance"){
            type="Bảo trì";
        }

        document
        .getElementById("view_type")
        .value=type;

        document
        .getElementById("view_content")
        .value=data.content;

        let start=data.start_date ? data.start_date : "Không giới hạn";
        let end=data.end_date ? data.end_date : "Không giới hạn";

        document
        .getElementById("view_time")
        .value=start+"  →  "+end;

        document
        .getElementById("view_status")
        .value=data.status==1 ? "Hiển thị" : "Ẩn";

        document
        .getElementById("view_pin")
        .value=data.is_pinned==1 ? "Có" : "Không";
    }

    function closeViewPopup(){
        document
        .getElementById("viewModal")
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

    window.onclick=function(e){

        if(e.target==document.getElementById("addModal")){
            closeAddModal();
        }

        if(e.target==document.getElementById("editModal")){
            closeEditPopup();
        }

        if(e.target==document.getElementById("viewModal")){
            closeViewPopup();
        }

        if(e.target==document.getElementById("deleteModal")){
            closeDeletePopup();
        }

        if(e.target==document.getElementById("successModal")){
            closeSuccess();
        }
    };

    <?php if (isset($_GET['success'])) { ?>
        window.onload=function(){
            document
            .getElementById("successText")
            .innerHTML=
            "<?php echo $_GET['success']; ?> thông báo thành công";

            document
            .getElementById("successModal")
            .style.display="flex";
        }
    <?php } ?>

</script>

</body>
</html>