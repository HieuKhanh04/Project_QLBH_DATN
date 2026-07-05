<?php
require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

/* XÁC NHẬN ĐƠN HÀNG */
if (isset($_GET['confirm'])) {
    $id = (int) $_GET['confirm'];
    // Lấy thông tin đơn hàng trước khi cập nhật
    $stmt = $conn->prepare('
        SELECT order_code
        FROM orders
        WHERE order_id = ?
    ');
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    // Cập nhật trạng thái
    $stmt = $conn->prepare("
        UPDATE orders
        SET status = 'confirmed'
        WHERE order_id = ?
    ");
    $stmt->execute([$id]);
    // Ghi log
    writeLog(
        $conn,
        'UPDATE',
        'Đơn hàng',
        'Xác nhận đơn hàng #'.$id.' - '.($order['order_code'] ?? '')
    );
    header('Location: orders.php?confirm_success=1');
    exit;
}

/* HỦY ĐƠN HÀNG */
if (isset($_GET['cancel'])) {
    $id = (int) $_GET['cancel'];
    // Lấy thông tin đơn hàng trước khi cập nhật
    $stmt = $conn->prepare('
        SELECT order_code
        FROM orders
        WHERE order_id = ?
    ');
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    // Cập nhật trạng thái
    $stmt = $conn->prepare("
        UPDATE orders
        SET
            status = 'cancelled',
            cancelled_by = 'Admin'
        WHERE order_id = ?
    ");
    $stmt->execute([$id]);
    // Ghi log
    writeLog(
        $conn,
        'UPDATE',
        'Đơn hàng',
        'Hủy đơn hàng #'.$id.' - '.($order['order_code'] ?? '')
    );
    header('Location: orders.php?cancel_success=1');
    exit;
}

/* LẤY ĐƠN HÀNG */
$stmt = $conn->query('
    SELECT *
    FROM orders
    ORDER BY order_id DESC
');
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Quản lý đơn hàng</title>

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

/* ================= SIDEBAR (GIỮ NGUYÊN 100% PRODUCTS) ================= */
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
}

/* TABLE */
.table-box{
    background:white;
    border-radius:22px;
    padding:25px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

.chart-title{
    font-size:24px;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

th:nth-child(1){width:90px;}
th:nth-child(2){width:180px;}
th:nth-child(3){width:150px;}
th:nth-child(4){width:220px;}
th:nth-child(5){width:160px;}
th:nth-child(6){width:150px;}
th:nth-child(7){width:230px;}

table th{
    text-align:left;
    padding-bottom:15px;
    color:#999;
}

table td{
    padding:18px 0;
    border-top:1px solid #f3f3f3;
}

/* ===== STATUS ===== */

.status{
    display:inline-block;
    min-width:120px;
    text-align:center;
    padding:8px 14px;
    border-radius:30px;
    font-size:13px;
    font-weight:bold;
    line-height:1.4;
}

/* Chờ xác nhận */
.pending{
    background:#fff4d6;
    color:#d48806;
}

/* Đã xác nhận */
.confirmed{
    background:#e6f4ff;
    color:#1677ff;
}

/* Đang giao */
.shipping{
    background:#f3e8ff;
    color:#7b2cbf;
}

/* Đã giao */
.delivered{
    background:#e8f8ef;
    color:#16a34a;
}

/* Đã hủy */
.cancelled{
    background:#ffe5e5;
    color:#dc2626;
}

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
    width:430px;
    background:white;
    border-radius:24px;
    padding:30px;
}

.modal-header{
    text-align:center;
}

.modal-header h2{
    margin-top:10px;
}

.warning-icon{
    font-size:55px;
    color:#ffb400;
}

.modal-actions{
    display:flex;
    justify-content:flex-end;
    gap:12px;
    margin-top:25px;
}

.cancel-btn{
    background:#eee;
    color:#333;
    border:none;
    padding:12px 22px;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
}

.confirm-btn{
    background:#28a745;
    color:white;
    border:none;
    padding:8px 12px;
    font-size:13px;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
}

.confirm-btn:hover{
    background:#208637;
}

.cancel-order-btn{
    background:#dc3545;
    color:#fff;
    border:none;
    padding:8px 12px;
    font-size:13px;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
}

.cancel-order-btn:hover{
    background:#bb2d3b;
}

</style>

</head>

<body>

<div class="admin-container">

<!-- SIDEBAR (GIỮ NGUYÊN 100% TỪ PRODUCTS.PHP) -->
<div class="sidebar">
    <a href="admin_dashboard.php" class="logo">
        HAN STORE
    </a>

    <div class="sidebar-content">
        <div class="menu">

            <div class="menu-title">MENU CHÍNH</div>

            <a href="admin_dashboard.php">
                <i class="fa-solid fa-chart-line"></i>
                Dashboard
            </a>

            <a href="products.php">
                <i class="fa-solid fa-shirt"></i>
                Sản phẩm
            </a>

            <a href="orders.php" class="active">
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

            <div class="menu-title">QUẢN LÝ NỘI DUNG</div>

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

            <div class="menu-title">HỆ THỐNG</div>

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

<!-- CONTENT (GIỮ NGUYÊN) -->
<div class="main-content">

    <div class="topbar">

        <div class="page-title">
            <h1>Quản lý đơn hàng</h1>
            <p>Theo dõi toàn bộ đơn hàng cửa hàng</p>
        </div>

        <!-- ADMIN ACCOUNT -->
        <div class="admin-box">
            <img src="https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg?semt=ais_hybrid&w=740&q=80">
            <div>
                <strong>Admin</strong><br>
                <small>Quản trị viên</small>
            </div>
        </div>
    </div>

    <div class="table-box">

        <div class="chart-title">Đơn hàng gần đây</div>

        <table>

            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>SĐT</th>
                <th>Ngày</th>
                <th>Trạng thái</th>
                <th>Tổng tiền</th>
                <th>Thao tác</th>
            </tr>

            <?php foreach ($orders as $o) { ?>

            <tr>

                <td>#<?php echo $o['order_id']; ?></td>
                <td><?php echo $o['receiver_name']; ?></td>
                <td><?php echo $o['receiver_phone']; ?></td>
                <td><?php echo $o['created_at']; ?></td>

                <td>
                    <?php
                        $status = $o['status'];
                $class = match ($status) {
                    'pending' => 'pending',
                    'confirmed' => 'confirmed',
                    'shipping' => 'shipping',
                    'delivered' => 'delivered',
                    'cancelled' => 'cancelled',
                    default => 'pending'
                }; ?>

                    <span class="status <?php echo $class; ?>">
                        <?php
                switch ($status) {
                    case 'pending':
                        echo 'Chờ xác nhận';
                        break;

                    case 'confirmed':
                        echo 'Đã xác nhận';
                        break;

                    case 'shipping':
                        echo 'Đang giao';
                        break;

                    case 'delivered':
                        echo 'Đã giao';
                        break;

                    case 'cancelled':
                        echo 'Đã hủy';

                        if (!empty($o['cancelled_by'])) {
                            echo "<br><small style='display:block;margin-top:4px;font-size:11px;color:#666'>
                            Hủy bởi: {$o['cancelled_by']}
                            </small>";
                        }

                        break;
                    default:
                        echo $status;
                }
                ?>
                    </span>
                </td>

                <td>
                    <?php echo number_format($o['total_price']); ?> đ
                </td>

                <td>
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        <a href="order_detail.php?id=<?php echo $o['order_id']; ?>">
                            <button
                                style="
                                    background:#0d6efd;
                                    color:#fff;
                                    border:none;
                                    padding:10px 18px;
                                    border-radius:10px;
                                    cursor:pointer;
                                    font-weight:bold;
                                ">
                                Xem
                            </button>
                        </a>

                        <?php if ($o['status'] == 'pending') { ?>
                            <button
                                class="confirm-btn"
                                onclick="openConfirmModal(<?php echo $o['order_id']; ?>)">
                                Xác nhận
                            </button>

                            <button
                                class="cancel-order-btn"
                                onclick="openCancelModal(<?php echo $o['order_id']; ?>)">
                                Hủy
                            </button>
                        <?php } ?>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </table>
    </div>
</div>

<div id="confirmModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="warning-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <h2>Xác nhận đơn hàng</h2>

            <p style="margin-top:15px;color:#666;">
                Bạn có chắc muốn xác nhận đơn hàng này?
            </p>

        </div>

        <div class="modal-actions">

            <button
                class="cancel-btn"
                onclick="closeConfirmModal()">
                Hủy
            </button>

            <a id="confirmLink" href="">
                <button class="confirm-btn">
                    Xác nhận
                </button>
            </a>
        </div>
    </div>
</div>

<div id="cancelModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="warning-icon" style="color:#dc3545">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>

            <h2>Hủy đơn hàng</h2>

            <p style="margin-top:15px;color:#666">
                Bạn có chắc muốn hủy đơn hàng này?
            </p>

        </div>

        <div class="modal-actions">
            <button
                class="cancel-btn"
                onclick="closeCancelModal()">
                Đóng
            </button>

            <a id="cancelLink" href="">
                <button class="cancel-order-btn">
                    Hủy đơn
                </button>
            </a>
        </div>
    </div>
</div>

<div id="successModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="warning-icon" style="color:#28a745">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <h2>Thành công</h2>

            <p id="successMessage" style="margin-top:15px;color:#666;"></p>

        </div>

        <div class="modal-actions">
            <button
                class="confirm-btn"
                style="background:#28a745"
                onclick="closeSuccessModal()">
                OK
            </button>
        </div>

    </div>
</div>

<script>
    function openConfirmModal(orderId){
        document.getElementById("confirmLink").href =
            "?confirm=" + orderId;

        document.getElementById("confirmModal").style.display = "flex";
    }

    function closeConfirmModal(){
        document.getElementById("confirmModal").style.display = "none";
    }

    function openCancelModal(orderId){
        document.getElementById("cancelLink").href =
            "?cancel=" + orderId;

        document.getElementById("cancelModal").style.display = "flex";
    }

    function closeCancelModal(){
        document.getElementById("cancelModal").style.display = "none";
    }

    function closeSuccessModal(){
        document.getElementById("successModal").style.display = "none";
        history.replaceState({}, "", "orders.php");
    }

    window.onclick = function(e){

        if(e.target == document.getElementById("confirmModal")){
            closeConfirmModal();
        }

        if(e.target == document.getElementById("cancelModal")){
            closeCancelModal();
        }

        if(e.target == document.getElementById("successModal")){
            closeSuccessModal();
        }
    };

    window.onload = function(){
        <?php if (isset($_GET['confirm_success'])) { ?>

            document.getElementById("successMessage").textContent =
                "Đơn hàng đã được xác nhận thành công.";

            document.getElementById("successModal").style.display = "flex";

        <?php } ?>

        <?php if (isset($_GET['cancel_success'])) { ?>

            document.getElementById("successMessage").textContent =
                "Đơn hàng đã được hủy thành công.";

            document.getElementById("successModal").style.display = "flex";

        <?php } ?>
    };

</script>

</body>
</html>