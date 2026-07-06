<?php
session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

$user = $_SESSION['customer'] ?? null;
$cart = $_SESSION['cart'] ?? [];
$buyNow = $_SESSION['buy_now'] ?? null;

$vouchers = [];
if (!empty($user['user_id'])) {
    $stmtVoucher = $conn->prepare("
        SELECT
            p.*
        FROM user_vouchers uv
        INNER JOIN promotions p
            ON uv.promotion_id = p.promotion_id
        WHERE
            uv.user_id = ?
            AND uv.is_used = 0
            AND p.status = 'active'
            AND CURDATE() BETWEEN p.start_date AND p.end_date
            AND p.used_count < p.quantity
        ORDER BY p.end_date ASC
    ");
    $stmtVoucher->execute([
        $user['user_id'],
    ]);
    $vouchers = $stmtVoucher->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['ids']) && !empty($_GET['ids'])) {
    $_SESSION['checkout_ids'] = explode(',', $_GET['ids']);
}
$isBuyNow = !empty($_SESSION['buy_now']);
$checkoutIds = $_SESSION['checkout_ids'] ?? [];

$total = 0;
$checkoutProducts = [];

if ($buyNow) {
    foreach ($buyNow as $cartKey => $item) {
        $productId = (int) $item['product_id'];
        $quantity = (int) $item['quantity'];
        $size = $item['size'] ?? '';
        $color = $item['color'] ?? '';

        $product = $productModel->getProductById($productId);

        if (!$product) {
            continue;
        }

        $stmtVariant = $conn->prepare('
            SELECT *
            FROM product_variants
            WHERE product_id = ?
            ');

        $stmtVariant->execute([$productId]);

        $variant = null;

        while ($row = $stmtVariant->fetch(PDO::FETCH_ASSOC)) {
            $sizeMatch =
                ($size == '' || $row['size'] == $size);

            $colorMatch =
                ($color == '' || $row['color'] == $color);

            if ($sizeMatch && $colorMatch) {
                $variant = $row;
                break;
            }
        }

        $image = '../uploads/no-image.jpg';
        if ($variant && !empty($variant['image'])) {
            $image = '../'.$variant['image'];
        } else {
            $stmtImage = $conn->prepare('
                SELECT image_url
                FROM product_images
                WHERE product_id=?
                ORDER BY is_main DESC,id ASC
                LIMIT 1
            ');
            $stmtImage->execute([$productId]);
            $img = $stmtImage->fetchColumn();
            if ($img) {
                $image = '../'.$img;
            }
        }

        if (!$image) {
            $image = 'https://picsum.photos/400/500?random='.$productId;
        }

        $price = $variant['price'] ?? $product['price'];
        $subtotal = $price * $quantity;

        $total += $subtotal;

        $checkoutProducts[] = [
            'cart_key' => $cartKey,
            'product_id' => $productId,
            'name' => $product['name'],
            'price' => $price,
            'quantity' => $quantity,
            'size' => $size,
            'color' => $color,
            'image' => $image,
            'subtotal' => $subtotal,
        ];
    }
} else {
    foreach ($checkoutIds as $cartKey) {
        if (!isset($cart[$cartKey])) {
            continue;
        }

        $item = $cart[$cartKey];

        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        $size = $item['size'] ?? '';
        $color = $item['color'] ?? '';

        $product = $productModel->getProductById($productId);

        if (!$product) {
            continue;
        }

        $stmtVariant = $conn->prepare('
            SELECT *
            FROM product_variants
            WHERE product_id = ?
            ');

        $stmtVariant->execute([$productId]);

        $variant = null;

        while ($row = $stmtVariant->fetch(PDO::FETCH_ASSOC)) {
            $sizeMatch =
                ($size == '' || $row['size'] == $size);

            $colorMatch =
                ($color == '' || $row['color'] == $color);

            if ($sizeMatch && $colorMatch) {
                $variant = $row;
                break;
            }
        }

        $image = '../uploads/no-image.jpg';
        if ($variant && !empty($variant['image'])) {
            $image = '../'.$variant['image'];
        } else {
            $stmtImage = $conn->prepare('
                SELECT image_url
                FROM product_images
                WHERE product_id=?
                ORDER BY is_main DESC,id ASC
                LIMIT 1
            ');
            $stmtImage->execute([$productId]);
            $img = $stmtImage->fetchColumn();
            if ($img) {
                $image = '../'.$img;
            }
        }

        if (!$image) {
            $image = 'https://picsum.photos/400/500?random='.$productId;
        }

        $price = $variant['price'] ?? $product['price'];
        $subtotal = $price * $quantity;

        $total += $subtotal;

        $checkoutProducts[] = [
            'cart_key' => $cartKey,
            'product_id' => $productId,
            'name' => $product['name'],
            'price' => $price,
            'quantity' => $quantity,
            'size' => $size,
            'color' => $color,
            'image' => $image,
            'subtotal' => $subtotal,
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Thanh toán</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>

        :root{
            --pink:#ff4fa3;
            --pink-dark:#e63d8d;
            --bg:#fff7fb;
        }

        body{
            background:var(--bg);
            font-family:'Quicksand',sans-serif;
            font-weight:600;
        }

        .checkout-card{
            border:none;
            border-radius:18px;
            box-shadow:0 4px 16px rgba(0,0,0,.08);
        }

        .section-title{
            color:var(--pink);
            font-size:28px;
            font-weight:700;
        }

        .btn-pink{
            background:var(--pink);
            color:#fff;
            border:none;
            border-radius:12px;
            font-weight:700;
        }

        .btn-pink:hover{
            background:var(--pink-dark);
            color:#fff;
        }

        .address-box{
            background:#fff;
            border-radius:14px;
            border:1px solid #eee;
            padding:15px;
        }

        .address-item{
            cursor:pointer;
        }

        .address-item:hover{
            background:#fff0f7;
        }

        .product-thumb{
            width:80px;
            height:95px;
            object-fit:cover;
            border-radius:12px;
        }

        .product-name{
            font-size:15px;
            font-weight:700;
        }

        .product-meta{
            font-size:13px;
            color:#777;
        }

        .product-price{
            color:var(--pink);
            font-weight:700;
        }

        .summary-box{
            position:sticky;
            top:20px;
        }

        .selected-address{
            display:none;
            background:#fff0f7;
            border:1px solid #ffc9e1;
            padding:12px;
            border-radius:12px;
            margin-top:10px;
        }

        .nav-tabs .nav-link{
            color:#666;
            font-weight:600;
        }

        .nav-tabs .nav-link.active{
            color:var(--pink);
            font-weight:700;
            border-color:#dee2e6 #dee2e6 #fff;
        }

        #addressTabs{
    display:flex;
    width:100%;
    margin-bottom:0;
    border:1px solid #e9dce4;
    border-bottom:none;
    border-radius:16px 16px 0 0;
    overflow:hidden;
}

#addressTabs .nav-item{
    flex:1;
}

        #addressTabs .nav-link{
            width:100%;
            border:none;
            border-radius:0;
            background:#fff;
            color:#666;
            font-weight:600;
            padding:14px 10px;
            text-align:center;
        }

        #addressTabs .nav-link.active{
            background:#fdf0f7;
            color:#ff4fa3;
        }

        #addressList{
            border:1px solid #e9dce4;
            border-top:none;
            border-radius:0 0 16px 16px;
            max-height:300px;
            overflow-y:auto;
        }

        .quantity-box{
            display:flex;
            align-items:center;
            gap:10px;
            margin-top:8px;
        }

        .qty-btn{
            width:32px;
            height:32px;
            border:none;
            border-radius:50%;
            background:#ffe5f1;
            color:#ff4fa3;
            font-weight:700;
            cursor:pointer;
        }

        .qty-btn:hover{
            background:#ff4fa3;
            color:#fff;
        }

        .qty-value{
            min-width:25px;
            text-align:center;
            font-weight:700;
        }

        .voucher-card{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:12px;
            border:1px solid #eee;
            border-radius:12px;
            margin-bottom:10px;
            cursor:pointer;
            transition:.2s;
            background:#fff;
        }

        .voucher-card:hover{
            border-color:#ff4fa3;
            background:#fff0f7;
        }

        .voucher-code{
            font-weight:700;
            color:#ff4fa3;
        }

        .voucher-desc{
            font-size:13px;
            color:#666;
        }

        .voucher-right{
            font-size:12px;
            color:#ff4fa3;
            font-weight:600;
        }
        
    </style>

</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container py-4">

    <div class="row g-4">

        <!-- LEFT -->
        <div class="col-lg-8">

            <div class="card checkout-card">

                <div class="card-body">

                    <h3 class="section-title mb-4">
                        Thông tin giao hàng
                    </h3>

                    <form action="../controllers/OrderController.php"
                          method="POST"
                          id="checkoutForm">

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Họ và tên
                                </label>

                                <input type="text"
                                       name="receiver_name"
                                       class="form-control"
                                       required
                                       value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Số điện thoại
                                </label>

                                <input type="text"
                                       name="receiver_phone"
                                       class="form-control"
                                       required
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">

                            </div>

                            <div class="col-md-12 mb-3">

                                <label class="form-label">
                                    Email
                                </label>

                                <input type="email"
                                       name="receiver_email"
                                       class="form-control"
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">

                            </div>

                            <div class="col-md-12 mb-3">

                                <label class="form-label">
                                    Số nhà, tên đường
                                </label>

                                <input type="text"
                                       id="addressDetail"
                                       class="form-control"
                                       required
                                       placeholder="Ví dụ: Số 12, Đường Lê Lợi">

                            </div>
                            <div class="col-md-12">

    <label class="form-label">
        Chọn địa chỉ
    </label>

    <div class="address-box">

        <ul class="nav nav-tabs" id="addressTabs">

            <li class="nav-item">
                <button
                    type="button"
                    class="nav-link active"
                    id="provinceTab">
                    Tỉnh / Thành phố
                </button>
            </li>

            <li class="nav-item">
                <button
                    type="button"
                    class="nav-link"
                    id="districtTab">
                    Quận / Huyện
                </button>
            </li>

            <li class="nav-item">
                <button
                    type="button"
                    class="nav-link"
                    id="wardTab">
                    Phường / Xã
                </button>
            </li>

        </ul>

<div id="addressList" class="list-group">
</div>

        <div
            id="selectedAddress"
            class="selected-address">
        </div>

    </div>

    <input
        type="hidden"
        name="receiver_address"
        id="receiverAddress">

</div>

<div class="mt-4">

    <label class="form-label fw-bold">
        Phương thức giao hàng
    </label>

    <div class="p-4 rounded-4 border bg-light">

        <div class="form-check d-flex justify-content-between align-items-center">

            <div>

                <input
                    class="form-check-input"
                    type="radio"
                    checked
                    disabled>

                <label class="form-check-label ms-2">

                    <strong>Giao hàng tận nơi</strong>

                    <div class="text-muted">
                        Nhận hàng từ 2 - 5 ngày
                    </div>

                </label>

            </div>

            <strong
                class="text-danger"
                id="shippingPriceText">

                0₫

            </strong>

        </div>

    </div>

</div>
<div id="shippingMethodBox" style="display:none;">
<div class="mt-4">

    <label class="form-label fw-bold">
        Phương thức thanh toán
    </label>

    <div class="p-3 rounded-4 border bg-light">

        <div class="form-check">

            <input
                class="form-check-input"
                type="radio"
                name="payment_method"
                value="COD"
                checked>

            <label class="form-check-label">
                Thanh toán khi nhận hàng (COD)
            </label>

        </div>

    </div>

</div>
</div>

<div class="col-md-12 mt-3">

    <label class="form-label">
        Ghi chú
    </label>

    <textarea
        name="note"
        rows="3"
        class="form-control"></textarea>

</div>

</div>

</div>

</div>

</div>

<!-- RIGHT -->
<div class="col-lg-4">

    <div class="card checkout-card summary-box">

        <div class="card-body">

            <h4 class="section-title mb-3">
                Đơn hàng
            </h4>

            <?php foreach ($checkoutProducts as $item) { ?>

            <div class="d-flex gap-3 mb-3 pb-3 border-bottom">

                <img
                    src="<?php echo $item['image']; ?>"
                    class="product-thumb">

                <div class="flex-grow-1">

                    <div class="product-name">
                        <?php echo htmlspecialchars($item['name']); ?>
                    </div>

                    <div class="product-meta">

                        <?php if (!empty($item['size'])) { ?>
                            Size:
                            <?php echo htmlspecialchars($item['size']); ?>
                            <br>
                        <?php } ?>

                        <?php if (!empty($item['color'])) { ?>
                            Màu:
                            <?php echo htmlspecialchars($item['color']); ?>
                            <br>
                        <?php } ?>

                        SL:
                        <div class="quantity-box mt-2">
                            <button
                                type="button"
                                class="qty-btn decrease-btn"
                                data-cart-key="<?php echo $item['cart_key']; ?>">

                                -
                            </button>

                            <span
                                class="qty-value"
                                id="qty-<?php echo $item['cart_key']; ?>">

                                <?php echo $item['quantity']; ?>
                            </span>

                            <button
                                type="button"
                                class="qty-btn increase-btn"
                                data-cart-key="<?php echo $item['cart_key']; ?>">

                                +
                            </button>

                        </div>

                    </div>

                    <div
                        class="product-price mt-1"
                        id="subtotal-<?php echo $item['cart_key']; ?>"
                        data-value="<?php echo $item['subtotal']; ?>">
                        <?php echo number_format($item['subtotal']); ?>₫
                    </div>

                </div>

            </div>

            <?php } ?>

            <div class="mt-3 mb-3 p-3 border rounded-4 bg-light">
                <label class="form-label fw-bold">
                    Mã giảm giá
                </label>

                <!-- INPUT + APPLY -->
                <div class="input-group">
                    <input type="text"
                        id="voucherInput"
                        class="form-control"
                        placeholder="Nhập mã voucher">

                    <button type="button"
                            class="btn btn-pink"
                            onclick="applyVoucher()">
                        Áp dụng
                    </button>
                </div>

            <!-- TOGGLE LIST -->
            <div class="mt-2 d-flex justify-content-between align-items-center">

                <small class="text-muted">
                    Hoặc chọn voucher của bạn
                </small>

                <button type="button"
                        class="btn btn-sm btn-outline-danger"
                        onclick="toggleVoucherList()">
                    Xem voucher
                </button>
            </div>

    <!-- VOUCHER LIST -->
    <div id="voucherList"
         class="mt-3"
         style="display:none; max-height:220px; overflow:auto;">

        <?php if (!empty($vouchers)) { ?>
            <?php foreach ($vouchers as $v) { ?>
                <div class="voucher-card"
                    onclick="selectVoucher('<?php echo $v['code']; ?>')">

                    <div class="voucher-left">
                        <div class="voucher-code">
                            <?php echo $v['code']; ?>
                        </div>

                        <div class="voucher-desc">
                            <?php if ($v['discount_type'] == 'percent') { ?>
                                Giảm <?php echo $v['discount_value']; ?>%
                            <?php } else { ?>
                                Giảm <?php echo number_format($v['discount_value']); ?>₫
                            <?php } ?>
                        </div>
                    </div>

                    <div class="voucher-right">
                        <span>Chọn</span>
                    </div>

                </div>

            <?php } ?>

            <?php } else { ?>
                <div class="text-muted small">Không có voucher khả dụng</div>
            <?php } ?>

            </div>

            <!-- ACTIVE VOUCHER -->
            <div id="activeVoucher"
                class="mt-3 p-2 rounded-3 border"
                style="display:none; background:#fff0f7;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong id="activeVoucherCode"></strong>
                        <div class="small text-muted">Đang áp dụng</div>
                    </div>

                    <button class="btn btn-sm btn-outline-danger"
                            onclick="removeVoucher()">
                        Huỷ
                    </button>
                </div>
            </div>
        </div>

            <div class="d-flex justify-content-between mb-2">

                <span>Tiền hàng</span>

                <span id="productTotal">
                    <?php echo number_format($total); ?>₫
                </span>

            </div>

            <div class="d-flex justify-content-between mb-2">
                <span>Giảm giá</span>
                <span class="text-success" id="discountValue">
                    0₫
                </span>
            </div>

            <div class="d-flex justify-content-between mb-2">

                <span>Phí vận chuyển</span>

                <span id="shippingFee">
                    0₫
                </span>

            </div>

            <hr>

            <div class="d-flex justify-content-between fw-bold fs-5">

                <span>Tổng cộng</span>

                <span
                    class="text-danger"
                    id="finalTotal">

                    <?php echo number_format($total); ?>₫

                </span>

            </div>

            <button
                type="submit"
                class="btn btn-pink w-100 mt-3">

                ĐẶT HÀNG

            </button>

        </div>

    </div>

</div>

</div>

</form>

</div>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

let selectedProvinceCode = '';
let selectedDistrictCode = '';

let selectedProvinceName = '';
let selectedDistrictName = '';
let selectedWardName = '';

/* LOAD PROVINCE */
fetch('https://provinces.open-api.vn/api/p/')
.then(response => response.json())
.then(data => renderProvince(data));

function renderProvince(data)
{
    let list = document.getElementById('addressList');

    list.innerHTML = '';

    data.forEach(item => {

        list.innerHTML += `
            <button
                type="button"
                class="list-group-item list-group-item-action address-item"
                onclick="selectProvince(${item.code}, '${item.name}')">

                ${item.name}

            </button>
        `;
    });
}

function selectProvince(code,name){

    selectedProvinceCode = code;
    selectedProvinceName = name;

    selectedDistrictCode = '';
    selectedDistrictName = '';
    selectedWardName = '';

    document.getElementById("shippingFee").innerText = "0₫";
    document.getElementById("shippingPriceText").innerText = "0₫";

    // document.getElementById("provinceTab").innerText = name;

    fetch(
        `https://provinces.open-api.vn/api/p/${code}?depth=2`
    )
    .then(r => r.json())
    .then(d => {

        renderDistrict(d.districts);

        document
            .getElementById('districtTab')
            .click();
    });
    recalculateTotal();
}

function selectDistrict(code,name){

    selectedDistrictCode = code;
    selectedDistrictName = name;

    selectedWardName = '';

    document.getElementById("shippingFee").innerText = "0₫";
    document.getElementById("shippingPriceText").innerText = "0₫";

    // document.getElementById("districtTab").innerText = name;

    fetch(
        `https://provinces.open-api.vn/api/d/${code}?depth=2`
    )
    .then(r => r.json())
    .then(d => {

        renderWard(d.wards);

        document
            .getElementById('wardTab')
            .click();
    });
    recalculateTotal();
}

function renderWard(data)
{
    let list = document.getElementById('addressList');

    list.innerHTML = '';

    data.forEach(item => {

        list.innerHTML += `
            <button
                type="button"
                class="list-group-item list-group-item-action address-item"
                onclick="selectWard('${item.name}')">

                ${item.name}

            </button>
        `;
    });
}

function selectWard(name)
{
    selectedWardName = name;

    // document.getElementById('wardTab').innerText = name;
    document.getElementById('shippingMethodBox')
        .style.display = 'block';

    let detailAddress =
        document.getElementById('addressDetail').value.trim();

    let fullAddress = '';

    if (detailAddress !== '') {

        fullAddress =
            detailAddress + ', ' +
            selectedWardName + ', ' +
            selectedDistrictName + ', ' +
            selectedProvinceName;

    } else {

        fullAddress =
            selectedWardName + ', ' +
            selectedDistrictName + ', ' +
            selectedProvinceName;
    }

    document.getElementById('receiverAddress').value =
        fullAddress;

    document.getElementById('selectedAddress').style.display =
        'block';

    document.getElementById('selectedAddress').innerHTML = `
        <strong>Địa chỉ giao hàng:</strong>
        <br>
        ${fullAddress}
    `;

    calculateShipping();
}

function calculateShipping(){

    let shipping = 30000;

    if(
        selectedProvinceName=="Hà Nội" ||
        selectedProvinceName=="Thành phố Hồ Chí Minh"
    ){
        shipping=15000;
    }

    document.getElementById("shippingFee").innerText =
        shipping.toLocaleString()+"₫";

    document.getElementById("shippingPriceText").innerText =
        shipping.toLocaleString()+"₫";

    recalculateTotal();
}

function renderDistrict(data)
{
    let list =
        document.getElementById('addressList');

    list.innerHTML = '';

    data.forEach(item => {

        list.innerHTML += `
            <button
                type="button"
                class="list-group-item list-group-item-action address-item"
                onclick="selectDistrict(${item.code}, '${item.name}')">

                ${item.name}

            </button>
        `;
    });
}

/* FORM VALIDATE */
document
.getElementById('checkoutForm')
.addEventListener('submit', function(e){

    let name =
        document.querySelector('[name="receiver_name"]').value.trim();

    let phone =
        document.querySelector('[name="receiver_phone"]').value.trim();

    let address =
        document.getElementById('receiverAddress').value.trim();

    if (name === '') {

        alert('Vui lòng nhập họ tên');

        e.preventDefault();
        return;
    }

    if (phone === '') {

        alert('Vui lòng nhập số điện thoại');

        e.preventDefault();
        return;
    }

    if (address === '') {

        alert('Vui lòng chọn địa chỉ giao hàng');

        e.preventDefault();
        return;
    }
});

document
.getElementById("provinceTab")
.addEventListener("click", function(){

    fetch("https://provinces.open-api.vn/api/p/")
        .then(r => r.json())
        .then(renderProvince);
});

document
.getElementById("districtTab")
.addEventListener("click", function(){

    if(!selectedProvinceCode) return;

    fetch(
        `https://provinces.open-api.vn/api/p/${selectedProvinceCode}?depth=2`
    )
    .then(r => r.json())
    .then(d => renderDistrict(d.districts));
});

document
.getElementById("wardTab")
.addEventListener("click", function(){

    if(!selectedDistrictCode) return;

    fetch(
        `https://provinces.open-api.vn/api/d/${selectedDistrictCode}?depth=2`
    )
    .then(r => r.json())
    .then(d => renderWard(d.wards));
});

document
.querySelectorAll('#addressTabs .nav-link')
.forEach(tab => {

    tab.addEventListener('click', function(){

        document
        .querySelectorAll('#addressTabs .nav-link')
        .forEach(btn => btn.classList.remove('active'));

        this.classList.add('active');
    });

});

document.addEventListener('click', function(e) {

    let inc = e.target.closest('.increase-btn');
    let dec = e.target.closest('.decrease-btn');

    console.log("CLICK");

    if (inc) {
        console.log("Increase:", inc.dataset.cartKey);
        updateQuantity(inc.dataset.cartKey, 'increase');
    }

    if (dec) {
        console.log("Decrease:", dec.dataset.cartKey);
        updateQuantity(dec.dataset.cartKey, 'decrease');
    }
});

function updateQuantity(cartKey, action)
{
    console.log("Start fetch");
    fetch('../controllers/update_checkout_quantity.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'cart_key=' + cartKey + '&action=' + action
    })
    .then(r => r.json())
    .then(data => {

        if (!data.success) return;

        document.getElementById('qty-' + cartKey).innerText = data.quantity;

        document.getElementById('subtotal-' + cartKey).innerText =
            Number(data.subtotal).toLocaleString() + '₫';

        // QUAN TRỌNG
        document.getElementById('subtotal-' + cartKey)
            .dataset.value = data.subtotal;

        recalculateTotal();
    });
}

function recalculateTotal() {

    let total = 0;

    document.querySelectorAll("[id^='subtotal-']")
        .forEach(el => {

            total += parseInt(el.dataset.value || 0);

        });

    let shipping =
        parseInt(
            document
            .getElementById("shippingFee")
            .innerText.replace(/[^\d]/g,"")
        ) || 0;

    let final =
        total
        + shipping
        - discountAmount;

    if(final < 0)
        final = 0;

    document.getElementById("productTotal").innerText =
        total.toLocaleString()+"₫";

    document.getElementById("discountValue").innerText =
        "-"+discountAmount.toLocaleString()+"₫";

    document.getElementById("finalTotal").innerText =
        final.toLocaleString()+"₫";
}

let discountAmount = 0;
let appliedVoucher = null;

/* TOGGLE LIST */
function toggleVoucherList() {
    let el = document.getElementById('voucherList');
    el.style.display = (el.style.display === 'none') ? 'block' : 'none';
}

/* CLICK SELECT */
function selectVoucher(code) {
    document.getElementById('voucherInput').value = code;
    applyVoucher(); // auto apply luôn
}

/* APPLY VOUCHER */
function applyVoucher() {

    let code = document.getElementById('voucherInput').value.trim();
    if (!code) return;

    let total = calculateCurrentTotal(); // OK sau khi thêm hàm

    fetch('../controllers/VoucherController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=apply&code=${code}&total=${total}`
    })
    .then(r => r.json())
    .then(data => {

        if (!data.success) {
            alert(data.message);
            return;
        }

        discountAmount = data.discount;
        appliedVoucher = data.voucher;

        document.getElementById('activeVoucher').style.display = 'block';
        document.getElementById('activeVoucherCode').innerText = data.voucher.code;

        recalculateTotal();
    });
}

/* REMOVE VOUCHER */
function removeVoucher() {

    discountAmount = 0;
    appliedVoucher = null;

    document.getElementById('voucherInput').value = '';
    document.getElementById('activeVoucher').style.display = 'none';

    document.getElementById('discountValue').innerText = '0₫';

    recalculateTotal();
}

function calculateCurrentTotal()
{
    let total = 0;

    document.querySelectorAll("[id^='subtotal-']")
        .forEach(el => {
            total += parseFloat(el.dataset.value || 0);
        });

    return total;
}

</script>

</body>
</html>