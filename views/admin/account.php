<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

/* KIỂM TRA ĐĂNG NHẬP */
// if (!isset($_SESSION['user'])) {
//     header('Location: ../login.php');
//     exit;
// }

if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['admin']['user_id'];

/* LẤY THÔNG TIN ADMIN */
$stmt = $conn->prepare('
    SELECT *
    FROM users
    WHERE user_id = ?
');
$stmt->execute([$userId]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    exit('Không tìm thấy tài khoản.');
}

/* CẬP NHẬT THÔNG TIN */
if (isset($_POST['updateProfile'])) {
    $avatar = $admin['avatar'];

    /* Upload avatar */
    if (
        isset($_FILES['avatar'])
        && $_FILES['avatar']['error'] == 0
    ) {
        if (!is_dir('../../uploads/avatar')) {
            mkdir('../../uploads/avatar', 0777, true);
        }

        $fileName =
            time().
            '_'.
            basename($_FILES['avatar']['name']);

        $target =
            '../../uploads/avatar/'.
            $fileName;

        if (move_uploaded_file(
            $_FILES['avatar']['tmp_name'],
            $target)
        ) {
            if (!empty($avatar)) {
                $oldFile = '../../'.$avatar;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            $avatar = 'uploads/avatar/'.$fileName;
        }
    }

    $stmt = $conn->prepare('
        UPDATE users
        SET
            name=?,
            email=?,
            phone=?,
            address=?,
            avatar=?
        WHERE user_id=?
    ');

    $stmt->execute([
        $_POST['name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $avatar,
        $userId,
    ]);

    writeLog(
        $conn,
        'UPDATE',
        'Tài khoản',
        'Cập nhật thông tin tài khoản'
    );

    /* cập nhật session */
    $_SESSION['admin']['name'] = $_POST['name'];
    $_SESSION['admin']['email'] = $_POST['email'];

    header('Location: account.php?success=profile');
    exit;
}

/* ĐỔI MẬT KHẨU */
if (isset($_POST['changePassword'])) {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($oldPassword != $admin['password']) {
        header('Location: account.php?error=oldpassword');
        exit;
    }

    if ($newPassword != $confirmPassword) {
        header('Location: account.php?error=confirm');
        exit;
    }

    if (strlen($newPassword) < 6) {
        header('Location: account.php?error=length');
        exit;
    }

    $stmt = $conn->prepare('
        UPDATE users
        SET password=?
        WHERE user_id=?
    ');

    $stmt->execute([
        $newPassword,
        $userId,
    ]);

    writeLog(
        $conn,
        'UPDATE',
        'Tài khoản',
        'Đổi mật khẩu'
    );

    header('Location: account.php?success=password');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Tài khoản quản trị</title>

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

.logout-btn:hover{
    background:#fff0f7;
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
    object-fit:cover;
}

/* CARD */

.card{
    background:#fff;
    border-radius:22px;
    padding:30px;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
    margin-bottom:25px;
}

.card-title{
    font-size:24px;
    font-weight:bold;
    color:#ff4fa3;
    margin-bottom:25px;
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
.form-group textarea{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:12px;
    font-size:14px;
}

.form-group input:focus,
.form-group textarea:focus{
    outline:none;
    border-color:#ff4fa3;
}

.save-btn{
    background:#ff4fa3;
    color:white;
    border:none;
    padding:12px 25px;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
}

.save-btn:hover{
    background:#ff2d91;
}

.avatar-preview{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #ffe1ef;
    margin-bottom:15px;
}

.readonly{
    background:#f8f8f8;
}

/* MODAL */

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
    border-radius:22px;
    padding:30px;
}

.modal-header{
    text-align:center;
    margin-bottom:20px;
}

.modal-icon{
    font-size:60px;
    color:#ff4fa3;
    margin-bottom:15px;
}

.success-icon{
    color:#23b26d;
}

.modal-actions{
    display:flex;
    justify-content:flex-end;
    gap:12px;
    margin-top:25px;
}

.cancel-btn{
    padding:12px 22px;
    border:none;
    border-radius:12px;
    background:#eee;
    cursor:pointer;
    font-weight:bold;
}

.cancel-btn:hover{
    background:#ddd;
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

            <a href="categories.php">
                <i class="fa-regular fa-folder"></i>
                Danh mục
            </a>

            <a href="collections.php">
                <i class="fa-regular fa-images"></i>
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

            <a href="account.php" class="active">
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
            <h1>Tài khoản</h1>
            <p>Quản lý thông tin tài khoản quản trị</p>
        </div>
    </div>

    <!-- THÔNG TIN TÀI KHOẢN -->
    <div class="card">
        <div class="card-title">
            Thông tin tài khoản
        </div>
            <div
                style="
                display:flex;
                gap:40px;
                align-items:flex-start;">

                <!-- AVATAR -->
                <div
                    style="
                    width:220px;
                    text-align:center;">

                    <img
                        class="avatar-preview"
                        src="<?php
                            echo !empty($admin['avatar'])
                            ? '../../'.$admin['avatar']
                            : 'https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg?semt=ais_hybrid&w=740&q=80'; ?>">

                    <div class="form-group">
                        <label>
                            Ảnh đại diện
                        </label>
                    </div>
                </div>

                <!-- THÔNG TIN -->
                <div style="flex:1;">
                    <div class="form-group">
                        <label>
                            Họ và tên
                        </label>

                        <input
                            type="text"
                            value="<?php echo htmlspecialchars($admin['name']); ?>"
                            readonly
                            class="readonly">
                    </div>

                    <div class="form-group">
                        <label>
                            Email
                        </label>

                        <input
                            type="email"
                            value="<?php echo htmlspecialchars($admin['email']); ?>"
                            readonly
                            class="readonly">
                    </div>

                    <div class="form-group">
                        <label>
                            Số điện thoại
                        </label>

                        <input
                            type="text"
                            value="<?php echo htmlspecialchars($admin['phone']); ?>"
                            readonly
                            class="readonly">
                    </div>

                    <div class="form-group">
                        <label>
                            Địa chỉ
                        </label>

                        <textarea
                            rows="3"
                            readonly
                            class="readonly"><?php echo htmlspecialchars($admin['address']); ?></textarea>
                    </div>

                    <div
                        style="
                        display:grid;
                        grid-template-columns:repeat(3,1fr);
                        gap:20px;">

                        <div class="form-group">
                            <label>
                                Vai trò
                            </label>

                            <input
                                class="readonly"
                                readonly
                                value="<?php
                                    echo $admin['role'] == 1
                                    ? 'Quản trị viên'
                                    : 'Khách hàng'; ?>">
                        </div>

                        <div class="form-group">
                            <label>
                                Trạng thái
                            </label>

                            <input
                                class="readonly"
                                readonly
                                value="<?php
                                    echo $admin['status'] == 1
                                    ? 'Hoạt động'
                                    : 'Khóa'; ?>">
                        </div>

                        <div class="form-group">
                            <label>
                                Ngày tạo
                            </label>

                            <input
                                class="readonly"
                                readonly
                                value="<?php echo date('d/m/Y', strtotime($admin['created_at'])); ?>">
                        </div>
                    </div>

                    <div
                        style="
                        display:flex;
                        justify-content:flex-end;
                        gap:15px;
                        margin-top:30px;">
                            <button
                                type="button"
                                class="save-btn"
                                onclick="openPasswordModal()">
                                <i class="fa-solid fa-key"></i>
                                Đổi mật khẩu
                            </button>

                            <button
                                type="button"
                                class="save-btn"
                                onclick="openProfileModal()">
                                <i class="fa-solid fa-pen"></i>
                                Sửa thông tin
                            </button>
                        </div>
                </div>
            </div>
    </div>

    <!-- POPUP SỬA THÔNG TIN -->
    <div id="profileModal" class="modal">
        <div class="modal-content" style="width:650px;">
            <div class="card-title">
                Chỉnh sửa thông tin
            </div>
            <form  id="profileForm" method="POST" enctype="multipart/form-data">
                <input
                    type="hidden"
                    name="updateProfile"
                    value="1">
                <div class="form-group">
                    <label>Ảnh đại diện</label>
                    <img
                        class="avatar-preview"
                        src="<?php
                            echo !empty($admin['avatar'])
                            ? '../../'.$admin['avatar']
                            : 'https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg?semt=ais_hybrid&w=740&q=80'; ?>">
                    <input
                        type="file"
                        name="avatar"
                        accept="image/*">
                </div>

                <div class="form-group">
                    <label>Họ và tên</label>
                    <input
                        type="text"
                        name="name"
                        value="<?php echo htmlspecialchars($admin['name']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($admin['email']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input
                        type="text"
                        name="phone"
                        value="<?php echo htmlspecialchars($admin['phone']); ?>">
                </div>

                <div class="form-group">
                    <label>Địa chỉ</label>
                    <textarea
                        name="address"
                        rows="3"><?php echo htmlspecialchars($admin['address']); ?></textarea>
                </div>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="cancel-btn"
                        onclick="closeProfileModal()">
                        Hủy
                    </button>

                    <button
                        type="button"
                        class="save-btn"
                        onclick="confirmProfile()">
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- POPUP ĐỔI MẬT KHẨU -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="card-title">
                Đổi mật khẩu
            </div>
            <form
                id="passwordForm"
                method="POST">

                <input
                    type="hidden"
                    name="changePassword">
                <div class="form-group">
                    <label>Mật khẩu hiện tại</label>
                    <input
                        type="password"
                        name="old_password"
                        required>
                </div>

                <div class="form-group">
                    <label>Mật khẩu mới</label>
                    <input
                        type="password"
                        name="new_password"
                        required>
                </div>

                <div class="form-group">
                    <label>Nhập lại mật khẩu</label>
                    <input
                        type="password"
                        name="confirm_password"
                        required>
                </div>

                <div class="modal-actions">
                    <button
                        type="button"
                        class="cancel-btn"
                        onclick="closePasswordModal()">
                        Hủy
                    </button>

                    <button
                        type="button"
                        class="save-btn"
                        onclick="confirmPassword()">
                        Đổi mật khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- POPUP XÁC NHẬN -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i
                    class="fa-solid fa-circle-question modal-icon"
                    style="color:#ffb400;">
                </i>
                <h2 id="confirmText"></h2>
            </div>

            <div class="modal-actions">
                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeConfirm()">
                    Hủy
                </button>

                <button
                    type="button"
                    class="save-btn"
                    id="confirmBtn">
                    Xác nhận
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

    <!-- ERROR POPUP -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fa-solid fa-circle-xmark modal-icon"></i>
                <h2 id="errorText"></h2>
            </div>
            <div class="modal-actions">
                <button
                    class="cancel-btn"
                    onclick="closeError()">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <script>
        function closeSuccess(){
            document
            .getElementById("successModal")
            .style.display="none";
        }

        function closeError(){
            document
            .getElementById("errorModal")
            .style.display="none";
        }

        /* Đóng popup khi click ra ngoài */
        window.onclick=function(e){
            if(e.target==document.getElementById("successModal")){
                closeSuccess();
            }
            if(e.target==document.getElementById("errorModal")){
                closeError();
            }
            if(e.target==document.getElementById("profileModal")){
                closeProfileModal();
            }

            if(e.target==document.getElementById("passwordModal")){
                closePasswordModal();
            }

            if(e.target==document.getElementById("confirmModal")){
                closeConfirm();
            }
        }

        /* THÔNG BÁO THÀNH CÔNG */
        <?php if (isset($_GET['success'])) { ?>
            window.onload=function(){
                let text="";

                <?php if ($_GET['success'] == 'profile') { ?>
                    text="Cập nhật thông tin thành công";
                <?php } ?>

                <?php if ($_GET['success'] == 'password') { ?>
                    text="Đổi mật khẩu thành công";
                <?php } ?>

                document
                .getElementById("successText")
                .innerHTML=text;

                document
                .getElementById("successModal")
                .style.display="flex";
            }
        <?php } ?>

        /* THÔNG BÁO LỖI */
        <?php if (isset($_GET['error'])) { ?>
            window.onload=function(){
                let text="";

                <?php if ($_GET['error'] == 'oldpassword') { ?>
                    text="Mật khẩu hiện tại không đúng";
                <?php } ?>

                <?php if ($_GET['error'] == 'confirm') { ?>
                    text="Mật khẩu xác nhận không khớp";
                <?php } ?>

                <?php if ($_GET['error'] == 'length') { ?>
                    text="Mật khẩu mới phải có ít nhất 6 ký tự";
                <?php } ?>

                document
                .getElementById("errorText")
                .innerHTML=text;

                document
                .getElementById("errorModal")
                .style.display="flex";
            }
        <?php } ?>

        function openProfileModal(){
            document
                .getElementById("profileModal")
                .style.display = "flex";
        }

        function closeProfileModal(){
            document
                .getElementById("profileModal")
                .style.display = "none";
        }

        function openPasswordModal(){
            document
                .getElementById("passwordModal")
                .style.display = "flex";
        }

        function closePasswordModal(){
            document
                .getElementById("passwordModal")
                .style.display = "none";
        }

        function closeConfirm(){
            document
                .getElementById("confirmModal")
                .style.display = "none";
        }

        function confirmProfile(){
            document
                .getElementById("confirmText")
                .innerHTML = "Bạn có chắc muốn cập nhật thông tin?";
            document
                .getElementById("confirmModal")
                .style.display = "flex";
            
            const btn = document.getElementById("confirmBtn");
            btn.onclick = null;
            btn.onclick=function(){
            closeConfirm();
            document
               .getElementById("profileForm")
                .submit();
            };
        }

        function confirmPassword(){
            const newPass =
                document.querySelector('[name="new_password"]').value;

            const confirm =
                document.querySelector('[name="confirm_password"]').value;

            if(newPass !== confirm){
                document
                    .getElementById("errorText")
                    .innerHTML = "Mật khẩu xác nhận không khớp";
                document
                    .getElementById("errorModal")
                    .style.display = "flex";
                return;
            }
            document
                .getElementById("confirmText")
                .innerHTML = "Bạn có chắc muốn đổi mật khẩu?";

            document
                .getElementById("confirmModal")
                .style.display = "flex";
            
            const btn = document.getElementById("confirmBtn");
            btn.onclick = null;
            btn.onclick=function(){
                closeConfirm();
                document
                    .getElementById("passwordForm")
                    .submit();
            };
        }
        
        document.querySelector("#profileForm input[name='avatar']").addEventListener("change", function(){
            if(this.files.length){
                const reader = new FileReader();
                reader.onload = function(e){
                    document.querySelector("#profileModal .avatar-preview").src =
                        e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

    </script>

</div>
</div>

</body>
</html>