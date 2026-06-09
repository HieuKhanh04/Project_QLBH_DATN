<?php
session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

$keyword = trim($_GET['keyword'] ?? '');

if ($keyword !== '') {
    $products = $productModel->searchProducts($keyword);
} else {
    $products = $productModel->getAllProducts();
}

/* CART COUNT */
$count = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item) && isset($item['quantity'])) {
            $count += (int) $item['quantity'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>HAN STORE</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

:root{
    --pink:#ff4fa3;
    --pink-hover:#e63d8d;
}

/* BASE */
body{
    background:#fff7fb;
    font-family:'Quicksand',sans-serif;
    font-weight:600;
}

/* SECTION TITLE */
.section-title{
    color:var(--pink);
    font-size:2rem;
    font-weight:700;
}

/* HERO */
.hero-banner{
    min-height:500px;
    border-radius:24px;
    background:
        linear-gradient(rgba(0,0,0,.35), rgba(0,0,0,.35)),
        url('https://images.unsplash.com/photo-1441986300917-64674bd600d8');
    background-size:cover;
    background-position:center;
    display:flex;
    align-items:center;
    padding:4rem;
    color:#fff;
}

.hero-banner h1{
    font-size:4rem;
    font-weight:700;
}

.hero-banner p{
    font-size:1.25rem;
}

/* =========================
   BUTTON SYSTEM (SYNC PRODUCTS)
========================= */

.btn-pink,
.btn-outline-pink{
    height:44px;
    padding:0 16px;
    font-size:15px;
    font-weight:700;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:.25s ease;
    letter-spacing:.2px;
}

/* BUY NOW */
.btn-pink{
    background:var(--pink);
    border:2px solid var(--pink);
    color:#fff;
    box-shadow:0 2px 6px rgba(255,79,163,.15);
}

.btn-pink:hover{
    background:var(--pink-hover);
    border-color:var(--pink-hover);
    transform:translateY(-2px);
    box-shadow:0 6px 16px rgba(255,79,163,.25);
}

/* ADD CART */
.btn-outline-pink{
    background:#fff;
    border:2px solid var(--pink);
    color:var(--pink);
    box-shadow:0 2px 6px rgba(255,79,163,.08);
}

.btn-outline-pink:hover{
    background:var(--pink);
    color:#fff;
    transform:translateY(-2px);
    box-shadow:0 6px 16px rgba(255,79,163,.25);
}

/* PRODUCT */
.product-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    transition:.3s;
}

.product-card:hover{
    transform:translateY(-6px);
    box-shadow:0 10px 25px rgba(0,0,0,.12);
}

.product-card img{
    width:100%;
    height:260px;          
    object-fit:cover;     
    object-position:center;
    display:block;
}

.product-name{
    font-weight:700;
    min-height:48px;
}

.product-price{
    color:var(--pink);
    font-weight:700;
    font-size:18px;
}

.hero-btn{
    width:auto;
    display:inline-flex;
    padding:14px 28px;
    font-size:18px;
    border-radius:14px;
    font-weight:700;
    box-shadow:0 6px 18px rgba(255,79,163,.25);
}

.hero-btn:hover{
    transform:translateY(-2px);
}

.modal-custom{
    border-radius:18px;
    border:none;
    box-shadow:0 20px 60px rgba(0,0,0,0.15);
    overflow:hidden;
}

.modal-img-box{
    background:#fff;
    border-radius:14px;
    padding:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
}

.modal-img-box img{
    max-height:320px;
    object-fit:cover;
    border-radius:12px;
}

.modal-header{
    padding:18px 22px;
    background:#fff;
}

.btn-close{
    background-color: transparent !important;
    opacity: 1 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

#modalDetailLink{
    color:#555;
    transition:.2s;
}

#modalDetailLink:hover{
    color:#ff4fa3;
}

/* Ẩn spinner input number */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type=number] {
    -moz-appearance: textfield;
}

.size-btn.active,
.color-btn.active{
    background:#ff4fa3 !important;
    color:#fff !important;
    border-color:#ff4fa3 !important;
}

</style>
</head>

<body>

<?php include 'layout/header.php'; ?>

<!-- HERO -->
<div class="container py-4">

    <div class="hero-banner">

        <div>
            <h1>Summer Pink</h1>
            <p class="mb-4">
                Bộ sưu tập thời trang mới với tone trắng hồng hiện đại
            </p>

            <a href="collections.php" class="btn btn-pink btn-lg hero-btn">
                Mua ngay
            </a>
        </div>

    </div>

