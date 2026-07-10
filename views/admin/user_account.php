<?php
session_start();

require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

/*  SEARCH USERS */
$keyword = trim($_GET['keyword'] ?? '');

if ($keyword != '') {
    $stmt = $conn->prepare('
        SELECT *
        FROM users
        WHERE name LIKE ?
           OR email LIKE ?
           OR phone LIKE ?
        ORDER BY user_id DESC
    ');

    $stmt->execute([
        "%$keyword%",
        "%$keyword%",
        "%$keyword%",
    ]);
} else {
    $stmt = $conn->query('
        SELECT *
        FROM users
        ORDER BY user_id DESC
    ');
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* TOGGLE STATUS (LOCK/UNLOCK) */
if (isset($_POST['toggle_status'])) {
    $id = (int) $_POST['user_id'];

    $stmt = $conn->prepare('
        SELECT name, status
        FROM users
        WHERE user_id = ?
    ');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $newStatus = $user['status'] == 1 ? 0 : 1;

        $stmt = $conn->prepare('
            UPDATE users
            SET status = ?
            WHERE user_id = ?
        ');
        $stmt->execute([$newStatus, $id]);

        writeLog(
            $conn,
            'UPDATE',
            'Tài khoản',
            ($newStatus == 1 ? 'Mở khóa' : 'Khóa').
            ' user #'.$id.' - '.$user['name']
        );
    }

    // header('Location: user_account.php?success=1');
    header('Location: user_account.php?success='.($newStatus == 1 ? 'Mở khóa' : 'Khóa'));
    exit;
}

/*  DELETE USER */
if (isset($_POST['delete'])) {
    $id = (int) $_POST['delete'];

    $stmt = $conn->prepare('SELECT name FROM users WHERE user_id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare('DELETE FROM users WHERE user_id = ?');
    $stmt->execute([$id]);

    writeLog(
        $conn,
        'DELETE',
        'Tài khoản',
        'Xóa user #'.$id.' - '.($user['name'] ?? '')
    );

    // header('Location: user_account.php?deleted=1');
    header('Location: user_account.php?success=Xóa');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Quản lý tài khoản người dùng</title>

<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
    margin-bottom:30px;
}

.page-title h1{
    font-size:36px;
    margin-bottom:10px;
}

.page-title p{
    color:#777;
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

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:35px 0 25px;
}

.header h2{
    margin:0;
    font-size:28px;
    font-weight:700;
}

.search-group{
    display:flex;
    align-items:center;
    gap:12px;
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

/* TABLE */
.table-box{
    width:100%;
    background:#fff;
    border-radius:22px;
    padding:25px;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:900px;
}

th{
    padding:18px 12px;
    color:#999;
    font-size:14px;
    text-align:left;
}

td{
    padding:18px 12px;
    border-top:1px solid #f3f3f3;
    vertical-align:middle;
}

/* STATUS */
.status{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.active{
    background:#e7fff1;
    color:#1fa463;
}

.banned{
    background:#ffe5e5;
    color:#d60000;
}

/* ACTION */
.action-btn{
    width:38px;
    height:38px;
    border:none;
    border-radius:10px;
    color:#fff;
    cursor:pointer;
    margin-right:6px;
}

.view-btn{
    background:#23b26d;
}

.lock-btn{
    background:#ffb400;
}

.delete-btn{
    background:#ff4d6d;
}

.action-btn:hover{
    opacity:.9;
}

/*  POPUP  */
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
    width:460px;
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
    color:#ff4d6d;
    margin-bottom:10px;
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
    background:#eee;
    border:none;
    padding:12px 22px;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
}

.save-btn{
    background:#ff4fa3;
    color:#fff;
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

                <a href="promotions.php">
                    <i class="fa-solid fa-tags"></i>
                    Khuyến mãi
                </a>
            
                <a href="user_account.php" class="active">
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
                    <i class="fa-solid fa-layer-group"></i>
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

    <!-- MAIN -->
    <div class="main-content">
        <!-- TOP -->
        <div class="topbar">
            <div class="page-title">
                <h1>Quản lý tài khoản người dùng</h1>
                <p>Quản lý thông tin tài khoản và trạng thái hoạt động của người dùng</p>
            </div>

            <div class="admin-box">
                <img src="https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg?semt=ais_hybrid&w=740&q=80">
                <div>
                    <strong>Admin</strong><br>
                    <small>Quản trị viên</small>
                </div>
            </div>

        </div>

        <!-- HEADER -->
        <div class="header">
            <h2>Danh sách tài khoản</h2>
            <div class="search-group">
                <form method="GET" class="search-box">
                    <input
                        type="text"
                        name="keyword"
                        placeholder="Tìm tài khoản người dùng..."
                        value="<?php echo htmlspecialchars($keyword); ?>">

                    <button type="submit">
                        <i class="fa fa-search"></i>
                    </button>
                </form>

                <?php if ($keyword != '') { ?>
                    <a href="user_account.php" class="reset-btn">
                        Tất cả
                    </a>
                <?php } ?>

            </div>

        </div>

        <!-- TABLE -->
        <div class="table-box">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>

                <?php foreach ($users as $u) { ?>
                <tr>
                    <td>#<?php echo $u['user_id']; ?></td>

                    <td><?php echo htmlspecialchars($u['name']); ?></td>

                    <td><?php echo htmlspecialchars($u['email']); ?></td>

                    <td><?php echo htmlspecialchars($u['phone']); ?></td>

                    <td>
                        <?php if ($u['status'] == 1) { ?>
                            <span style="color:#23b26d;font-weight:bold;">Hoạt động</span>
                        <?php } else { ?>
                            <span style="color:#ff4d6d;font-weight:bold;">Bị khóa</span>
                        <?php } ?>
                    </td>

                    <td>
                        <button
                            class="action-btn view-btn"
                            onclick="location.href='user_detail.php?id=<?php echo $u['user_id']; ?>'">
                            <i class="fa fa-eye"></i>
                        </button>

                        <button
                            type="button"
                            class="action-btn lock-btn"
                            onclick="openLockPopup(
                                <?php echo $u['user_id']; ?>,
                                <?php echo $u['status']; ?>
                            )">
                            <i class="fa-solid <?php echo $u['status'] == 1 ? 'fa-lock' : 'fa-lock-open'; ?>"></i>
                        </button>

                        <button
                            type="button"
                            class="action-btn delete-btn"
                            onclick="openDeletePopup(<?php echo $u['user_id']; ?>)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>

<div id="lockModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-lock modal-icon"></i>
            <h2 id="lockTitle"></h2>
        </div>

        <form method="POST">
            <input
                type="hidden"
                name="user_id"
                id="lock_user_id">

            <p id="lockContent" style="text-align:center;"></p>

            <div class="modal-actions">
                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeLockPopup()">
                    Hủy
                </button>

                <button
                    class="save-btn"
                    name="toggle_status">
                    Xác nhận
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fa-solid fa-circle-xmark modal-icon"></i>
            <h2>Xóa tài khoản</h2>
        </div>

        <form method="POST">
            <input
                type="hidden"
                name="delete"
                id="delete_user_id">
            <p style="text-align:center;">
                Bạn có chắc muốn xóa tài khoản này?
            </p>

            <div class="modal-actions">

                <button
                    type="button"
                    class="cancel-btn"
                    onclick="closeDeletePopup()">
                    Hủy
                </button>

                <button
                    class="save-btn">
                    Xóa
                </button>
            </div>
        </form>
    </div>
</div>

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
    function openLockPopup(id, status){
        document.getElementById("lock_user_id").value = id;
        if(status == 1){
            document.getElementById("lockTitle").innerHTML =
            "Khóa tài khoản";
            document.getElementById("lockContent").innerHTML =
            "Bạn có chắc muốn khóa tài khoản này?";
            document.querySelector("#lockModal .modal-icon")
            .className="fa-solid fa-lock modal-icon";
        }else{
            document.getElementById("lockTitle").innerHTML =
            "Mở khóa tài khoản";
            document.getElementById("lockContent").innerHTML =
            "Bạn có chắc muốn mở khóa tài khoản này?";
            document.querySelector("#lockModal .modal-icon")
            .className="fa-solid fa-lock-open modal-icon success-icon";
        }
        document.getElementById("lockModal").style.display="flex";
    }
    function closeLockPopup(){
        document.getElementById("lockModal").style.display="none";
    }
    function openDeletePopup(id){
        document.getElementById("delete_user_id").value=id;
        document.getElementById("deleteModal").style.display="flex";
    }
    function closeDeletePopup(){
        document.getElementById("deleteModal").style.display="none";
    }
    function closeSuccess(){
        document.getElementById("successModal").style.display="none";
    }
    window.onclick=function(e){
        if(e.target==document.getElementById("lockModal"))
            closeLockPopup();
        if(e.target==document.getElementById("deleteModal"))
            closeDeletePopup();
    }
    <?php if (isset($_GET['success'])) { ?>
        window.onload=function(){
            document.getElementById("successModal").style.display="flex";
            document.getElementById("successText").innerHTML=
            "<?php echo $_GET['success']; ?> tài khoản thành công";
        }
    <?php } ?>
</script>

</body>
</html>