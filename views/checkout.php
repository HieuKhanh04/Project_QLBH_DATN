<?php

session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

$cart = $_SESSION['cart'] ?? [];
$selectedIds = [];

if (isset($_GET['ids']) && !empty($_GET['ids'])) {
    $selectedIds =
        explode(',', $_GET['ids']);

    // lưu lại session
    $_SESSION['checkout_ids'] = $selectedIds;
} else {
    // lấy lại nếu reload
    $selectedIds =
        $_SESSION['checkout_ids'] ?? [];
}

$total = 0;

?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<title>Thanh toán</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#fff7fb;
    color:#333;
}

/* MAIN */
.checkout-container{

    width:95%;

    max-width:1400px;

    margin:30px auto;

    display:grid;

    grid-template-columns:1fr 420px;

    gap:30px;

    align-items:start;
}

/* LEFT */
.checkout-left{

    background:white;

    border-radius:20px;

    padding:35px;

    box-shadow:0 4px 15px rgba(0,0,0,0.06);
}

/* RIGHT */
.checkout-right{

    background:white;

    border-radius:20px;

    padding:30px;

    box-shadow:0 4px 15px rgba(0,0,0,0.06);

    position:sticky;

    top:20px;
}

/* TITLE */
.section-title{

    font-size:28px;

    color:#ff4fa3;

    margin-bottom:30px;

    font-weight:bold;
}

/* INPUT */
.form-group{

    margin-bottom:22px;
}

.form-group label{

    display:block;

    margin-bottom:10px;

    font-weight:bold;

    font-size:15px;
}

