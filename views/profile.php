<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$tab = $_GET['tab'] ?? 'profile';

$orders = [];
if ($tab == 'orders') {
    $stmt = $conn->prepare('
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
    ');

    $stmt->execute([
        $user['user_id'],
    ]);

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$vouchers = [];
if ($tab == 'vouchers') {
    $stmt = $conn->prepare('
        SELECT p.*, uv.is_used
        FROM promotions p
        JOIN user_vouchers uv ON uv.promotion_id = p.promotion_id
        WHERE uv.user_id = ?
        ORDER BY p.created_at DESC
    ');

    $stmt->execute([$user['user_id']]);
    $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function statusText($status)
{
    switch ($status) {
        case 'pending':
            return 'Chờ xác nhận';
        case 'processing':
            return 'Đang xử lý';
        case 'shipping':
            return 'Đang giao';
        case 'delivered':
            return 'Đã giao';
        case 'cancelled':
            return 'Đã huỷ';
        default:
            return 'Không xác định';
    }
}

$promotions = [];
if ($tab == 'vouchers') {
    $stmt = $conn->prepare("
        SELECT p.*
        FROM promotions p
        JOIN user_vouchers uv ON uv.promotion_id = p.promotion_id
        WHERE uv.user_id = ?
        AND p.status = 'active'
        AND p.start_date <= NOW()
        AND p.end_date >= NOW()
    ");
    $stmt->execute([$user['user_id']]);
    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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

.text-pink{
    color:var(--pink);
}

.modal-content{
    box-shadow:0 10px 30px rgba(0,0,0,.15);
}

.form-control{
    border-radius:12px;
}

.form-control:focus{
    border-color:#ff4fa3;
    box-shadow:0 0 0 .2rem rgba(255,79,163,.15);
}

.avatar-wrapper{
    position:relative;
    width:110px;
    margin:auto;
}

.avatar-img{
    width:110px;
    height:110px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #fff;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

.change-avatar-btn{
    position:absolute;
    bottom:0;
    right:0;

    width:36px;
    height:36px;

    border:none;
    border-radius:50%;

    background:#ff4fa3;
    color:white;

    cursor:pointer;
}

.change-avatar-btn:hover{
    background:#e63d8d;
}

.order-card{
    background:#fffafd;
    border-radius:16px;
    padding:20px;
    margin-bottom:15px;
    border:1px solid #ffe3f1;
}

.order-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:10px;
}

.order-info{
    color:#666;
    display:flex;
    justify-content:space-between;
    flex-wrap:wrap;
}

.sidebar{
    background:white;
    position:sticky;
    top:110px;
}

.order-status{
    padding:8px 14px;
    border-radius:30px;
    font-size:13px;
    font-weight:700;
    color:#fff;
}

/* Chờ xác nhận */
.status-pending{
    background:#fff3cd;
    color:#856404;
}

.status-confirmed{
    background:#d1ecf1;
    color:#0c5460;
}

.status-shipping{
    background:#cfe2ff;
    color:#084298;
}

.status-delivered{
    background:#d4edda;
    color:#155724;
}

.status-cancelled{
    background:#f8d7da;
    color:#721c24;
}

.order-link{
    text-decoration: none !important;
    color: inherit;
    display: block;
}

.order-status{
    padding:6px 12px;
    border-radius:20px;
    font-size:13px;
    font-weight:700;
    display:inline-block;
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
                    <div class="avatar-wrapper">
                        <?php if (!empty($user['avatar'])) { ?>
                            <img src="uploads/avatars/<?php echo $user['avatar']; ?>"
                                class="avatar-img">

                        <?php } else { ?>
                            <div class="avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>
                        <?php } ?>

                        <button
                            class="change-avatar-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#avatarModal">
                            <i class="fa-solid fa-camera"></i>
                        </button>
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
                    <a href="?tab=profile"
                    class="list-group-item <?php echo $tab == 'profile' ? 'active-menu' : ''; ?>">
                        <i class="fa-regular fa-user me-2"></i>
                        Thông tin tài khoản
                    </a>

                    <a href="?tab=orders"
                    class="list-group-item <?php echo $tab == 'orders' ? 'active-menu' : ''; ?>">
                        <i class="fa-solid fa-box me-2"></i>
                        Đơn mua
                    </a>

                    <a href="?tab=vouchers"
                    class="list-group-item <?php echo $tab == 'vouchers' ? 'active-menu' : ''; ?>">
                        <i class="fa-solid fa-ticket me-2"></i>
                        Voucher của tôi
                    </a>

                    <a href="?tab=password"
                    class="list-group-item <?php echo $tab == 'password' ? 'active-menu' : ''; ?>">
                        <i class="fa-solid fa-lock me-2"></i>
                        Đổi mật khẩu
                    </a>

                    <a href="logout.php"
                    class="list-group-item text-danger">
                        <i class="fa-solid fa-right-from-bracket me-2"></i>
                        Đăng xuất
                    </a>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="col-lg-9">

            <div class="profile-card content-card p-4 p-lg-5">
                <?php if ($tab == 'profile') { ?>

                    <h3 class="content-title">
                        Thông tin tài khoản
                    </h3>

                    <div class="info-item">
                        <div class="info-label">Họ và tên</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Số điện thoại</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($user['phone'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Địa chỉ</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($user['address'] ?? ''); ?>
                        </div>
                    </div>

                    <button
                        class="btn btn-pink mt-3"
                        data-bs-toggle="modal"
                        data-bs-target="#editProfileModal">
                        <i class="fa-solid fa-pen me-2"></i>
                        Chỉnh sửa thông tin
                    </button>

                <?php } ?>

                <?php if ($tab == 'orders') { ?>

                    <h3 class="content-title">
                        Đơn mua của tôi
                    </h3>

                    <?php if (empty($orders)) { ?>

                        <div class="alert alert-light border">
                            Bạn chưa có đơn hàng nào.
                        </div>

                    <?php } ?>

                    <?php foreach ($orders as $order) { ?>
                        <?php
                        $statusClass = '';
                        switch ($order['status']) {
                            case 'pending':
                                $statusClass = 'status-pending';
                                break;

                            case 'confirmed':
                                $statusClass = 'status-confirmed';
                                break;

                            case 'shipping':
                                $statusClass = 'status-shipping';
                                break;

                            case 'delivered':
                                $statusClass = 'status-delivered';
                                break;

                            case 'cancelled':
                                $statusClass = 'status-cancelled';
                                break;

                            default:
                                $statusClass = 'status-pending';
                        }
                        ?>

                        <a href="order_detail.php?id=<?php echo $order['order_id']; ?>"
                        class="order-link">
                            <div class="order-card">
                                <div class="order-top">
                                    <div>
                                        <b><?php echo $order['order_code']; ?></b>
                                    </div>

                                    <div class="<?php echo $statusClass; ?>">
                                        <?php echo statusText($order['status']); ?>
                                    </div>
                                </div>

                                <div class="order-info">
                                    <div>
                                        Ngày đặt:
                                        <?php echo date(
                                            'd/m/Y H:i',
                                            strtotime($order['created_at'])
                                        ); ?>
                                    </div>

                                    <div>
                                        Số lượng:
                                        <?php echo $order['total_quantity']; ?>
                                    </div>
                                </div>

                                <div class="mt-2 text-end">
                                    <span class="fw-bold text-danger fs-5">
                                        <?php
                                        echo number_format(
                                            $order['total_price'],
                                            0,
                                            ',',
                                            '.'
                                        );
                        ?>
                                        đ
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php } ?>
                <?php } ?>

                <?php if ($tab == 'vouchers') { ?>
                    <h3 class="content-title">
                        Voucher của tôi
                    </h3>

                    <?php if (empty($vouchers)) { ?>
                        <div class="alert alert-light border">
                            Bạn chưa có voucher nào.
                        </div>
                    <?php } ?>

                    <?php foreach ($vouchers as $v) { ?>
                        <div class="order-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <b><?php echo $v['code']; ?></b>
                                    <div class="text-muted">
                                        <?php echo $v['discount_value']; ?>
                                        <?php echo $v['discount_type'] == 'percent' ? '%' : 'đ'; ?>
                                    </div>
                                </div>

                                <div>
                                    <?php if ($v['is_used']) { ?>
                                        <span class="badge bg-secondary">Đã dùng</span>
                                    <?php } else { ?>
                                        <span class="badge bg-success">Chưa dùng</span>
                                    <?php } ?>
                                </div>

                            </div>

                            <div class="mt-2 text-muted" style="font-size:13px;">
                                HSD: <?php echo $v['end_date']; ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>

                <?php if ($tab == 'password') { ?>

                    <h3 class="content-title">
                        Đổi mật khẩu
                    </h3>

                    <form action="../controllers/ChangePasswordController.php"
                        method="POST">

                        <div class="mb-3">
                            <label>Mật khẩu hiện tại</label>
                            <input type="password"
                                name="old_password"
                                class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Mật khẩu mới</label>
                            <input type="password"
                                name="new_password"
                                class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Nhập lại mật khẩu mới</label>
                            <input type="password"
                                name="confirm_password"
                                class="form-control">
                        </div>

                        <button class="btn btn-pink">
                            Cập nhật mật khẩu
                        </button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CHỈNH SỬA THÔNG TIN -->
<div class="modal fade"
     id="editProfileModal"
     tabindex="-1">

    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header">
                <h5 class="modal-title text-pink fw-bold">
                    Chỉnh sửa thông tin tài khoản
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <form action="../controllers/UpdateProfileController.php"
                  method="POST">
                <div class="modal-body">
                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Họ và tên
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="<?php echo htmlspecialchars($user['name']); ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?php echo htmlspecialchars($user['email']); ?>"
                            required>
                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Số điện thoại
                        </label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Địa chỉ
                        </label>

                        <textarea
                            name="address"
                            rows="3"
                            class="form-control"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button
                        type="submit"
                        class="btn btn-pink">
                        <i class="fa-solid fa-floppy-disk me-2"></i>
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div
    class="modal fade"
    id="avatarModal"
    tabindex="-1">

    <div class="modal-dialog">
        <div class="modal-content">
            <form
                action="../controllers/UploadAvatarController.php"
                method="POST"
                enctype="multipart/form-data">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Thay đổi ảnh đại diện
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">
                    <input
                        type="file"
                        name="avatar"
                        class="form-control"
                        accept="image/*"
                        required>
                </div>

                <div class="modal-footer">

                    <button
                        type="submit"
                        class="btn btn-pink">
                        Tải lên
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>