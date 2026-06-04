<?php
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

/* PRODUCT */
$stmt = $conn->prepare('
SELECT *
FROM products
WHERE product_id=?
');
$stmt->execute([$id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (isset($_GET['delete_variant'])) {
    $variantId = $_GET['delete_variant'];

    $stmt = $conn->prepare('
    DELETE FROM product_variants
    WHERE id=?
    ');

    $stmt->execute([$variantId]);

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
        $file =
            '../../'.
            $image['image_url'];

        if (file_exists($file)) {
            unlink($file);
        }

        $stmt = $conn->prepare('
            DELETE FROM product_images
            WHERE id=?
        ');

        $stmt->execute([$imageId]);
    }

    header('Location: edit_product.php?id='.$id);
    exit;
}

if (!$product) {
    exit('Không tìm thấy sản phẩm');
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
        price=?,
        description=?,
        category_id=?,
        status=?
    WHERE product_id=?
    ');

    $stmt->execute([
        $name,
        $price,
        $description,
        $category_id,
        $status,
        $id,
    ]);

    if (isset($_POST['variant_id'])) {
        foreach ($_POST['variant_id'] as $key => $variantId) {
            $stmt = $conn->prepare('
            UPDATE product_variants
            SET
                size=?,
                color=?,
                price=?,
                discount_price=?,
                quantity=?
            WHERE id=?
            ');

            $stmt->execute([
                $_POST['size'][$key],
                $_POST['color'][$key],
                $_POST['variant_price'][$key],
                $_POST['discount_price'][$key],
                $_POST['quantity'][$key],
                $variantId,
            ]);
        }
    }

    if (!empty($_POST['new_size'])) {
        $stmt = $conn->prepare('
        INSERT INTO product_variants(
            product_id,
            size,
            color,
            price,
            discount_price,
            quantity
        )
        VALUES(?,?,?,?,?,?)
        ');

        $stmt->execute([
            $id,
            $_POST['new_size'],
            $_POST['new_color'],
            $_POST['new_price'],
            $_POST['new_discount'],
            $_POST['new_quantity'],
        ]);
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
}

.variant-table th,
.variant-table td{
    border:1px solid #eee;
    padding:10px;
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
    background:#ff4fa3;
    color:white;
    border:none;
    padding:15px 30px;
    border-radius:12px;
    cursor:pointer;
    font-weight:bold;
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
    display:flex;
    gap:10px;
}

.back-btn{
    padding:15px 25px;
    background:#f1f1f1;
    color:#333;
    text-decoration:none;
    border-radius:12px;
    font-weight:bold;
}

.main-image{
    background:#ff4fa3;
    color:white;
    padding:5px;
    border-radius:8px;
    text-align:center;
    margin-top:5px;
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
            <label>Giá gốc</label>

            <input
            type="number"
            name="price"
            value="<?php echo $product['price']; ?>">
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

        <table class="variant-table">

            <tr>
                <th>Size</th>
                <th>Màu</th>
                <th>Giá</th>
                <th>Giảm giá</th>
                <th>Tồn kho</th>
                <th>Xóa</th>
            </tr>

            <?php foreach ($variants as $v) { ?>

            <tr>

                <input type="hidden"
                       name="variant_id[]"
                       value="<?php echo $v['id']; ?>">

                <td>
                    <input type="text"
                           name="size[]"
                           value="<?php echo $v['size']; ?>">
                </td>

                <td>
                    <input type="text"
                           name="color[]"
                           value="<?php echo $v['color']; ?>">
                </td>

                <td>
                    <input type="number"
                           name="variant_price[]"
                           value="<?php echo $v['price']; ?>">
                </td>

                <td>
                    <input type="number"
                           name="discount_price[]"
                           value="<?php echo $v['discount_price']; ?>">
                </td>

                <td>
                    <input type="number"
                           name="quantity[]"
                           value="<?php echo $v['quantity']; ?>">
                </td>
                <td>
                    <a
                        href="?id=<?php echo $id; ?>&delete_variant=<?php echo $v['id']; ?>"
                        onclick="return confirm('Xóa biến thể?')">
                        🗑️
                    </a>
                </td>
            </tr>
            <?php } ?>
        </table>

        <h3>Thêm biến thể</h3>
        <div class="variant-add">
            <input type="text" name="new_size" placeholder="Size">
            <input type="text" name="new_color" placeholder="Màu">
            <input type="number" name="new_price" placeholder="Giá">
            <input type="number" name="new_discount" placeholder="Giảm giá">
            <input type="number" name="new_quantity" placeholder="Số lượng">
        </div>
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
            ← Quay lại
        </a>

        <button class="save-btn">
            Cập nhật sản phẩm
        </button>
    </div>

</div>

</form>

</body>
</html>