.form-group input,
.form-group textarea,
.form-group select{

    width:100%;

    padding:14px 16px;

    border:1px solid #ffd4ea;

    border-radius:14px;

    outline:none;

    font-size:15px;

    transition:0.2s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus{

    border-color:#ff4fa3;
}

.form-group textarea{

    height:120px;

    resize:none;
}

/* SHIPPING BOX */
.shipping-box{

    background:#fff0f7;

    padding:18px;

    border-radius:14px;

    color:#777;

    margin-bottom:25px;
}

/* PAYMENT */
.payment-method{

    display:flex;

    flex-direction:column;

    gap:15px;

    margin-top:15px;
}

.payment-item{

    border:1px solid #ffd4ea;

    border-radius:14px;

    padding:18px;

    display:flex;

    align-items:center;

    gap:12px;

    cursor:pointer;

    transition:0.2s;
}

.payment-item:hover{

    border-color:#ff4fa3;

    background:#fff5fa;
}

/* CART */
.cart-title{

    font-size:24px;

    color:#ff4fa3;

    margin-bottom:25px;

    font-weight:bold;
}

/* PRODUCT */
.cart-product{

    display:flex;

    gap:15px;

    padding-bottom:20px;

    margin-bottom:20px;

    border-bottom:1px solid #f2f2f2;
}

.cart-product img{

    width:90px;
    height:110px;

    object-fit:cover;

    border-radius:14px;
}

.product-info{

    flex:1;
}

.product-color{

    color:#999;

    font-size:14px;

    margin-bottom:6px;
}

.product-name{

    font-size:16px;

    font-weight:bold;

    margin-bottom:10px;

    line-height:1.5;
}

.product-price{

    color:#ff4fa3;

    font-weight:bold;

    font-size:18px;
}

.old-price{

    color:#999;

    text-decoration:line-through;

    font-size:14px;

    margin-right:8px;
}

/* QTY */
.qty-box{

    display:flex;

    align-items:center;

    gap:12px;

    margin-top:12px;
}

.qty-btn{

    width:30px;
    height:30px;

    border:none;

    border-radius:50%;

    background:#ffe3f1;

    color:#ff4fa3;

    cursor:pointer;

    font-weight:bold;
}

/* COUPON */
.coupon-box{

    display:flex;

    gap:10px;

    margin:25px 0;
}

.coupon-box input{

    flex:1;

    padding:13px;

    border:1px solid #ffd4ea;

    border-radius:12px;

    outline:none;
}

.coupon-box button{

    padding:0 20px;

    border:none;

    border-radius:12px;

    background:#ff4fa3;

    color:white;

    cursor:pointer;
}

/* SUMMARY */
.summary{

    margin-top:20px;
}

.summary-row{

    display:flex;

    justify-content:space-between;

    margin-bottom:18px;

    font-size:16px;
}

.total{

    font-size:24px;

    font-weight:bold;

    color:#ff4fa3;
}

/* ORDER BUTTON */
.order-btn{

    width:100%;

    margin-top:30px;

    padding:18px;

    border:none;

    border-radius:16px;

    background:#ff4fa3;

    color:white;

    font-size:18px;
    font-weight:bold;
    cursor:pointer;
    transition:0.2s;
}

.order-btn:hover{
    background:#e63d8d;
}

.payment-box{
    margin-top:25px;
}

.cod-method{
    margin-top:10px;
    background:#fff0f7;
    padding:15px;
    border-radius:12px;
    display:flex;
    align-items:center;
    gap:10px;
}

.checkout-qty{
    display:flex;
    align-items:center;
    gap:10px;
}

/* ADDRESS */
.address-box{
    border:1px solid #ffd4ea;
    border-radius:16px;
    overflow:hidden;
    background:white;
}

/* TABS */
.address-tabs{
    display:flex;
    border-bottom:1px solid #ffe3f1;
}

.tab-btn{
    flex:1;
    padding:14px;
    border:none;
    background:white;
    cursor:pointer;
    font-weight:bold;
    color:#666;
    transition:0.2s;
}

.tab-btn.active{

    background:#fff0f7;
    color:#ff4fa3;
}

/* LIST */
.address-list{
    max-height:300px;
    overflow-y:auto;
    padding:10px;
}

/* ITEM */
.address-item{
    padding:12px;
    border-radius:10px;
    cursor:pointer;
    transition:0.2s;
}

.address-item:hover{
    background:#fff0f7;
    color:#ff4fa3;
}

/* SHIPPING METHOD */
.shipping-method{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#fff0f7;
    padding:15px 18px;
    border-radius:14px;
    margin-top:10px;
}

.shipping-left{
    display:flex;
    align-items:center;
    gap:12px;
}

.shipping-price{
    color:#ff4fa3;
    font-weight:bold;
}
.shipping-text{
    display:flex;
    flex-direction:column;
}

.shipping-title{
    white-space:nowrap;
    font-weight:bold;
}

.shipping-desc{
    margin-top:4px;
    color:#777;
    white-space:nowrap;
}

</style>
</head>
<body>
<?php include 'layout/header.php'; ?>

<div class="checkout-container">

    <!-- LEFT -->
    <div class="checkout-left">
        <div class="section-title">
            Thông tin giao hàng
        </div>

        <div class="form-group">
            <label>Họ và tên</label>
            <input type="text"
            placeholder="Nhập họ và tên">

        </div>

        <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text"
            placeholder="Nhập số điện thoại">

        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email"
            placeholder="Nhập email (không bắt buộc)">

        </div>

        <div class="form-group">
            <label>Địa chỉ, tên đường</label>
            <input type="text"
            placeholder="Địa chỉ, tên đường">

        </div>

        <div class="form-group">
            <label>Địa chỉ</label>

            <div class="address-box">

                <!-- TAB -->
                <div class="address-tabs">

                    <button type="button"
                        class="tab-btn active"
                        id="provinceTab">
                        Tỉnh / Thành phố
                    </button>

                    <button type="button"
                        class="tab-btn"
                        id="districtTab">
                        Quận / Huyện
                    </button>

                    <button type="button"
                        class="tab-btn"
                        id="wardTab">
                        Phường / Xã
                    </button>
                </div>

                <!-- LIST -->
                <div class="address-list"
                    id="addressList">
                    Đang tải...
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Phương thức giao hàng</label>

            <div class="shipping-box"
                id="shippingBox">

                Nhập địa chỉ để xem các phương thức giao hàng

            </div>

        </div>

        <div class="payment-box">
            <h3>Phương thức thanh toán</h3>
            <div class="cod-method">
                <input type="radio"
                    checked
                    disabled>

                <span>
                    Thanh toán khi nhận hàng (COD)
                </span>
            </div>
        </div>

        <div class="form-group">
            <label>Ghi chú đơn hàng</label>
            <textarea></textarea>
        </div>

    </div>

    <!-- RIGHT -->
    <div class="checkout-right">

        <div class="cart-title">
            Giỏ hàng
        </div>

        <?php foreach ($cart as $id => $qty) {
            // chỉ lấy sản phẩm được tick
            if (!in_array($id, $selectedIds)) {
                continue;
            }

            $product = $productModel->getProductById($id);

            if ($product) {
                $subtotal = $product['price'] * $qty;

                $total += $subtotal;
                ?>

            <div class="cart-product">

                <img src="https://picsum.photos/200/250">
                <div class="product-info">

                    <div class="product-name">
                        <?php echo $product['name']; ?>
                    </div>

                    <div class="product-color">
                        light grey / L
                    </div>

                    <div class="qty-box">

                        <button class="qty-btn"
                            onclick="updateQty(<?php echo $id; ?>, 'decrease')">

                            -

                        </button>

                        <span id="qty-<?php echo $id; ?>">
                            <?php echo $qty; ?>
                        </span>

                        <button class="qty-btn"
                            onclick="updateQty(<?php echo $id; ?>, 'increase')">

                            +

                        </button>

                    </div>

                    <div class="product-price"
                        id="price-<?php echo $id; ?>"
                        data-price="<?php echo $product['price']; ?>">
                        <span class="old-price">
                            <?php echo number_format($product['price'] + 100000); ?>₫
                        </span>
                        <?php echo number_format($product['price']); ?>₫

                    </div>

                </div>

            </div>

        <?php }
            } ?>

        <!-- COUPON -->
        <div class="coupon-box">

            <input type="text"
            placeholder="Nhập mã khuyến mãi">

            <button>
                Áp dụng
            </button>

        </div>

        <!-- SUMMARY -->
        <div class="summary">

            <div class="summary-row">
                <span>Tổng tiền hàng</span>
                <span id="productTotal"
                    data-total="<?php echo $total; ?>">
                    <?php echo number_format($total); ?>₫
                </span>
            </div>

            <div class="summary-row">
                <span>Phí vận chuyển</span>
                <span id="shippingFee">
                    0₫
                </span>
            </div>

            <div class="summary-row total">
                <span>Tổng thanh toán</span>
                <span id="finalTotal">
                    <?php echo number_format($total); ?>₫
                </span>
            </div>

        </div>

        <button class="order-btn">
            ĐẶT HÀNG
        </button>

    </div>

</div>

    <script>
        let selectedProvinceName = "";
        let selectedDistrictName = "";
        let selectedWardName = "";

        /* LOAD PROVINCE */
        fetch("https://provinces.open-api.vn/api/p/")
            .then(res => res.json())
            .then(data => {

                renderProvince(data);
            });

        /* RENDER PROVINCE */
        function renderProvince(data){

            let list =
                document.getElementById("addressList");

            list.innerHTML = "";

            data.forEach(item => {

                list.innerHTML += `
                    <div class="address-item"
                        onclick="selectProvince(${item.code})">

                        ${item.name}

                    </div>
                `;
            });
        }

        /* SELECT PROVINCE */
        function selectProvince(code, name){

            selectedProvinceCode = code;
            selectedProvinceName = name;


            document.getElementById("districtTab")
                .classList.add("active");

            fetch(`https://provinces.open-api.vn/api/p/${code}?depth=2`)
                .then(res => res.json())
                .then(data => {

                    renderDistrict(data.districts);
                });
        }

        /* RENDER DISTRICT */
        function renderProvince(data){

            let list =
                document.getElementById("addressList");

            list.innerHTML = "";

            data.forEach(item => {

                list.innerHTML += `
                    <div class="address-item"
                        onclick="selectProvince(${item.code}, '${item.name}')">

                        ${item.name}

                    </div>
                `;
            });
        }

        /* SELECT DISTRICT */
        function selectDistrict(code, name){

            selectedDistrictCode = code;
            selectedDistrictName = name;

            document.getElementById("wardTab")
                .classList.add("active");

            fetch(`https://provinces.open-api.vn/api/d/${code}?depth=2`)
                .then(res => res.json())
                .then(data => {

                    renderWard(data.wards);
                });
        }

        function renderDistrict(data){
            let list =
                document.getElementById("addressList");

            list.innerHTML = "";

            data.forEach(item => {

                list.innerHTML += `
                    <div class="address-item"
                        onclick="selectDistrict(${item.code}, '${item.name}')">

                        ${item.name}

                    </div>
                `;
            });
        }

        /* RENDER WARD */
        function renderWard(data){

            let list =
                document.getElementById("addressList");

            list.innerHTML = "";

            data.forEach(item => {

                list.innerHTML += `
                    <div class="address-item"
                        onclick="selectWard('${item.name}')">

                        ${item.name}

                    </div>
                `;
            });
        }

        function selectWard(name){

            selectedWardName = name;

            let fullAddress =
                selectedWardName + ", "
                + selectedDistrictName + ", "
                + selectedProvinceName;

            /* HIỆN ĐỊA CHỈ */
            document.getElementById("addressList")
                .innerHTML = `
                    <div class="address-item"
                        style="
                            background:#fff0f7;
                            color:#ff4fa3;
                            font-weight:bold;
                        ">
                        ${fullAddress}
                    </div>
                `;

            /* TÍNH SHIP */
            let ship = 30000;

            if(
                selectedProvinceName === "Hà Nội"
                || selectedProvinceName === "TP Hồ Chí Minh"
            ){
                ship = 15000;
            }

            if(
                selectedProvinceName === "Đà Nẵng"
                || selectedProvinceName === "Hải Phòng"
                || selectedProvinceName === "Cần Thơ"
            ){
                ship = 25000;
            }

            /* HIỆN SHIPPING */
            document.getElementById("shippingBox")
                .innerHTML = `
                    <div class="shipping-method">

                        <div class="shipping-left">

                            <input type="radio"
                                checked>

                            <div class="shipping-text">

                                <div class="shipping-title">
                                    Giao hàng tận nơi
                                </div>

                                <small class="shipping-desc">
                                    Nhận hàng từ 2 - 5 ngày
                                </small>

                            </div>

                        </div>

                        <div class="shipping-price">

                            ${ship.toLocaleString()}₫

                        </div>

                    </div>
                `;

            /* UPDATE TOTAL */
            updateTotal(ship);
        }

        /* CLICK TAB */
        document.getElementById("provinceTab")
            .onclick = function(){

            fetch("https://provinces.open-api.vn/api/p/")
                .then(res => res.json())
                .then(data => {

                    renderProvince(data);
                });
        };

        document.getElementById("districtTab")
            .onclick = function(){

            if(!selectedProvinceCode) return;

            fetch(`https://provinces.open-api.vn/api/p/${selectedProvinceCode}?depth=2`)
                .then(res => res.json())
                .then(data => {

                    renderDistrict(data.districts);
                });
        };

        document.getElementById("wardTab")
            .onclick = function(){

            if(!selectedDistrictCode) return;

            fetch(`https://provinces.open-api.vn/api/d/${selectedDistrictCode}?depth=2`)
                .then(res => res.json())
                .then(data => {

                    renderWard(data.wards);
                });
        };

        function updateTotal(ship){
            let productTotal =
                <?php echo $total; ?>;

            let finalTotal =
                productTotal + ship;

            /* SHIP */
            document.getElementById("shippingFee")
                .innerText =
                    ship.toLocaleString() + "₫";

            /* TOTAL */
            document.getElementById("finalTotal")
                .innerText =
                    finalTotal.toLocaleString() + "₫";
        }

    </script>

    <script>

    async function updateQty(id, action){

        let response = await fetch(
            `../controllers/CartController.php?action=${action}&id=${id}&ajax=1`
        );

        let data = await response.json();

        /* lấy số lượng hiện tại */
        let qtyElement =
            document.getElementById("qty-" + id);

        let qty =
            parseInt(qtyElement.innerText);

        if(action === "increase"){
            qty++;
        }else{
            if(qty > 1){
                qty--;
            }
        }

        qtyElement.innerText = qty;

        /* GIÁ 1 SP */
        let priceElement =
            document.getElementById("price-" + id);

        let price =
            parseInt(priceElement.dataset.price);

        /* UPDATE GIÁ SP */
        let subtotal = price * qty;

        priceElement.innerHTML =
            subtotal.toLocaleString() + "₫";

        /* UPDATE TỔNG */
        updateAllTotal();
    }

    /* TÍNH LẠI TOTAL */
    function updateAllTotal(){

        let productTotal = 0;

        document.querySelectorAll(".product-price")
            .forEach(item => {

                let text =
                    item.innerText
                        .replace(/[₫,.]/g, "");

                productTotal += parseInt(text);
            });

        /* SHIP */
        let shipText =
            document.getElementById("shippingFee")
                .innerText
                .replace(/[₫,.]/g, "");

        let ship = parseInt(shipText) || 0;

        /* UPDATE */
        document.getElementById("productTotal")
            .innerText =
                productTotal.toLocaleString() + "₫";

        document.getElementById("finalTotal")
            .innerText =
                (productTotal + ship).toLocaleString() + "₫";
    }

    </script>
</body>
</html>