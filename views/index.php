<?php

session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);
$products = $productModel->getAllProducts() ?? [];

$count = 0;

if (isset($_SESSION['cart'])) {
    $count = array_sum($_SESSION['cart']);
}

?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<meta charset="UTF-8">
<title>Trang chủ</title>

<style>
    body {
        margin: 0;
        font-family: Arial;
        background: #fff5f9;
    }

    /* HEADER */
    .header {
        background: #ff85c1;
        padding: 15px 30px;
        display: flex;
        align-items: center;
        position: relative;
    }

    /* LOGO */
    .logo {
        color: white;
        font-size: 28px;
        font-family: 'Pacifico', cursive;
    }

    /* SEARCH BOX (FIX 1 MÀU) */
    .search-box {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 500px;
        display: flex;
        background: white;
        border-radius: 30px;
        overflow: hidden;
        border: 2px solid #ff85c1;
    }

    .search-box input {
        flex: 1;
        padding: 10px 15px;
        border: none;
        outline: none;
    }

    /* ICON SEARCH */
    .search-box button {
        background: white;
        border: none;
        padding: 0 15px;
        cursor: pointer;
        color: #ff85c1;
        font-size: 16px;
    }

    /* HEADER ICONS */
.header-icons{
    margin-left:auto;

    display:flex;
    align-items:center;

    gap:15px;
}

    /* ICON BOX */
    .icon-box{

        width:45px;
        height:45px;

        border-radius:50%;

        background:white;

        display:flex;
        align-items:center;
        justify-content:center;

        position:relative;

        text-decoration:none;

        box-shadow:0 4px 10px rgba(0,0,0,0.1);

        transition:0.2s;
    }

    /* ICON */
    .icon-box i{
        color:#ff85c1;
        font-size:18px;
    }

    /* HOVER */
    .icon-box:hover{
        transform:scale(1.08);
    }

    /* BADGE */
    .cart-count{

        position:absolute;

        top:-5px;
        right:-5px;

        background:red;
        color:white;

        font-size:11px;

        min-width:18px;
        height:18px;

        border-radius:50%;

        display:flex;
        align-items:center;
        justify-content:center;
    }

    /* BADGE SỐ LƯỢNG */
    .cart-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: red;
        color: white;
        font-size: 12px;
        padding: 3px 6px;
        border-radius: 50%;
    }

    /* GRID */
    .product-list {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        padding: 20px;
    }

    /* CARD */
    .product-card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: 0.3s;
        cursor: pointer;
    }

    .product-card:hover {
        transform: translateY(-5px);
    }

    .product-link{
        text-decoration: none;
        color: inherit;
    }

    /* IMAGE */
    .product-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 10px;
    }

    /* TEXT */
    .product-card h3 {
        margin: 10px 0;
    }

    .product-card p {
        color: #e05297;
        font-weight: bold;
    }

    /* BUTTON */
    .product-card a {
        display: inline-block;
        margin-top: 10px;
        padding: 8px 12px;
        background: #ff85c1;
        color: white;
        border-radius: 8px;
        text-decoration: none;
    }
    .search-box{
        position: relative; /* QUAN TRỌNG */
    }

    #suggest-box{
         position:absolute;
        top:45px;
        left:0;
        width:100%;

        background:white;
        border:1px solid #eee;
        border-radius:10px;

        max-height:250px;
        overflow-y:auto;

        z-index:999;
    }

    .suggest-item{
        padding:10px;
        cursor:pointer;
    }

    .suggest-item:hover{
        background:#ffe3f1;
    }
</style>
</head>

<body>


<div class="header">

    <!-- LOGO -->
    <div class="logo">
        HAN STORE
    </div>

    <!-- SEARCH -->
    <form class="search-box"
      action="../controllers/HomeController.php"
      method="GET">

        <input type="text"
            name="keyword"
            placeholder="Tìm sản phẩm...">

        <!-- THÊM BOX GỢI Ý -->
        <div id="suggest-box"></div>

        <button type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

    </form>

    <!-- RIGHT ICON -->
    <div class="header-icons">

        <!-- CART -->
        <a href="../views/cart.php" class="icon-box">

            <i class="fa-solid fa-cart-shopping"></i>

            <?php if ($count > 0) { ?>

                <span class="cart-count">
                    <?php echo $count; ?>
                </span>

            <?php } ?>

        </a>

        <!-- NOTIFICATION -->
        <a href="#" class="icon-box">
            <i class="fa-regular fa-bell"></i>
        </a>

        <!-- ACCOUNT -->
        <a href="profile.php" class="icon-box">
            <i class="fa-regular fa-user"></i>
        </a>

    </div>

</div>

<div class="product-list">

<?php foreach ($products as $p) { ?>

    <div class="product-card"
         onclick="goDetail(<?php echo $p['id']; ?>)">

        <img src="https://via.placeholder.com/150">

        <h3><?php echo $p['name']; ?></h3>

        <p><?php echo number_format($p['price']); ?> VND</p>

        <!-- NÚT GIỎ HÀNG GIỮ NGUYÊN -->
        <a href="../controllers/CartController.php?action=add&id=<?php echo $p['id']; ?>"
           onclick="event.stopPropagation()">
            Thêm vào giỏ
        </a>

    </div>

<?php } ?>
</div>

    <script>
        let input = document.querySelector("input[name='keyword']");
        let box = document.getElementById("suggest-box");

        input.addEventListener("keyup", function(e){

            let keyword = this.value;

            if(keyword.length == 0){
                box.innerHTML = "";
                return;
            }

            fetch("../controllers/SuggestController.php?keyword=" + keyword)
            .then(res => res.json())
            .then(data => {

                let html = "";

                data.forEach(item => {
                    html += `<div class="suggest-item"
                                onclick="selectItem(`${item.name}`)">
                                ${item.name}
                            </div>`;
                });

                box.innerHTML = html;
            });
        });

        // click gợi ý
        function selectItem(name){
            input.value = name;
            box.innerHTML = "";
        }

        // ENTER để search
        input.addEventListener("keypress", function(e){
            if(e.key === "Enter"){
                box.innerHTML = "";
            }
        });
    </script>

    <script>
        function goDetail(id){
            window.location.href = "/DATN_Code/views/product_detail.php?id=" + id;
        }
    </script>
</body>
</html>