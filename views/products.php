<?php
session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

/* CATEGORY */
$stmt = $conn->query('SELECT * FROM categories');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* FILTER */
$category_id = $_GET['category'] ?? 0;

if ($category_id > 0) {
    $products = $productModel->getProductsByCategory($category_id);
} else {
    $products = $productModel->getAllProducts();
}

$activeCategory = (int) $category_id;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Sản phẩm</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@600;700&display=swap" rel="stylesheet">

<style>
:root{
    --pink:#ff4fa3;
    --pink-hover:#e63d8d;
}

body{
    font-family:'Quicksand', sans-serif;
    background:#fff7fb;
}

/* TITLE */
.title{
    font-size:32px;
    font-weight:700;
    color:var(--pink);
    text-align:center;
    margin:20px 0;
}

/* CATEGORY */
.category-card{
    border:none;
    border-radius:14px;
    text-decoration:none;
    color:#333;
    background:#fff;
    transition:.25s;
    display:block;
}

.category-card:hover{
    transform:translateY(-4px);
    box-shadow:0 8px 20px rgba(0,0,0,.1);
}

.category-card.active{
    border:2px solid var(--pink);
}

.category-card img{
    width:100%;
    height:110px;
    object-fit:cover;
    border-radius:10px;
}

.category-name{
    margin-top:8px;
    font-weight:600;
}

/* PRODUCT CARD */
.product-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    transition:.3s;
    background:#fff;
}

.product-card:hover{
    transform:translateY(-6px);
    box-shadow:0 10px 25px rgba(0,0,0,.12);
}

.product-card img{
    width:100%;
    height:280px;
    object-fit:cover;
    object-position:center;
    display:block;
}

.product-name{
    font-weight:700;
    min-height:45px;
    color:#333;
}

.product-price{
    color:var(--pink);
    font-weight:700;
    font-size:18px;
}

/* =========================
   BUTTON SYSTEM (GIỐNG INDEX 100%)
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
    transition:.25s;
}

/* MUA NGAY */
.btn-pink{
    background:var(--pink);
    border:2px solid var(--pink);
    color:#fff;
}

.btn-pink:hover{
    background:var(--pink-hover);
    border-color:var(--pink-hover);
    color:#fff;
}

/* THÊM GIỎ */
.btn-outline-pink{
    background:#fff;
    border:2px solid var(--pink);
    color:var(--pink);
}

.btn-outline-pink:hover{
    background:var(--pink);
    color:#fff;
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

<div class="container py-3">

    <h2 class="title">Danh mục sản phẩm</h2>

    <!-- CATEGORY -->
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3 col-lg-2">
            <a href="products.php"
               class="category-card p-2 text-center <?php echo $activeCategory == 0 ? 'active' : ''; ?>">
                <img src="https://picsum.photos/200?random=1">
                <div class="category-name">Tất cả</div>
            </a>
        </div>

        <?php foreach ($categories as $c) { ?>
        <div class="col-6 col-md-3 col-lg-2">
            <a href="products.php?category=<?php echo $c['category_id']; ?>"
               class="category-card p-2 text-center <?php echo $activeCategory == $c['category_id'] ? 'active' : ''; ?>">
                <img src="https://picsum.photos/200?random=<?php echo $c['category_id']; ?>">
                <div class="category-name">
                    <?php echo htmlspecialchars($c['name']); ?>
                </div>
            </a>
        </div>
        <?php } ?>

    </div>

    <!-- PRODUCTS -->
    <div class="row g-4">

        <?php foreach ($products as $p) { ?>
        <?php $priceRange = $productModel->getPriceRange($p['product_id']); ?>
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
        <div class="col-12 col-md-6 col-lg-3">

            <div class="card product-card shadow-sm">

                <a href="product_detail.php?id=<?php echo $p['product_id']; ?>"
                   class="text-decoration-none text-dark">

                    <img src="<?php echo !empty($p['image_url']) ? '../'.ltrim($p['image_url'], '/') : '../assets/no-image.png'; ?>">

                    <div class="card-body">
                        <div class="product-name">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </div>

                        <div class="product-price mt-2">
                            <?php if ($priceRange['min_price'] == $priceRange['max_price']) { ?>
                                <?php echo number_format($priceRange['min_price']); ?>₫
                            <?php } else { ?>
                                <?php echo number_format($priceRange['min_price']); ?>₫
                                - 
                                <?php echo number_format($priceRange['max_price']); ?>₫
                            <?php } ?>
                        </div>
                    </div>

                </a>

                <!-- BUTTONS (GIỐNG INDEX 100%) -->
                <div class="card-footer bg-white border-0">
                    <div class="row g-2">
                        <div class="col-6">
                            <button
                                type="button"
                                class="btn btn-outline-pink w-100 add-cart-btn"
                                onclick="openCartModal(this)"
                                data-id="<?php echo $p['product_id']; ?>"
                                data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                data-price="<?php echo $priceRange['min_price']; ?>"
                                data-image="<?php echo !empty($p['image_url']) ? '../'.ltrim($p['image_url'], '/') : '../assets/no-image.png'; ?>"
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

</div>

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
    let stock = parseInt(btn.dataset.stock);
    if(stock > 0){
        document.getElementById('modalStatus').innerHTML =
            '<span class="text-success">Còn hàng (' + stock + ')</span>';
    }else{
        document.getElementById('modalStatus').innerHTML =
            '<span class="text-danger">Hết hàng</span>';
    }
    
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