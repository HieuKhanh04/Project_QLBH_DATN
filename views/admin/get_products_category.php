<?php
require_once '../../config/database.php';
require_once '../../includes/activity_log.php';

$category_id = $_GET['category_id'] ?? 0;

$stmt = $conn->prepare('
    SELECT *
    FROM categories
    WHERE category_id=?
');

$stmt->execute([$category_id]);

$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    exit('Danh mục không tồn tại');
}

writeLog(
    $conn,
    'VIEW',
    'Danh mục',
    'Xem danh sách sản phẩm của danh mục #'.$category_id.' - '.$category['name']
);

$stmt = $conn->prepare('
    SELECT
        p.*,
        pi.image_url
    FROM products p
    LEFT JOIN product_images pi
        ON p.product_id = pi.product_id
        AND pi.is_main = 1
    WHERE p.category_id = ?
    ORDER BY p.product_id DESC
');

$stmt->execute([$category_id]);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sản phẩm danh mục</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial;
            background:#fff5f9;
        }

        .main{
            margin-left:260px;
            padding:30px;
        }

        .box{
            background:white;
            padding:25px;
            border-radius:22px;
        }

        h1{
            margin-bottom:10px;
        }

        p{
            color:#777;
            margin-bottom:25px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th{
            text-align:left;
            color:#999;
            padding:15px;
        }

        td{
            padding:15px;
            border-top:1px solid #eee;
        }

        .badge{
            background:#e4fff0;
            color:#23b26d;
            padding:6px 12px;
            border-radius:20px;
        }

        .back-btn{
            display:inline-block;
            margin-bottom:20px;
            background:#ff4fa3;
            color:white;
            padding:12px 18px;
            border-radius:12px;
            text-decoration:none;
        }

        img{
            width:60px;
            height:60px;
            object-fit:cover;
            border-radius:10px;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
    <a href="categories.php" class="back-btn">
        <i class="fa fa-arrow-left"></i>
        Quay lại
    </a>

    <div class="box">
        <h1>
            Danh mục <?php echo $category['name']; ?>
        </h1>

        <p>
            Danh sách sản phẩm thuộc danh mục
        </p>

        <table>
            <tr>
                <th>Ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Trạng thái</th>
            </tr>

            <?php foreach ($products as $p) { ?>

            <tr>
                <td>
                    <?php if (!empty($p['image_url'])) { ?>
                    <img src="../../<?php echo $p['image_url']; ?>">
                    <?php } else { ?>
                    Không có ảnh
                    <?php } ?>
                </td>

                <td>
                    <strong>
                        <?php echo $p['name']; ?>
                    </strong>
                </td>

                <td>
                    <?php echo number_format($p['price']); ?> VNĐ
                </td>

                <td>

                    <span class="badge">

                        <?php echo isset($p['status']) && $p['status'] == 1
                            ? 'Hoạt động'
                            : 'Ẩn';
                ?>

                    </span>

                </td>

            </tr>

            <?php } ?>

            <?php if (count($products) == 0) { ?>

            <tr>

                <td colspan="4" style="text-align:center">
                    Chưa có sản phẩm trong danh mục này
                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>
</html>