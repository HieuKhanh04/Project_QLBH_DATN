<?php
session_start();
require_once '../../config/database.php';

/* CHECK ADMIN */
if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] != 1) {
    header('Location: ../login.php');
    exit;
}

/* PARAMS */
$keyword = trim($_GET['keyword'] ?? '');
$action = trim($_GET['action'] ?? '');
$page = (int) ($_GET['page'] ?? 1);
if ($page < 1) {
    $page = 1;
}

$limit = 10;

/*  STATISTICS */
$totalLogs = $conn->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn();

$todayLogs = $conn->query('
    SELECT COUNT(*) FROM activity_logs
    WHERE DATE(created_at) = CURDATE()
')->fetchColumn();

$loginLogs = $conn->query("
    SELECT COUNT(*) FROM activity_logs
    WHERE action = 'LOGIN'
")->fetchColumn();

$updateLogs = $conn->query("
    SELECT COUNT(*) FROM activity_logs
    WHERE action = 'UPDATE'
")->fetchColumn();

/*  COUNT FILTER */
$sqlCount = 'SELECT COUNT(*) FROM activity_logs WHERE 1=1';
$params = [];

if ($keyword != '') {
    $sqlCount .= ' AND (action LIKE ? OR module LIKE ? OR description LIKE ?)';
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

if ($action != '') {
    $sqlCount .= ' AND action = ?';
    $params[] = $action;
}

$stmt = $conn->prepare($sqlCount);
$stmt->execute($params);
$totalRows = $stmt->fetchColumn();

$totalPages = max(1, ceil($totalRows / $limit));
if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $limit;

/*  GET DATA */
$sql = '
SELECT l.*, u.name, u.email, u.avatar
FROM activity_logs l
LEFT JOIN users u ON l.user_id = u.user_id
WHERE 1=1
';

$params = [];

if ($keyword != '') {
    $sql .= ' AND (l.action LIKE ? OR l.module LIKE ? OR l.description LIKE ?)';
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

if ($action != '') {
    $sql .= ' AND l.action = ?';
    $params[] = $action;
}

$sql .= " ORDER BY l.created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Nhật ký hoạt động</title>

<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* == RESET == */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#fff5f9;
}

/* == LAYOUT == */
.admin-container{
    display:flex;
}

/* == SIDEBAR (GIỮ NGUYÊN CỦA BẠN) == */
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

/* == MAIN == */
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
    margin-bottom:10px;
}

.page-title p{
    color:#777;
}

/* ADMIN */
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

/* STAT */
.stat-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-bottom:30px;
}

.stat-card{
    background:#fff;
    border-radius:22px;
    padding:25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
}

.stat-card i{
    font-size:38px;
    color:#ff4fa3;
}

/* FILTER */
.filter-bar{
    background:#fff;
    border-radius:22px;
    padding:20px;
    display:flex;
    gap:15px;
    align-items:center;
    margin-bottom:25px;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
}

.filter-bar input,
.filter-bar select{
    height:46px;
    border:1px solid #eee;
    border-radius:12px;
    padding:0 15px;
    outline:none;
}

.filter-btn{
    height:46px;
    padding:0 24px;
    border:none;
    border-radius:12px;
    background:#ff4fa3;
    color:#fff;
    cursor:pointer;
}

.filter-bar{
    align-items:center;
}

.filter-bar form{
    align-items:center;
}

.filter-bar a{
    display:flex;
    align-items:center;
    justify-content:center;
}

/* TABLE */
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

table th{
    text-align:left;
    color:#999;
    padding-bottom:18px;
}

table td{
    padding:18px 0;
    border-top:1px solid #f4f4f4;
}

/* BADGE */
.badge{
    padding:8px 14px;
    border-radius:30px;
    font-size:13px;
    font-weight:bold;
}

.create{background:#e7fff1;color:#1fa463;}
.update{background:#fff7df;color:#d49300;}
.delete{background:#ffe7eb;color:#ff4566;}
.login{background:#e8f2ff;color:#2f80ed;}
.logout{background:#f3ebff;color:#7b61ff;}

/* PAGINATION */
.pagination{
    display:flex;
    justify-content:center;
    gap:10px;
    margin-top:30px;
}

.pagination a{
    width:42px;
    height:42px;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#fff;
    border:1px solid #eee;
    border-radius:12px;
    text-decoration:none;
}

.pagination a.active{
    background:#ff4fa3;
    color:#fff;
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
                <a href="categories.php" class="sidebar-item">
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

                <a href="#" class="sidebar-item">
                    <i class="fa-solid fa-gear"></i>
                    Cài đặt
                </a>

                <a href="account.php" class="sidebar-item">
                    <i class="fa-regular fa-user"></i>
                    Tài khoản
                </a>

                <a href="activity_logs.php" class="active">
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

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="page-title">
            <h1>Nhật ký hoạt động</h1>
            <p>Theo dõi toàn bộ lịch sử thao tác của quản trị viên</p>
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

    <!-- STATISTICS -->
    <div class="stat-grid">

        <div class="stat-card">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <div>
                <h2><?php echo $totalLogs; ?></h2>
                <p>Tổng hoạt động</p>
            </div>
        </div>

        <div class="stat-card">
            <i class="fa-solid fa-calendar-day"></i>
            <div>
                <h2><?php echo $todayLogs; ?></h2>
                <p>Hôm nay</p>
            </div>
        </div>

        <div class="stat-card">
            <i class="fa-solid fa-right-to-bracket"></i>
            <div>
                <h2><?php echo $loginLogs; ?></h2>
                <p>Đăng nhập</p>
            </div>
        </div>

        <div class="stat-card">
            <i class="fa-solid fa-pen-to-square"></i>
            <div>
                <h2><?php echo $updateLogs; ?></h2>
                <p>Cập nhật</p>
            </div>
        </div>

    </div>

    <!-- FILTER -->
    <div class="filter-bar">
        <form method="GET" id="filterForm" style="display:flex; gap:12px; flex:1; align-items:center;">
            <input type="text"
                name="keyword"
                placeholder="Tìm theo thao tác, module, mô tả..."
                value="<?php echo htmlspecialchars($keyword); ?>">

            <select name="action" id="actionFilter">
                <option value="">Tất cả</option>
                <option value="LOGIN" <?php echo $action == 'LOGIN' ? 'selected' : ''; ?>>LOGIN</option>
                <option value="CREATE" <?php echo $action == 'CREATE' ? 'selected' : ''; ?>>CREATE</option>
                <option value="UPDATE" <?php echo $action == 'UPDATE' ? 'selected' : ''; ?>>UPDATE</option>
                <option value="DELETE" <?php echo $action == 'DELETE' ? 'selected' : ''; ?>>DELETE</option>
                <option value="LOGOUT" <?php echo $action == 'LOGOUT' ? 'selected' : ''; ?>>LOGOUT</option>
            </select>
        </form>

        <a href="activity_logs.php" class="filter-btn" style="text-decoration:none; display:flex; align-items:center;">
            Reset
        </a>
    </div>

    <!-- TABLE -->
    <div class="table-box">

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Thời gian</th>
                    <th>Người thực hiện</th>
                    <th>Thao tác</th>
                    <th>Module</th>
                    <th>Mô tả</th>
                </tr>
            </thead>

            <tbody>

            <?php if (!empty($logs)) { ?>
                <?php foreach ($logs as $index => $log) { ?>

                    <tr>

                        <td>
                            <?php echo $offset + $index + 1; ?>
                        </td>

                        <td>
                            <?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?>
                        </td>

                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">

                                <?php if (!empty($log['avatar'])) { ?>
                                    <img src="../../<?php echo $log['avatar']; ?>"
                                         style="width:40px;height:40px;border-radius:50%;">
                                <?php } else { ?>
                                    <img src="https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg"
                                         style="width:40px;height:40px;border-radius:50%;">
                                <?php } ?>

                                <div>
                                    <div style="font-weight:bold;">
                                        <?php echo htmlspecialchars($log['name'] ?? 'System'); ?>
                                    </div>
                                    <div style="font-size:12px;color:#888;">
                                        <?php echo htmlspecialchars($log['email'] ?? ''); ?>
                                    </div>
                                </div>

                            </div>
                        </td>

                        <td>
                            <?php
                            $class = match ($log['action']) {
                                'CREATE' => 'create',
                                'UPDATE' => 'update',
                                'DELETE' => 'delete',
                                'LOGIN' => 'login',
                                'LOGOUT' => 'logout',
                                default => ''
                            };
                    ?>
                            <span class="badge <?php echo $class; ?>">
                                <?php echo $log['action']; ?>
                            </span>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($log['module']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($log['description']); ?>
                        </td>

                    </tr>

                <?php } ?>
            <?php } else { ?>

                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;">
                        Không có dữ liệu
                    </td>
                </tr>

            <?php } ?>

            </tbody>
        </table>

    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1) { ?>

        <div class="pagination">

            <?php if ($page > 1) { ?>
                <a href="?page=1&keyword=<?php echo urlencode($keyword); ?>&action=<?php echo urlencode($action); ?>">
                    «
                </a>
            <?php } ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); ++$i) { ?>

                <a href="?page=<?php echo $i; ?>&keyword=<?php echo urlencode($keyword); ?>&action=<?php echo urlencode($action); ?>"
                   class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>

            <?php } ?>

            <?php if ($page < $totalPages) { ?>
                <a href="?page=<?php echo $totalPages; ?>&keyword=<?php echo urlencode($keyword); ?>&action=<?php echo urlencode($action); ?>">
                    »
                </a>
            <?php } ?>

        </div>

    <?php } ?>

</div>
</div>

<script>
    document.getElementById('actionFilter').addEventListener('change', function () {

        const url = new URL(window.location.href);

        if (this.value) {
            url.searchParams.set('action', this.value);
        } else {
            url.searchParams.delete('action');
        }

        url.searchParams.set('page', 1);

        window.location.href = url.toString();
    });
</script>

</body>
</html>