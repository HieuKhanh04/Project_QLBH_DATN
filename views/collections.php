<?php
session_start();

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

/* COLLECTIONS */
$stmt = $conn->query('SELECT * FROM collections');
$collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* FILTER */
$collection_id = $_GET['collection'] ?? 0;

if ($collection_id > 0) {
    $stmt = $conn->prepare('SELECT * FROM products WHERE collection_id = ?');
    $stmt->execute([$collection_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = $productModel->getAllProducts();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Bộ sưu tập</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">

<style>

:root{
    --pink:#ff4fa3;
    --pink-hover:#e63d8d;
}

/* BASE */
body{
    background:#fff7fb;
    font-family:'Quicksand', sans-serif;
    font-weight:600;
}

/* TITLE */
.page-title{
    text-align:center;
    font-size:32px;
    font-weight:700;
    color:var(--pink);
    margin:25px 0;
}

/* COLLECTION CARD */
.collection-card{
    border:none;
    border-radius:16px;
    background:#fff;
    transition:.25s ease;
    text-decoration:none;
    color:#333;
}

.collection-card:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 20px rgba(0,0,0,.12);
}

.collection-card img{
    width:100%;
    height:120px;
    object-fit:cover;
    border-radius:12px;
}

.collection-name{
    margin-top:10px;
    font-weight:600;
}

/* PRODUCT CARD */
.product-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    background:#fff;
    transition:.3s ease;
}

.product-card:hover{
    transform:translateY(-6px);
    box-shadow:0 12px 25px rgba(0,0,0,.12);
}

.product-img{
    width:100%;
    height:260px;
}

.product-img img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* TEXT */
.product-name{
    font-weight:700;
    color:#333;
    min-height:48px;
}

.product-price{
    color:var(--pink);
    font-weight:700;
    font-size:18px;
}

/* =========================
   BUTTON SYSTEM (SYNC INDEX)
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

#modalDetailLink{
    font: size 16px;
    font-weight:500;
    color:#666;
}

#modalDetailLink:hover{
    color:var(--pink);
}

/* BUTTON đồng bộ index */
.btn-pink,
.btn-outline-pink{
    height:44px;
    font-size:15px;
    font-weight:700;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
}

/* Ẩn spinner mặc định */
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.qty-input {
    -moz-appearance: textfield;
    width: 70px;
    font-weight: 500;
    font-size: 14px;
    border-radius: 10px;
    text-align: center;
}

/* nút + - */
.qty-btn{
    width:38px;
    height:38px;
    border-radius:10px;
    font-weight:700;
    font-size:16px;
    display:flex;
    align-items:center;
    justify-content:center;
}

</style>

</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container py-4">

    <h2 class="page-title">Bộ sưu tập</h2>

    <!-- COLLECTION LIST -->
    <div class="row g-3 mb-5">

        <div class="col-6 col-md-3 col-lg-2">
            <a href="collections.php"
               class="collection-card d-block text-center p-2 shadow-sm">

                <img src="https://picsum.photos/200/200?random=1">
                <div class="collection-name">Tất cả</div>

            </a>
        </div>

        <?php foreach ($collections as $c) { ?>

        <div class="col-6 col-md-3 col-lg-2">

            <a href="collections.php?collection=<?php echo $c['collection_id']; ?>"
               class="collection-card d-block text-center p-2 shadow-sm">

                <img src="<?php echo $c['image']; ?>">
                <div class="collection-name">
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

                    <div class="product-img">
                        <img src="<?php echo !empty($p['image_url']) ? '../'.ltrim($p['image_url'], '/') : '../assets/no-image.png'; ?>">
                    </div>

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

                <!-- BUTTONS SYNC INDEX -->
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

<script>
function addToCart(id){

    fetch('../controllers/CartController.php?action=add&id=' + id + '&quantity=1&ajax=1')
    .then(res => res.json())
    .then(data => {

        if (data.success) {
            alert('Đã thêm vào giỏ hàng');

            // nếu có header cart count
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.innerText = data.count;
                cartCount.style.display = 'flex';
            }
        }
    });
}

function buyNow(id){
    fetch(
        '../controllers/CartController.php?action=buy_now'
        + '&id=' + id
        + '&quantity=1'
    )
    .then(res => res.json())
    .then(data => {
        if(data.success){
            window.location.href = 'checkout.php';
        }else{
            alert(data.message || 'Có lỗi xảy ra');
        }
    });
}

</script>
<div class="modal fade" id="cartModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">

    <div class="modal-content p-3">

      <div class="modal-header border-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-4 align-items-start">

          <!-- LEFT: IMAGE -->
          <div class="col-md-5 text-center">
            <img id="modalImage"
                 class="img-fluid rounded"
                 style="max-height:320px; object-fit:cover;">
          </div>

          <!-- RIGHT: INFO -->
          <div class="col-md-7">

            <h4 id="modalName" class="mb-2"></h4>
            <div id="modalPrice" class="mb-3"></div>

            <!-- SIZE -->
            <div id="sizeBox" class="mb-2" style="display:none;">
              <div class="mb-1">Size</div>
              <div id="modalSize" class="mt-1"></div>
            </div>

            <!-- COLOR -->
            <div id="colorBox" class="mb-3" style="display:none;">
              <div class="mb-1">Màu sắc</div>
              <div id="modalColor" class="mt-1"></div>
            </div>

            <!-- QUANTITY -->
            <div class="mb-3">
              <div class="mb-1">Số lượng</div>
              <div class="d-flex align-items-center gap-2">
                <button type="button"
                        class="btn btn-outline-secondary qty-btn"
                        onclick="decreaseQty()">
                    -
                </button>

                <input type="number"
                    id="modalQty"
                    value="1"
                    min="1"
                    class="form-control text-center qty-input">

                <button type="button"
                        class="btn btn-outline-secondary qty-btn"
                        onclick="increaseQty()">
                    +
                </button>

            </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="d-grid gap-2">

                <button class="btn btn-pink w-100"
                        style="height:44px;font-size:15px;font-weight:700;border-radius:12px;"
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

    document.getElementById('modalDetailLink').href =
        'product_detail.php?id=' + currentProductId;

    document.getElementById('modalQty').value = 1;

    // RESET
    selectedSize = '';
    selectedColor = '';

    if (hasVariant) {

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

/* chọn size */
function selectSize(btn){
    document.querySelectorAll('#modalSize .variant-btn')
        .forEach(b => b.classList.remove('active'));

    btn.classList.add('active');
    selectedSize = btn.innerText.trim();
}

function selectColor(btn){
    document.querySelectorAll('#modalColor .variant-btn')
        .forEach(b => b.classList.remove('active'));

    btn.classList.add('active');
    selectedColor = btn.innerText.trim();
}

/* ADD CART */
function addToCartModal(){

    let qty = document.getElementById('modalQty').value;

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

    if (hasVariant) {
        url += '&size=' + encodeURIComponent(selectedSize)
             + '&color=' + encodeURIComponent(selectedColor);
    }

    fetch(url)
    .then(res => res.json())
    .then(data => {

        if (data.success) {
            alert('Đã thêm vào giỏ hàng');
            bootstrap.Modal.getInstance(document.getElementById('cartModal')).hide();

            // update cart UI nếu có
            const cart = document.querySelector('.cart-count');
            if (cart) cart.innerText = data.count;
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
</script>

</body>
</html>