</div>

<!-- PRODUCTS -->
<section class="container py-5">

    <div class="text-center mb-5">
        <h2 class="section-title">Sản phẩm nổi bật</h2>
    </div>

    <div class="row g-4">

        <?php foreach ($products as $p) { ?>
        <?php
            $variants = $productModel->getVariantsByProduct($p['product_id']);

            $sizes = [];
            $colors = [];

            foreach ($variants as $v) {
                if (!empty($v['size'])) {
                    $sizes[] = $v['size'];
                }

                if (!empty($v['color'])) {
                    $colors[] = $v['color'];
                }
            }

            $sizes = array_unique($sizes);
            $colors = array_unique($colors);
            ?>

        <div class="col-12 col-sm-6 col-lg-3">

            <div class="card product-card h-100 shadow-sm">

                <a href="product_detail.php?id=<?php echo $p['product_id']; ?>"
                   class="text-decoration-none text-dark">

                    <img src="https://picsum.photos/500/600?random=<?php echo $p['product_id']; ?>">

                    <div class="card-body">
                        <div class="product-name">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </div>

                        <div class="product-price mt-2">
                            <?php echo number_format($p['price']); ?>₫
                        </div>
                    </div>

                </a>

                <!-- BUTTONS SYNC PRODUCTS -->
                <div class="card-footer bg-white border-0">

                    <div class="row g-2">

                        <div class="col-6">
                            <button
                                type="button"
                                class="btn btn-outline-pink w-100 add-cart-btn"
                                onclick="openCartModal(this)"
                                data-id="<?php echo $p['product_id']; ?>"
                                data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                data-price="<?php echo $p['price']; ?>"
                                data-image="https://picsum.photos/500/600?random=<?php echo $p['product_id']; ?>"
                                data-has-variant="<?php echo $productModel->hasVariant($p['product_id']) ? 1 : 0; ?>"
                                data-sizes='<?php echo json_encode(array_values($sizes)); ?>'
                                data-colors='<?php echo json_encode(array_values($colors)); ?>'
                            >
                                Thêm giỏ
                            </button>
                        </div>

                        <div class="col-6">
                            <a href="product_detail.php?id=<?php echo $p['product_id']; ?>"
                               class="btn btn-pink w-100">
                                Mua ngay
                            </a>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <?php } ?>

    </div>

</section>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="modal fade" id="cartModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">

    <div class="modal-content modal-custom">

      <!-- HEADER -->
      <div class="modal-header border-0">
        <button type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body pt-0">
        <div class="row g-4 align-items-start">

          <!-- IMAGE -->
          <div class="col-md-5 text-center">
            <div class="modal-img-box">
              <img id="modalImage" src="" class="img-fluid">
            </div>
          </div>

          <!-- INFO -->
          <div class="col-md-7">

            <h5 id="modalName" class="fw-bold mb-2"></h5>

            <div id="modalPrice" class="text-danger fw-bold fs-5 mb-2"></div>

            <div id="modalStatus" class="mb-3"></div>

            <!-- SIZE -->
            <div class="mb-2" id="sizeBox" style="display:none;">
              <div class="fw-semibold mb-1">Size</div>
              <div id="modalSize"></div>
            </div>

            <!-- COLOR -->
            <div class="mb-3" id="colorBox" style="display:none;">
              <div class="fw-semibold mb-1">Màu sắc</div>
              <div id="modalColor"></div>
            </div>

            <!-- QTY -->
                <div class="mb-3">
                    <div class="fw-semibold mb-1">Số lượng</div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <button type="button"
                                class="btn btn-outline-secondary"
                                onclick="decreaseQty()">
                            -
                        </button>

                        <input type="number"
                            id="modalQty"
                            value="1"
                            min="1"
                            class="form-control text-center"
                            style="width:80px;">

                        <button type="button"
                                class="btn btn-outline-secondary"
                                onclick="increaseQty()">
                            +
                        </button>
                    </div>
                </div>
                <button class="btn btn-pink w-100 py-2"
                        onclick="addToCartModal()">
                Thêm vào giỏ hàng
                </button>

                <div class="text-center mt-3">
                    <a id="modalDetailLink"
                    href="#"
                    class="text-decoration-underline text-dark fw-semibold">
                        Xem chi tiết sản phẩm >>
                    </a>
                </div>
            </div>
        </div>
      </div>

    </div>

  </div>
