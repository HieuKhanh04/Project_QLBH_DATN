<?php
$count = 0;

if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item) && isset($item['quantity'])) {
            $count += (int) $item['quantity'];
        }
    }
}

// if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
//     foreach ($_SESSION['cart'] as $item) {
//         if (is_array($item) && isset($item['quantity'])) {
//             $count += (int) $item['quantity'];
//         }

//         // trường hợp cart lưu kiểu cũ: [id => qty]
//         elseif (is_numeric($item)) {
//             $count += (int) $item;
//         }
//     }
// }

?>
<?php
$current = basename($_SERVER['PHP_SELF']);

/* THÔNG BÁO */
// $customerId = $_SESSION['customer_id'] ?? 0;

$customerId = $_SESSION['user']['user_id'] ?? 0;

$stmt = $conn->prepare('
    SELECT
        n.notification_id,
        n.title,
        n.content,
        n.type,
        n.created_at,
        COALESCE(r.is_read, 0) AS is_read
    FROM notifications n
    LEFT JOIN notification_reads r
        ON n.notification_id = r.notification_id
        AND r.customer_id = ?
    ORDER BY n.created_at DESC
');

$stmt->execute([$customerId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
// echo '<pre>';
// print_r($notifications);
// exit;

$unreadCount = 0;

foreach ($notifications as $n) {
    if ((int) $n['is_read'] === 0) {
        ++$unreadCount;
    }
}
?>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap"
rel="stylesheet"> -->

<style>

    .top-bar{

        background:#ff85c1;

        color:white;

        text-align:center;

        padding:10px;

        font-size:14px;
    }

    .header{

        background:white;

        height:90px;

        display:flex;

        align-items:center;

        justify-content:space-between;

        padding:0 50px;

        box-shadow:0 2px 10px rgba(0,0,0,0.05);

        position:sticky;

        top:0;

        z-index:1000;
    }

    .logo{

        font-family: 'Great Vibes', cursive;
        font-size: 42px;
        color: #ff4fa3;
        font-weight: bold;
        text-shadow: 0 2px 6px rgba(255, 79, 163, 0.3);
        text-decoration: none;
    }

    .menu{

        display:flex;

        gap:35px;
    }

    .menu a{

        text-decoration:none;

        color:#333;

        font-size:16px;

        font-weight:bold;
    }

    .menu a:hover{
        color:#ff4fa3;
    }

    .search-form{

        display:flex;

        align-items:center;

        background:#fff0f7;

        border-radius:30px;

        overflow:hidden;
    }

    .search-form input{

        border:none;

        outline:none;

        padding:10px 15px;

        width:220px;

        background:transparent;
    }

    .search-form button{

        border:none;

        background:none;

        padding:0 15px;

        cursor:pointer;

        color:#ff4fa3;
    }

    .header-icons{

        display:flex;

        gap:15px;
    }

    .icon-box{

        width:45px;
        height:45px;

        border-radius:50%;

        background:#fff0f7;

        display:flex;

        align-items:center;
        justify-content:center;

        text-decoration:none;

        position:relative;
    }

    .icon-box i{

        color:#ff4fa3;

        font-size:18px;
    }

    .cart-count{

        position:absolute;

        top:-5px;
        right:-5px;

        background:red;

        color:white;

        min-width:18px;
        height:18px;

        border-radius:50%;

        display:flex;

        align-items:center;
        justify-content:center;

        font-size:11px;
    }

    .logo{
        text-decoration:none;
        font-weight: bold;
    }

    .menu a.active{
        color:#ff4fa3;
        border-bottom:2px solid #ff4fa3;
        padding-bottom:5px;
    }

    .menu a{
        text-decoration:none;
        color:#333;
        font-size:16px;
        font-weight:bold;
        position:relative;
        transition:0.2s;
    }

    /* hover */
    .menu a:hover{
        color:#ff4fa3;
    }

    /* ACTIVE */
    .menu a.active-menu{
        color:#ff4fa3;
    }

    /* gạch chân animation */
    .menu a.active-menu::after{
        content:"";
        position:absolute;
        left:0;
        bottom:-5px;
        width:100%;
        height:2px;
        background:#ff4fa3;
        border-radius:2px;
    }

    .menu a.active{
        color:#ff4fa3;
        border-bottom:2px solid #ff4fa3;
        padding-bottom:5px;
    }

    .notification-box{
    position:relative;
}

    /* badge số thông báo */
    .notif-count{
        position:absolute;
        top:-5px;
        right:-5px;
        background:red;
        color:white;
        font-size:11px;
        width:18px;
        height:18px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
    }

    /* dropdown */
    .notif-dropdown{
        position:absolute;
        top:55px;
        right:0;
        width:260px;
        background:white;
        box-shadow:0 5px 20px rgba(0,0,0,0.15);
        border-radius:12px;
        padding:10px;
        display:none;
        z-index:999;
    }

    /* item */
    .notif-item{
        padding:10px;
        font-size:14px;
        border-bottom:1px solid #eee;
        cursor:pointer;
    }

    .notif-item:hover{
        background:#fff0f7;
    }

    /* hover mở dropdown */
   .notif-dropdown{
        display:none;
    }

    .notif-dropdown.show{
        display:block;
    }

    .notif-item-link{
        text-decoration:none;
        color:#333;
        display:block;
    }

    .notif-title{
        font-weight:700;
        margin-bottom:4px;
    }

    .notif-content{
        font-size:12px;
        color:#777;
    }

    .notif-item.unread{
        background:#fff0f7;
        border-left:4px solid #ff4fa3;
    }

    .notif-dropdown{
        width:320px;
        max-height:400px;
        overflow-y:auto;
    }

    .notif-dropdown{
        position:absolute;
        top:55px;
        right:0;
        width:320px;
        max-height:400px;
        overflow-y:auto;
        background:#fff;
        border-radius:12px;
        box-shadow:0 10px 25px rgba(0,0,0,.15);
        z-index:9999;
    }

</style>

<div class="top-bar">
    FREESHIP TOÀN QUỐC - GIẢM 50% CHO ĐƠN ĐẦU TIÊN
</div>

<div class="header">

    <a href="index.php" class="logo">
        HAN STORE
    </a>

    <div class="menu">
        <a href="index.php" class="<?php echo $current == 'index.php' ? 'active' : ''; ?>">TRANG CHỦ</a>
        <a href="products.php"
            class="<?php echo ($current == 'products.php') ? 'active-menu' : ''; ?>">
            SẢN PHẨM
            </a>
        <a href="promotion.php" class="<?php echo $current == 'promotion.php' ? 'active' : ''; ?>">
            KHUYẾN MÃI
        </a>

        <a href="collections.php"
            class="<?php if ($current == 'collections.php') {
                echo 'active';
            } ?>">
                BỘ SƯU TẬP
            </a>
        <a href="contact.php" class="<?php echo $current == 'contact.php' ? 'active' : ''; ?>">LIÊN HỆ</a>
    </div>

    <form action="index.php"
    method="GET"
    class="search-form">

        <input type="text"
        name="keyword"
        placeholder="Tìm sản phẩm...">

        <button type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

    </form>

    <div class="header-icons">

        <div class="notification-box">
            <a href="javascript:void(0)"
                class="icon-box"
                id="notificationBtn">
                <i class="fa-regular fa-bell"></i>
                <?php if ($unreadCount > 0) { ?>
                    <span class="notif-count">
                        <?php echo $unreadCount; ?>
                    </span>
                <?php } ?>
            </a>

            <div class="notif-dropdown">
                <?php if (empty($notifications)) { ?>
                    <div class="notif-item">
                        Không có thông báo
                    </div>
                <?php } ?>

                <?php foreach ($notifications as $n) { ?>
                    <a href="notification_detail.php?id=<?php echo $n['notification_id']; ?>"
                    class="notif-item-link">
                        <div class="notif-item <?php echo $n['is_read'] == 0 ? 'unread' : ''; ?>">
                            <div class="notif-title">
                                <?php echo htmlspecialchars($n['title']); ?>
                            </div>

                            <div class="notif-content">
                                <?php
                                echo mb_substr(
                                    strip_tags($n['content']),
                                    0,
                                    60
                                );
                    ?>
                                ...
                            </div>
                        </div>
                    </a>
                <?php } ?>
            </div>
        </div>

        <a href="profile.php" class="icon-box">
            <i class="fa-regular fa-user"></i>
        </a>

        <a href="cart.php" class="icon-box">

            <i class="fa-solid fa-bag-shopping"></i>

            <?php if ($count > 0) { ?>

                <span class="cart-count">
                    <?php echo $count; ?>
                </span>

            <?php } ?>

        </a>

    </div>

</div>

<script>

const notificationBtn =
    document.getElementById('notificationBtn');

const notificationDropdown =
    document.querySelector('.notif-dropdown');

notificationBtn.addEventListener('click', function(e){

    e.preventDefault();

    notificationDropdown.classList.toggle('show');

});

/* click ngoài sẽ đóng */
document.addEventListener('click', function(e){

    if(
        !e.target.closest('.notification-box')
    ){
        notificationDropdown.classList.remove('show');
    }

});

</script>