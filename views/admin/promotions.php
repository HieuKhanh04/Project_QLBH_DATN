<?php
require_once '../../config/database.php';

/* THÊM */

if (isset($_POST['addPromotion'])) {
    $stmt = $conn->prepare('
        INSERT INTO promotions(
            code,
            discount_type,
            discount_value,
            quantity,
            used_count,
            start_date,
            end_date,
            status)
        VALUES(?,?,?,?,?,?,?,?)
    ');

    $status = 'active';

    $stmt->execute([
        $_POST['code'],
        $_POST['discount_type'],
        $_POST['discount_value'],
        $_POST['quantity'],
        0,
        $_POST['start_date'],
        $_POST['end_date'],
        $status,
    ]);

    header(
        'Location: promotions.php?success=Thêm'
    );

    exit;
}

/* SỬA */

if (isset($_POST['updatePromotion'])) {
    $stmt = $conn->prepare('
        UPDATE promotions SET
            code=?,
            discount_type=?,
            discount_value=?,
            quantity=?,
            start_date=?,
            end_date=?
        WHERE promotion_id=?
    ');

    $stmt->execute([
        $_POST['code'],
        $_POST['discount_type'],
        $_POST['discount_value'],
        $_POST['quantity'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['promotion_id'],
    ]);
    header('Location: promotions.php?success=Sửa');
    exit;
}

/* XÓA */
if (isset($_POST['deletePromotion'])) {
    $stmt = $conn->prepare('
        DELETE FROM promotions
        WHERE promotion_id=?
    ');

    $stmt->execute([
        $_POST['delete_id'],
    ]);

    header('Location: promotions.php?success=Xóa');
    exit;
}

/* LẤY DỮ LIỆU */
$stmt = $conn->query('
    SELECT *
    FROM promotions
    ORDER BY promotion_id DESC
');
$promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Quản lý khuyến mãi</title>

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

/* SIDEBAR  */
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
    margin-bottom:30px;
}

.page-title h1{
    font-size:36px;
    margin-bottom:10px;
}

.page-title p{
    color:#777;
}

/* TABLE */
.table-box{
    background:white;
    border-radius:22px;
    padding:25px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
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

/* BADGE */
.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:13px;
    font-weight:bold;
}

.active{
    background:#e4fff0;
    color:#23b26d;
}

.expired{
    background:#ffe4e4;
    color:#ff4d4d;
}

/* BUTTON */
.add-btn{
    background:#ff4fa3;
    color:white;
    border:none;
    padding:12px 18px;
    border-radius:12px;
    cursor:pointer;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
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

            <a href="promotions.php" class="active">
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
            <h1>Quản lý khuyến mãi</h1>
            <p>Quản lý mã giảm giá hệ thống</p>
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

    <div class="header">
        <h2>Danh sách khuyến mãi</h2>
        <button
            class="add-btn"
            onclick="openAddModal()">
            + Thêm mã
        </button>
    </div>

    <div class="table-box">

        <table>

            <tr>
                <th>Mã</th>
                <th>Loại</th>
                <th>Giá trị</th>
                <th>Số lượng</th>
                <th>Đã dùng</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>

            <?php foreach ($promotions as $p) { ?>

            <tr>

                <td><strong><?php echo $p['code']; ?></strong></td>

                <td>
                    <?php echo $p['discount_type']; ?>
                </td>

                <td>
                    <?php echo $p['discount_value']; ?>
                </td>

                <td>
                    <?php echo $p['quantity']; ?>
                </td>

                <td>
                    <?php echo $p['used_count']; ?>
                </td>

                <td>
                    <?php echo $p['start_date']; ?> → <?php echo $p['end_date']; ?>
                </td>

                <td>
                    <span class="badge <?php echo $p['status']; ?>">
                        <?php echo $p['status']; ?>
                    </span>
                </td>

                 <td>
                    <div style="display:flex; gap:10px;">
                        <!-- SỬA -->
                        <button
                            onclick='openEditPopup(
                            <?php echo json_encode($p); ?>
                            )'
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

                        <!-- XÓA -->
                        <button
                            onclick="openDeletePopup(
                            <?php echo $p['promotion_id']; ?>)"
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
                    </div>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
</div>

<!-- ADD PROMOTION MODAL -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-tags modal-icon"></i>
            <h2>Thêm mã khuyến mãi</h2>
        </div>

        <form action="promotions.php" method="POST">

            <div class="form-group">
                <label>Mã khuyến mãi</label>
                <input
                    type="text"
                    name="code"
                    required>
            </div>

            <div class="form-group">
                <label>Loại giảm</label>
                <select name="discount_type">

                    <option value="percent">Phần trăm (%)</option>

                    <option value="fixed">Số tiền (VNĐ)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Giá trị</label>
                <input
                    type="number"
                    name="discount_value"
                    required>
            </div>

            <div class="form-group">
                <label>Số lượng</label>
                <input
                    type="number"
                    name="quantity"
                    required>
            </div>

            <div class="form-group">
                <label>Ngày bắt đầu</label>
                <input
                    type="date"
                    name="start_date"
                    required>
            </div>

            <div class="form-group">
                <label>Ngày kết thúc</label>

                <input
                    type="date"
                    name="end_date"
                    required>
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
                    name="addPromotion"
                    class="save-btn">
                    Thêm
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT POPUP -->
<div id="editPromotionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-pen-to-square modal-icon"></i>
            <h2>
                Sửa mã khuyến mãi
            </h2>
        </div>

        <form method="POST">
            <input type="hidden" name="promotion_id" id="edit_id">
            <div class="form-group">
                <label>
                    Mã khuyến mãi
                </label>
                <input type="text" name="code" id="edit_code" required>
            </div>

            <div class="form-group">
                <label>
                    Loại giảm
                </label>

                <select name="discount_type" id="edit_type"> 
                    <option value="percent">
                        Phần trăm (%)
                    </option>

                    <option value="fixed">
                        Số tiền (VNĐ)
                    </option>
                </select>
            </div>
        
            <div class="form-group">
                <label>
                    Giá trị giảm
                </label>

                <input type="number" name="discount_value" id="edit_value">
            </div>

            <div class="form-group">
                <label>
                    Số lượng
                </label>

                <input type="number" name="quantity" id="edit_quantity">
            </div>

            <div class="form-group">
                <label>
                    Thời gian áp dụng
                </label>

                <div style=" display:flex; gap:12px;">
                    <input type="date" name="start_date" id="edit_start">
                    <input type="date" name="end_date" id="edit_end">
                </div>
            </div>

            <div class="modal-actions">
                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeEditPopup()">
                    Hủy
                </button>

                <button
                    name="updatePromotion"
                    class="save-btn">
                    Lưu thay đổi
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
            <h2>Xóa khuyến mãi</h2>
        </div>

        <form method="POST">
            <input type="hidden" name="delete_id" id="delete_id">
            <p style="text-align:center">
                Bạn có chắc muốn xóa mã này?
            </p>
    
            <div class="modal-actions">
                <button type="button" class="cancel-btn" onclick="closeDeletePopup()">
                    Hủy
                </button>

                <button name="deletePromotion" class="save-btn">
                    Xóa
                </button>
            </div>
        </form>
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
        document.getElementById("addModal").style.display="flex";
    }

    function closeAddModal(){
        document.getElementById("addModal").style.display="none";
    }

    window.onclick=function(e){
        if(e.target==document.getElementById("addModal")){
            closeAddModal();
        }
    }

    function openAddModal(){
    document.getElementById("addModal").style.display="flex";
}

    function closeAddModal(){
        document.getElementById("addModal").style.display="none";
    }

    function openEditPopup(data){
        document.getElementById("editPromotionModal").style.display="flex";

        document.getElementById("edit_id").value=data.promotion_id;
        document.getElementById("edit_code").value=data.code;
        document.getElementById("edit_type").value=data.discount_type;
        document.getElementById("edit_value").value=data.discount_value;
        document.getElementById("edit_quantity").value=data.quantity;
        document.getElementById("edit_start").value=data.start_date;
        document.getElementById("edit_end").value=data.end_date;
    }

    function closeEditPopup(){
        document.getElementById("editPromotionModal").style.display="none";
    }

    function openDeletePopup(id){
        document.getElementById("deleteModal").style.display="flex";
        document.getElementById("delete_id").value=id;
    }

    function closeDeletePopup(){
        document.getElementById("deleteModal").style.display="none";
    }

    function closeSuccess(){
        document.getElementById("successModal").style.display="none";
    }

    <?php if (isset($_GET['success'])) { ?>

    window.onload=function(){
        document.getElementById("successText").innerHTML =
        "<?php echo $_GET['success']; ?> mã khuyến mãi thành công";
        document.getElementById("successModal").style.display="flex";
    }
    <?php } ?>
</script>
</body>
</html>