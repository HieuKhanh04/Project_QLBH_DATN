<?php

session_start();

require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

/* TÌM KIẾM */
$keyword = trim($_GET['keyword'] ?? '');

if ($keyword != '') {
    $stmt = $conn->prepare('
        SELECT
            r.*,
            p.name AS product_name,
            pi.image_url
        FROM product_reviews r
        INNER JOIN products p
            ON r.product_id = p.product_id

        LEFT JOIN product_images pi
            ON p.product_id = pi.product_id
            AND pi.is_main = 1

        WHERE
            p.name LIKE ?
            OR r.customer_name LIKE ?
            OR r.comment LIKE ?

        ORDER BY r.created_at DESC
    ');

    $stmt->execute([
        "%$keyword%",
        "%$keyword%",
        "%$keyword%",
    ]);
} else {
    $stmt = $conn->query('
        SELECT
            r.*,
            p.name AS product_name,
            pi.image_url
        FROM product_reviews r
        INNER JOIN products p
            ON r.product_id = p.product_id

        LEFT JOIN product_images pi
            ON p.product_id = pi.product_id
            AND pi.is_main = 1

        ORDER BY r.created_at DESC
    ');
}

$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* XÓA ĐÁNH GIÁ */
if (isset($_POST['delete'])) {
    $id = (int) $_POST['delete'];

    $stmt = $conn->prepare('
        SELECT
            review_id,
            product_id,
            customer_name
        FROM reviews
        WHERE review_id=?
    ');

    $stmt->execute([$id]);

    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare('
        DELETE FROM reviews
        WHERE review_id=?
    ');

    $stmt->execute([$id]);

    writeLog(
        $conn,
        'DELETE',
        'Đánh giá',
        'Xóa đánh giá #'.$id.
        ' - '.$review['customer_name']
    );

    header('Location: reviews.php?deleted=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Quản lý đánh giá</title>

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

/* ADMIN BOX */
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

/* SEARCH */
.search-box{
    width:340px;
    height:50px;
    background:white;
    border-radius:14px;
    padding:0 16px;
    display:flex;
    align-items:center;
    border:1px solid #ffe3ef;
    margin-bottom:30px;
}

.search-box input{
    border:none;
    outline:none;
    background:transparent;
    flex:1;
    font-size:15px;
}

.search-btn{
    border:none;
    background:none;
    color:#ff4fa3;
    cursor:pointer;
}

/* TABLE */
.review-table{
    background:white;
    border-radius:24px;
    padding:25px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    text-align:left;
    padding-bottom:18px;
    color:#999;
}

table td{
    padding:18px 10px;
    border-top:1px solid #f3f3f3;
    vertical-align:top;
}

/* PRODUCT INFO */
.product-info{
    display:flex;
    align-items:center;
    gap:12px;
}

.product-info img{
    width:60px;
    height:60px;
    border-radius:12px;
    object-fit:cover;
}

/* STAR */
.star{
    color:#ffb400;
}

/* MODAL */
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
    width:480px;
    background:#fff;
    border-radius:24px;
    padding:30px;
    text-align:center;
}

.modal-icon{
    font-size:60px;
    color:#ff4d6d;
    margin-bottom:10px;
}

.modal-actions{
    margin-top:20px;
    display:flex;
    justify-content:center;
    gap:12px;
}

.cancel-btn{
    background:#eee;
    border:none;
    padding:10px 18px;
    border-radius:10px;
    cursor:pointer;
}

.save-btn{
    background:#ff4fa3;
    color:#fff;
    border:none;
    padding:10px 18px;
    border-radius:10px;
    cursor:pointer;
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

                <a href="reviews.php" class="active">
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

    <div class="main-content">

    <!-- TOPBAR -->
    <div class="topbar">

        <div class="page-title">
            <h1>Quản lý đánh giá</h1>
            <p>Quản lý đánh giá của khách hàng về sản phẩm</p>
        </div>

        <div class="admin-box">
            <img src="https://img.magnific.com/free-vector/smiling-woman-with-glasses_1308-177859.jpg">
            <div>
                <strong>Admin</strong><br>
                <small>Quản trị viên</small>
            </div>
        </div>

    </div>

    <!-- SEARCH -->
    <div class="header-action">
        <form method="GET" class="search-box">
            <input type="text" name="keyword"
                   placeholder="Tìm đánh giá..."
                   value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
            <button class="search-btn">
                <i class="fa fa-search"></i>
            </button>
        </form>
    </div>

    <!-- TABLE -->
    <div class="review-table">
        <table>
            <tr>
                <th>Sản phẩm</th>
                <th>Khách hàng</th>
                <th>Đánh giá</th>
                <th>Bình luận</th>
                <th>Thời gian</th>
                <th>Thao tác</th>
            </tr>

            <?php foreach ($reviews as $r) { ?>

            <tr>
                <!-- SẢN PHẨM -->
                <td>
                    <div class="product-info">
                        <img src="../../<?php echo $r['image_url'] ?? 'uploads/no-image.png'; ?>">
                        <div>
                            <strong><?php echo htmlspecialchars($r['product_name']); ?></strong><br>
                            <small>#SP<?php echo $r['product_id']; ?></small><br>

                            <?php if (!empty($r['size']) || !empty($r['color'])) { ?>
                                <small>
                                    <?php echo $r['size'] ?? ''; ?>
                                    <?php echo $r['color'] ? ' / '.$r['color'] : ''; ?>
                                </small>
                            <?php } ?>
                        </div>
                    </div>
                </td>

                <!-- KHÁCH HÀNG -->
                <td>
                    <?php echo htmlspecialchars($r['customer_name']); ?>
                </td>

                <!-- RATING -->
                <td>
                    <?php for ($i = 1; $i <= 5; ++$i) { ?>
                        <i class="fa-star fa-<?php echo $i <= $r['rating'] ? 'solid star' : 'regular'; ?>"></i>
                    <?php } ?>
                </td>

                <!-- COMMENT -->
                <td>
                    <?php echo nl2br(htmlspecialchars($r['comment'])); ?>
                </td>

                <!-- TIME -->
                <td>
                    <?php echo $r['created_at']; ?>
                </td>

                <!-- ACTION -->
                <td>
                    <!-- XEM -->
                    <button
                        onclick="location.href='review_detail.php?id=<?php echo $r['review_id']; ?>'"
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

                    <!-- XÓA -->
                    <button
                        type="button"
                        onclick="openDeleteModal(<?php echo $r['review_id']; ?>)"
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

<!-- DELETE MODAL -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <i class="fa-solid fa-circle-exclamation modal-icon"></i>
        <h2>Xóa đánh giá</h2>
        <p>Bạn có chắc muốn xóa đánh giá này không?</p>
        <form method="POST">

            <input type="hidden" name="delete" id="deleteId">

            <div class="modal-actions">

                <button type="button"
                        class="cancel-btn"
                        onclick="closeDeleteModal()">
                    Hủy
                </button>

                <button type="submit"
                        class="save-btn">
                    Xóa
                </button>
            </div>
        </form>
    </div>
</div>

<div id="successModal" class="modal">
    <div class="modal-content">
        <i class="fa-solid fa-circle-check modal-icon" style="color:#23b26d;"></i>
        <h2>Xóa thành công</h2>
        <div class="modal-actions">
            <button class="save-btn" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(id){
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteModal').style.display = 'flex';
    }
    function closeDeleteModal(){
        document.getElementById('deleteModal').style.display = 'none';
    }
    function closeSuccessModal(){
        document.getElementById('successModal').style.display = 'none';
    }
    /* click ngoài modal để đóng */
    window.onclick = function(e){
        if(e.target == document.getElementById('deleteModal')){
            closeDeleteModal();
        }
        if(e.target == document.getElementById('successModal')){
            closeSuccessModal();
        }
    }
</script>

<?php if (isset($_GET['deleted'])) { ?>
    <script>
        window.onload = function(){
            document.getElementById('successModal').style.display = 'flex';
        };
    </script>
<?php } ?>

</div>
</body>
</html>