</div>

<script>

let currentProductId = 0;
let hasVariant = false;
let selectedSize = '';
let selectedColor = '';

function openCartModal(btn){

    currentProductId = btn.dataset.id;
    hasVariant = btn.dataset.hasVariant == "1";

    document.getElementById('modalName').innerText = btn.dataset.name;
    document.getElementById('modalPrice').innerText =
        Number(btn.dataset.price).toLocaleString() + '₫';

    document.getElementById('modalImage').src = btn.dataset.image;

    document.getElementById('modalStatus').innerHTML =
        '<span class="text-success">Còn hàng</span>';
    
    document.getElementById('modalDetailLink').href =
        'product_detail.php?id=' + btn.dataset.id;

    /* RESET */
    document.getElementById('modalQty').value = 1;

    /* VARIANT LOGIC */
    if(hasVariant){

        document.getElementById('sizeBox').style.display = 'block';
        document.getElementById('colorBox').style.display = 'block';

        const sizes = JSON.parse(btn.dataset.sizes || '[]');
        const colors = JSON.parse(btn.dataset.colors || '[]');

        let sizeHtml = '';
        let colorHtml = '';

        sizes.forEach(size => {
            sizeHtml += `
                <button type="button"
                        class="btn btn-outline-secondary btn-sm me-1 size-btn">
                    ${size}
                </button>
            `;
        });

        colors.forEach(color => {
            colorHtml += `
                <button type="button"
                        class="btn btn-outline-secondary btn-sm me-1 color-btn">
                    ${color}
                </button>
            `;
        });

        document.getElementById('modalSize').innerHTML = sizeHtml;
        document.getElementById('modalColor').innerHTML = colorHtml;

    } else {
        document.getElementById('sizeBox').style.display = 'none';
        document.getElementById('colorBox').style.display = 'none';
    }

    new bootstrap.Modal(document.getElementById('cartModal')).show();
}

/* ADD TO CART */
function addToCartModal(){

    let qty = document.getElementById('modalQty').value;

    // CHECK BIẾN THỂ
    if (hasVariant) {
        const sizes = document.querySelectorAll('.size-btn');
        const colors = document.querySelectorAll('.color-btn');
        if (sizes.length > 0 && !selectedSize) {
            alert('Vui lòng chọn Size');
            return;
        }
        if (colors.length > 0 && !selectedColor) {
            alert('Vui lòng chọn Màu sắc');
            return;
        }
    }

    let url = '../controllers/CartController.php?action=add'
            + '&id=' + currentProductId
            + '&quantity=' + qty
            + '&ajax=1';

    // thêm biến thể nếu có
    if (hasVariant) {
        url += '&size=' + encodeURIComponent(selectedSize)
             + '&color=' + encodeURIComponent(selectedColor);
    }

    fetch(url)
    .then(res => res.json())
    .then(data => {

        if (data.success) {

            alert('Đã thêm vào giỏ hàng');

            // ✅ CẬP NHẬT SỐ LƯỢNG GIỎ NGAY
            updateCartCount(data.count);

            bootstrap.Modal.getInstance(
                document.getElementById('cartModal')
            ).hide();

            // reset selection
            selectedSize = '';
            selectedColor = '';
        }
    });
}

function increaseQty(){
    let input = document.getElementById('modalQty');
    input.value = parseInt(input.value || 1) + 1;
}

function decreaseQty(){
    let input = document.getElementById('modalQty');
    let val = parseInt(input.value || 1);

    if (val > 1) {
        input.value = val - 1;
    }
}

document.addEventListener('click', function (e) {

    // SIZE
    if (e.target.classList.contains('size-btn')) {

        document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');

        selectedSize = e.target.innerText.trim();
    }

    // COLOR
    if (e.target.classList.contains('color-btn')) {

        document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');

        selectedColor = e.target.innerText.trim();
    }
});

function updateCartCount(count){

    let badge = document.querySelector('.cart-count');

    if (!badge) {

        // nếu chưa có badge → tạo mới
        let cartIcon = document.querySelector('a[href="cart.php"]');

        badge = document.createElement('span');
        badge.className = 'cart-count';

        cartIcon.appendChild(badge);
    }

    badge.innerText = count;
}
</script>
</body>
</html>