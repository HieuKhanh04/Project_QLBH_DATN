<?php

$count = 0;

if (isset($_SESSION['cart'])) {
    $count = array_sum($_SESSION['cart']);
}

?>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap"
rel="stylesheet">

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

    font-size:34px;

    color:#ff4fa3;

    font-family:'Pacifico', cursive;
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

</style>

<div class="top-bar">
    FREESHIP TOÀN QUỐC - GIẢM 50% CHO ĐƠN ĐẦU TIÊN
</div>

<div class="header">

    <div class="logo">
        HAN STORE
    </div>

    <div class="menu">

        <a href="index.php">TRANG CHỦ</a>

        <a href="#">SẢN PHẨM</a>

        <a href="#">KHUYẾN MÃI</a>

        <a href="#">BỘ SƯU TẬP</a>

        <a href="contact.php">LIÊN HỆ</a>

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