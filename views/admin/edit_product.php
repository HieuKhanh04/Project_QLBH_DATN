<?php
require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

$id = $_GET['id'] ?? 0;

/* PRODUCT */
$stmt = $conn->prepare('
SELECT p.*, c.name AS category_name
FROM products p
LEFT JOIN categories c 
ON p.category_id = c.category_id
WHERE p.product_id=?
');
$stmt->execute([$id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    exit('Không tìm thấy sản phẩm');
}

if (isset($_GET['delete_variant'])) {
    $variantId = $_GET['delete_variant'];

    // Lấy thông tin biến thể trước khi xóa
    $stmt = $conn->prepare('
        SELECT size, color
        FROM product_variants
        WHERE id=?
    ');

    $stmt->execute([$variantId]);
    $variant = $stmt->fetch(PDO::FETCH_ASSOC);

    // Xóa biến thể
    $stmt = $conn->prepare('
        DELETE FROM product_variants
        WHERE id=?
    ');

    if ($stmt->execute([$variantId])) {
        writeLog(
            $conn,
            'DELETE',
            'Biến thể sản phẩm',
            'Xóa biến thể '.$variant['size'].' - '.$variant['color'].' của sản phẩm '.$id.' - '.$product['name']
        );
    }

    header('Location: edit_product.php?id='.$id);
    exit;
}

if (isset($_GET['main_image'])) {
    $imageId = $_GET['main_image'];

    $stmt = $conn->prepare('
        UPDATE product_images
        SET is_main = 0
        WHERE product_id = ?
    ');

    $stmt->execute([$id]);

    $stmt = $conn->prepare('
        UPDATE product_images
        SET is_main = 1
        WHERE id = ?
    ');

    $stmt->execute([$imageId]);

    writeLog(
        $conn,
        'UPDATE',
        'Sản phẩm',
        'Đổi ảnh đại diện sản phẩm '.$id.' - '.$product['name']
    );

    header('Location: edit_product.php?id='.$id);
    exit;
}

if (isset($_GET['delete_image'])) {
    $imageId = $_GET['delete_image'];

    $stmt = $conn->prepare('
        SELECT image_url
        FROM product_images
        WHERE id=?
    ');

    $stmt->execute([$imageId]);

    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        $file = '../../'.$image['image_url'];

        if (file_exists($file)) {
            unlink($file);
        }

        $stmt = $conn->prepare('
        DELETE FROM product_images
        WHERE id=?
    ');

        if ($stmt->execute([$imageId])) {
            writeLog(
                $conn,
                'DELETE',
                'Hình ảnh sản phẩm',
                'Xóa ảnh #'.$imageId.' của sản phẩm #'.$id.' - '.$product['name']
            );
        }
    }

    header('Location: edit_product.php?id='.$id);
    exit;
}

/* VARIANTS */
$stmt = $conn->prepare('
SELECT *
FROM product_variants
WHERE product_id=?
');
$stmt->execute([$id]);

$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* IMAGES */
$stmt = $conn->prepare('
SELECT *
FROM product_images
WHERE product_id=?
');
$stmt->execute([$id]);

$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query('
SELECT category_id,name
FROM categories
ORDER BY name
');

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare('
    UPDATE products
    SET
        name=?,
        description=?,
        category_id=?,
        status=?
    WHERE product_id=?
    ');

    if ($stmt->execute([
        $name,
        $description,
        $category_id,
        $status,
        $id,
    ])) {
        writeLog(
            $conn,
            'UPDATE',
            'Sản phẩm',
            'Cập nhật thông tin sản phẩm '.$id.' - '.$name
        );
    }

    if (isset($_POST['variant_id'])) {
        foreach ($_POST['variant_id'] as $key => $variantId) {
            // 1. LẤY ẢNH CŨ
            $stmtOld = $conn->prepare('SELECT image FROM product_variants WHERE id=?');
            $stmtOld->execute([$variantId]);
            $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

            $variantImage = $old['image'] ?? ''; // GIỮ ẢNH CŨ

            // 2. NẾU UPLOAD ẢNH MỚI
            if (isset($_FILES['new_image']['name'][$key]) && $_FILES['new_image']['name'][$key] != '') {
                $fileName = time().'_'.basename($_FILES['new_image']['name'][$key]);
                $tmpName = $_FILES['new_image']['tmp_name'][$key];
                $target = '../../uploads/products/'.$fileName;

                if (move_uploaded_file($tmpName, $target)) {
                    $variantImage = 'uploads/products/'.$fileName;
                }
            }

            // 3. UPDATE
            $stmt = $conn->prepare('
                UPDATE product_variants
                SET size=?, color=?, price=?, discount_price=?, quantity=?, image=?
                WHERE id=?
            ');

            $stmt->execute([
                $_POST['size'][$key],
                $_POST['color'][$key],
                $_POST['variant_price'][$key],
                $_POST['discount_price'][$key],
                $_POST['quantity'][$key],
                $variantImage,
                $variantId,
            ]);

            writeLog(
                $conn,
                'UPDATE',
                'Sản phẩm',
                'Cập nhật biến thể #'.$variantId.' của sản phẩm #'.$id.' - '.$name
            );
        }
    }
    // echo '<pre>';
    // print_r($_POST);
    // exit;
    if (isset($_POST['new_size'])) {
        foreach ($_POST['new_price'] as $i => $price) {
            if ($price == '') {
                continue;
            }
            // ===== UPLOAD IMAGE VARIANT =====
            $variantImage = null;
            /* 1. UPLOAD ẢNH MỚI (ưu tiên cao nhất) */
            if (!empty($_FILES['new_image']['name'][$i])) {
                $fileName = time().'_'.basename($_FILES['new_image']['name'][$i]);
                $tmpName = $_FILES['new_image']['tmp_name'][$i];
                $target = '../../uploads/products/'.$fileName;

                if (move_uploaded_file($tmpName, $target)) {
                    $variantImage = 'uploads/products/'.$fileName;
                }
            }

            /* 2. NẾU KHÔNG UPLOAD → DÙNG ẢNH CŨ */
            if (!$variantImage && !empty($_POST['new_image_ref'][$i])) {
                $variantImage = $_POST['new_image_ref'][$i];
            }

            // ===== INSERT VARIANT =====
            $stmt = $conn->prepare('
                INSERT INTO product_variants(
                    product_id,
                    size,
                    color,
                    price,
                    discount_price,
                    quantity,
                    image
                )
                VALUES(?,?,?,?,?,?,?)
            ');

            $stmt->execute([
                $id,
                $_POST['new_size'][$i] ?: null,
                $_POST['new_color'][$i] ?: null,
                $price,
                $_POST['new_discount'][$i] ?: null,
                $_POST['new_quantity'][$i] ?: 0,
                $variantImage,
            ]);
        }
    }

    if (!empty($_FILES['product_images']['name'][0])) {
        foreach ($_FILES['product_images']['tmp_name'] as $key => $tmpName) {
            $fileName =
                time().'_'.
                basename($_FILES['product_images']['name'][$key]);

            $target =
                '../../uploads/products/'.
                $fileName;

            move_uploaded_file(
                $tmpName,
                $target
            );

            $stmt = $conn->prepare('
                INSERT INTO product_images(
                    product_id,
                    image_url
                )
                VALUES (?,?)
            ');

            $stmt->execute([
                $id,
                'uploads/products/'.$fileName,
            ]);
        }
        $count = count($_FILES['product_images']['name']);
        writeLog(
            $conn,
            'CREATE',
            'Sản phẩm',
            'Thêm '.$count.' hình ảnh cho sản phẩm #'.$id.' - '.$name
        );
    }

    header('Location: edit_product.php?id='.$id);
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Sửa sản phẩm</title>
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>

body{
    background:#fff5f9;
    font-family:Arial;
}

.container{
    width:1100px;
    margin:30px auto;
}

.box{
    background:#fff;
    border-radius:20px;
    padding:25px;
    margin-bottom:20px;
}

.title{
    font-size:22px;
    font-weight:bold;
    margin-bottom:20px;
}

.form-group{
    margin-bottom:15px;
}

label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
}

input,
textarea,
select{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:10px;
}

.variant-table{
	width:100%;
	border-collapse:collapse;
	table-layout:fixed;
	margin-top:15px;
}

.variant-table th,
.variant-table td{
	border:1px solid #eee;
	padding:12px;
	vertical-align:middle;
}

.variant-table input{
	width:100%;
	box-sizing:border-box;
	padding:10px;
	border:1px solid #ddd;
	border-radius:10px;
}

.image-grid{
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}

.image-grid img{
    width:140px;
    height:140px;
    object-fit:cover;
    border-radius:12px;
}

.save-btn{
    display:inline-block;
    margin-bottom:20px;
    background:#ff4fa3;
    color:white;
    border-radius:12px;
    text-decoration:none;
    border:none;
    padding:14px 22px;
    border-radius:14px;
    cursor:pointer;
    margin-left:10px;
}

.variant-add{
    display:grid;
    grid-template-columns:repeat(5,1fr);
    gap:10px;
    margin-top:15px;
}

.image-item{
    width:140px;
}

.image-item a{
    display:block;
    text-align:center;
    margin-top:8px;
    color:red;
    text-decoration:none;
}

.action-area{
    margin-top:20px;
    display:flex;
    justify-content:flex-start;
    gap:10px;
}

.back-btn{
    display:inline-block;
    margin-bottom:20px;
    background:#ff4fa3;
    color:white;
    border-radius:12px;
    text-decoration:none;
    border:none;
    padding:14px 22px;
    border-radius:14px;
    cursor:pointer;
    margin-left:10px;
}

.main-image{
    background:#ff4fa3;
    color:white;
    padding:5px;
    border-radius:8px;
    text-align:center;
    margin-top:5px;
}

input:focus,
textarea:focus,
select:focus{
    border-color:#ff4fa3;
    outline:none;
}

.title{
    color:#ff4fa3;
}

.delete-variant,
.remove-btn{
	width:40px;
	height:40px;
	background:#ff4d6d;
	color:#fff;
	border:none;
	border-radius:10px;
	display:flex;
	align-items:center;
	justify-content:center;
	cursor:pointer;
	text-decoration:none;
	margin:auto;
}

.add-variant-btn{
    margin-top:15px;
    padding:12px 20px;
    background:#ff4fa3;
    color:white;
    border:none;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
}

.variant-table input{
    width:100%;
}

.variant-preview img{
    width:60px;
    height:60px;
    object-fit:cover;
    border-radius:8px;
    margin-top:5px;
    border:1px solid #ddd;
}

.variant-preview-list{
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}

.variant-card{
    width:180px;
    border:1px solid #eee;
    border-radius:12px;
    padding:10px;
    background:#fff;
}

.variant-card img{
    width:100%;
    height:120px;
    object-fit:cover;
    border-radius:10px;
    margin-bottom:8px;
}

.variant-card .name{
    font-weight:bold;
    font-size:14px;
}

.variant-card .price{
    color:#ff4fa3;
    font-weight:bold;
}

</style>
</head>

<body>
<form method="POST" enctype="multipart/form-data">

<div class="container">
    <div class="box">
        <div class="title">
            Thông tin sản phẩm
        </div>

        <div class="form-group">
            <label>Tên sản phẩm</label>

            <input
            type="text"
            name="name"
            value="<?php echo htmlspecialchars($product['name']); ?>">
        </div>

        <div class="form-group">
            <label>Danh mục</label>

            <select name="category_id">
                <?php foreach ($categories as $cat) { ?>
                <option
                value="<?php echo $cat['category_id']; ?>"
                <?php echo ($cat['category_id'] == $product['category_id']) ? 'selected' : ''; ?>
                >
                <?php echo $cat['name']; ?>
                </option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label>Mô tả</label>

            <textarea
            name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Trạng thái</label>

            <select name="status">
                <option value="1" <?php if ($product['status'] == 1) {
                    echo 'selected';
                } ?>>
                    Hiển thị
                </option>

                <option value="0" <?php if ($product['status'] == 0) {
                    echo 'selected';
                } ?>>
                    Ẩn
                </option>
            </select>
        </div>
    </div>

    <div class="box">
        <div class="title">
            Biến thể sản phẩm
        </div>

        <!-- DANH SÁCH BIẾN THỂ -->
        <table class="variant-table">
            <thead>
                <tr>
                    <th>Size</th>
                    <th>Màu</th>
                    <th>Giá</th>
                    <th>Giảm giá</th>
                    <th>Số lượng</th>
                    <th>Ảnh</th>
                    <th>Thao tác</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($variants as $v) { ?>
                    <tr>
                        <td>
                            <input
                                type="hidden"
                                name="variant_id[]"
                                value="<?php echo $v['id']; ?>">

                            <input
                                type="text"
                                name="size[]"
                                value="<?php echo htmlspecialchars($v['size']); ?>">
                        </td>

                        <td>
                            <input
                                type="text"
                                name="color[]"
                                value="<?php echo htmlspecialchars($v['color']); ?>">
                        </td>

                        <td>
                            <input
                                type="number"
                                name="variant_price[]"
                                value="<?php echo $v['price']; ?>">
                        </td>

                        <td>
                            <input
                                type="number"
                                name="discount_price[]"
                                value="<?php echo $v['discount_price']; ?>">
                        </td>

                        <td>
                            <input
                                type="number"
                                name="quantity[]"
                                value="<?php echo $v['quantity']; ?>">
                        </td>

                        <td>
                            <?php if (!empty($v['image'])) { ?>
                                <?php if (!empty($v['image'])) { ?>
                                    <img src="../../<?php echo $v['image']; ?>" width="50">
                                <?php } ?>

                                <input
                                    type="file"
                                    name="new_image[]"
                                    accept="image/*"
                                    onchange="previewVariantImage(this);renderVariantPreview()">

                                <div class="variant-preview"></div>
                            <?php } else { ?>
                                <span>Không có ảnh</span>
                            <?php } ?>
                        </td>

                        <td>
                            <a
                            href="?id=<?php echo $id; ?>&delete_variant=<?php echo $v['id']; ?>"
                            onclick="return confirm('Xóa biến thể này?')"
                            class="delete-variant">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="box">
            <div class="title">Preview biến thể</div>
            <div id="variant-preview-list" class="variant-preview-list"></div>
        </div>

        <!-- THÊM BIẾN THỂ -->
        <h3 style="margin-top:25px;">
            Thêm biến thể
        </h3>

        <table class="variant-table">
            <thead>
                <tr>
                    <th>Size</th>
                    <th>Màu</th>
                    <th>Giá</th>
                    <th>Giảm giá</th>
                    <th>Số lượng</th>
                    <th>Ảnh</th>
                    <th>Thêm</th>
                </tr>
            </thead>

            <tbody id="newVariant">
                <tr>
                    <td>
                        <input
                        type="text"
                        name="new_size[]"
                        placeholder="Size">
                    </td>

                    <td>
                        <input
                        type="text"
                        name="new_color[]"
                        placeholder="Màu">
                    </td>

                    <td>
                        <input
                        type="number"
                        name="new_price[]"
                        placeholder="Giá">
                    </td>

                    <td>
                        <input
                        type="number"
                        name="new_discount[]"
                        placeholder="Giảm">
                    </td>

                    <td>
                        <input
                        type="number"
                        name="new_quantity[]"
                        placeholder="Số lượng">
                    </td>

                    <td>
                        <input type="file"
                            name="new_image[]"
                            accept="image/*"
                            onchange="previewVariantImage(this)">
                        <div style="margin-top:5px;">
                            <select name="new_image_ref[]">
                                <option value="">-- Dùng ảnh cũ --</option>

                                <?php foreach ($variants as $v) { ?>
                                    <?php if (!empty($v['image'])) { ?>
                                        <option value="<?php echo $v['image']; ?>">
                                            <?php echo trim(($v['size'] ?? '').' - '.($v['color'] ?? ''), ' -'); ?>
                                        </option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="variant-preview"></div>
                    </td>

                    <td style="text-align:center;">
                        <button
                            type="button"
                            class="add-variant-btn"
                            onclick="addVariant()">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="box">
        <div class="title">
            Hình ảnh sản phẩm
        </div>

        <div class="image-grid">
            <?php foreach ($images as $img) { ?>
                <div class="image-item">
                    <img src="../../<?php echo $img['image_url']; ?>">

                    <?php if ($img['is_main'] == 1) { ?>
                        <div class="main-image">
                            Ảnh chính
                        </div>
                    <?php } ?>

                    <a
                    href="?id=<?php echo $id; ?>&main_image=<?php echo $img['id']; ?>">
                        Đặt ảnh chính
                    </a>

                    <a
                    href="?id=<?php echo $id; ?>&delete_image=<?php echo $img['id']; ?>"
                    onclick="return confirm('Xóa ảnh?')">
                        Xóa
                    </a>
                </div>
            <?php } ?>
        </div>

        <div class="form-group">
            <label>Thêm ảnh sản phẩm</label>
            <input
                type="file"
                name="product_images[]"
                multiple>
        </div>

    </div>

    <div class="action-area">
        <a href="products.php" class="back-btn">
            <i class="fa fa-arrow-left"></i>
            Quay lại
        </a>

        <button type="submit" class="save-btn">
            Cập nhật sản phẩm
        </button>
    </div>
</div>

</form>

<script>
    function addVariant(){
        let html = `
        <tr>
            <td><input type="text" name="new_size[]" placeholder="Size"></td>
            <td><input type="text" name="new_color[]" placeholder="Màu"></td>
            <td><input type="number" name="new_price[]" placeholder="Giá"></td>
            <td><input type="number" name="new_discount[]" placeholder="Giảm"></td>
            <td><input type="number" name="new_quantity[]" placeholder="SL"></td>
            <td>
                <input
                    type="file"
                    name="new_image[]"
                    accept="image/*"
                    onchange="previewVariantImage(this);renderVariantPreview();">

                <div style="margin-top:5px;">
                    <select
                        name="new_image_ref[]"
                        onchange="renderVariantPreview()">

                        <option value="">-- Dùng ảnh cũ --</option>

                        <?php foreach ($variants as $v) { ?>
                            <?php if (!empty($v['image'])) { ?>
                                <option value="<?php echo $v['image']; ?>">
                                    <?php echo trim(($v['size'] ?? '').' - '.($v['color'] ?? ''), ' -'); ?>
                                </option>
                            <?php } ?>
                        <?php } ?>

                    </select>
                </div>

                <div class="variant-preview"></div>
            </td>
        </tr>
        `;

        document
        .getElementById('newVariant')
        .insertAdjacentHTML('beforeend',html);

        renderVariantPreview();
    }


    function removeVariant(btn){
        btn.closest('tr').remove();
        renderVariantPreview();
    }
</script>

<script>
function previewVariantImage(input){
    const file = input.files[0];
    if(!file) return;

    const reader = new FileReader();

    reader.onload = function(e){
        let preview = input.parentElement.querySelector('.variant-preview');

        if(!preview){
            preview = document.createElement('div');
            preview.className = 'variant-preview';
            input.parentElement.appendChild(preview);
        }

        preview.innerHTML = `<img src="${e.target.result}">`;
        renderVariantPreview();
    };

    reader.readAsDataURL(file);
}

function renderVariantPreview(){
    const rows = document.querySelectorAll('.variant-table tbody tr');

    let html = '';

    rows.forEach(row => {
        const size =
        row.querySelector('input[name="size[]"]')?.value ||
        row.querySelector('input[name="new_size[]"]')?.value ||
        '';

        const color =
        row.querySelector('input[name="color[]"]')?.value ||
        row.querySelector('input[name="new_color[]"]')?.value ||
        '';

        const price =
        row.querySelector('input[name="variant_price[]"]')?.value ||
        row.querySelector('input[name="new_price[]"]')?.value ||
        '';

        const discount =
        row.querySelector('input[name="discount_price[]"]')?.value ||
        row.querySelector('input[name="new_discount[]"]')?.value ||
        '';

        let img = '';

        // =========================
        // 1. ẢNH VARIANT CŨ (DB)
        // =========================
        const oldImg = row.querySelector('td img');
        if (oldImg) {
            img = oldImg.src;
        }

        // =========================
        // 2. ẢNH UPLOAD MỚI
        // =========================
        const fileInput = row.querySelector('input[type="file"]');
        if (fileInput && fileInput.files && fileInput.files[0]) {
            img = URL.createObjectURL(fileInput.files[0]);
        }

        // =========================
        // 3. ẢNH CHỌN TỪ DROPDOWN (QUAN TRỌNG)
        // =========================
        const select = row.querySelector('select[name="new_image_ref[]"]');
        if (select && select.value && !fileInput?.files?.length) {
            img = '../../' + select.value;
        }

        html += `
            <div class="variant-card">
                ${img
                    ? `<img src="${img}">`
                    : `<div style="height:120px;background:#f5f5f5;border-radius:10px;"></div>`
                }
                <div class="name">${size} - ${color}</div>
                <div class="price">${price}₫</div>
                ${discount ? `<small>Sale: ${discount}₫</small>` : ''}
            </div>
        `;
    });

    document.getElementById('variant-preview-list').innerHTML = html;
}

document.addEventListener('input', function(e){
    if(e.target.closest('.variant-table')){
        renderVariantPreview();
    }
});

document.addEventListener('change', function(e){
    if(e.target.closest('.variant-table')){
        renderVariantPreview();
    }
});

window.addEventListener('DOMContentLoaded', function(){
    renderVariantPreview();
});
</script>

</body>
</html>