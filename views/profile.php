<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Tài khoản của tôi</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

:root{
    --pink:#ff4fa3;
    --pink-light:#ff85c1;
    --bg:#fff7fb;
}

/* ✅ FIX FONT ĐỒNG BỘ */
body{
    background:var(--bg);
    font-family:'Quicksand', sans-serif;
    font-weight:600;
}

/* TITLE */
.account-title{
    color:var(--pink);
    font-size:32px;
    font-weight:700;
    text-align:center;
    margin-bottom:30px;
}

/* CARD */
.profile-card{
    border:none;
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 6px 20px rgba(0,0,0,0.08);
}

/* SIDEBAR */
.sidebar{
    background:white;
}

.avatar{
    width:95px;
    height:95px;
    border-radius:50%;
    background:linear-gradient(135deg,var(--pink),var(--pink-light));
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-size:38px;
    margin:auto;
}

.user-name{
    font-size:20px;
    font-weight:700;
    color:#333;
}

.user-role{
    color:#888;
    font-size:14px;
    font-weight:600;
}

/* MENU */
.profile-menu .list-group-item{
    border:none;
    padding:14px 18px;
    border-radius:12px;
    margin-bottom:8px;
    transition:.25s;
    font-weight:600;
    font-size:14px;
}

.profile-menu .list-group-item:hover{
    background:#fff0f7;
    color:var(--pink);
}

.profile-menu .active-menu{
    background:#fff0f7;
    color:var(--pink);
    font-weight:700;
}

/* CONTENT */
.content-card{
    background:white;
}

.content-title{
    color:var(--pink);
    font-weight:700;
    margin-bottom:25px;
    font-size:22px;
}

.info-item{
    padding:16px;
    background:#fffafd;
    border-radius:14px;
    margin-bottom:15px;
}

.info-label{
    font-size:13px;
    color:#888;
    margin-bottom:5px;
}

.info-value{
    font-size:16px;
    font-weight:700;
    color:#333;
}

/* BUTTON */
.btn-pink{
    background:var(--pink);
    border:none;
    color:white;
    border-radius:12px;
    padding:10px 20px;
    font-weight:700;
}

.btn-pink:hover{
    background:#e63d8d;
    color:white;
}

.btn-outline-pink{
    border:2px solid var(--pink);
    color:var(--pink);
    border-radius:12px;
    font-weight:700;
}

.btn-outline-pink:hover{
    background:var(--pink);
    color:white;
}

</style>
</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container mt-4 mb-4">
    <h2 class="account-title">Tài khoản của tôi</h2>
</div>

<div class="container mb-5">

    <div class="row g-4">

        <!-- SIDEBAR -->
        <div class="col-lg-3">

            <div class="profile-card sidebar p-4">

                <div class="text-center">

                    <div class="avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div class="user-name mt-3">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </div>

                    <div class="user-role">
                        <?php echo ($user['role'] ?? 0) == 1 ? 'Quản trị viên' : 'Khách hàng'; ?>
                    </div>

                </div>

                <hr>

                <div class="profile-menu list-group">

                    <a href="#" class="list-group-item active-menu">
                        <i class="fa-regular fa-user me-2"></i>
                        Thông tin tài khoản
                    </a>

                    <a href="#" class="list-group-item">
                        <i class="fa-solid fa-box me-2"></i>
                        Đơn mua
                    </a>

                    <a href="#" class="list-group-item">
                        <i class="fa-solid fa-ticket me-2"></i>
                        Voucher của tôi
                    </a>

                    <a href="#" class="list-group-item">
                        <i class="fa-solid fa-lock me-2"></i>
                        Đổi mật khẩu
                    </a>

                    <a href="logout.php" class="list-group-item text-danger">
                        <i class="fa-solid fa-right-from-bracket me-2"></i>
                        Đăng xuất
                    </a>

                </div>

            </div>

        </div>

        <!-- CONTENT -->
        <div class="col-lg-9">

            <div class="profile-card content-card p-4 p-lg-5">

                <h3 class="content-title">Thông tin tài khoản</h3>

                <div class="info-item">
                    <div class="info-label">Họ và tên</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>

                <div class="info-item">
                    <div class="info-label">Vai trò</div>
                    <div class="info-value">
                        <?php echo ($user['role'] ?? 0) == 1 ? 'Quản trị viên' : 'Khách hàng'; ?>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">

                    <button class="btn btn-pink">
                        <i class="fa-solid fa-pen me-2"></i>
                        Chỉnh sửa thông tin
                    </button>
                    
                </div>

            </div>

        </div>

    </div>

</div>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>