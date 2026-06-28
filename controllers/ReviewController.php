<?php

session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../views/login.php');
    exit;
}

$user = $_SESSION['user'];

$productId = (int) ($_POST['product_id'] ?? 0);
$orderId = (int) ($_POST['order_id'] ?? 0);

$size = trim($_POST['size'] ?? '');
$color = trim($_POST['color'] ?? '');

$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if (
    $productId <= 0
    || $orderId <= 0
    || $rating < 1
    || $rating > 5
) {
    exit('Dữ liệu không hợp lệ.');
}

/* ===================================
   KIỂM TRA ĐƠN HÀNG
=================================== */

$stmt = $conn->prepare('
SELECT status
FROM orders
WHERE order_id = ?
AND user_id = ?
');

$stmt->execute([
    $orderId,
    $user['user_id'],
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    exit('Không tìm thấy đơn hàng.');
}

if ($order['status'] != 'delivered') {
    exit('Đơn hàng chưa được giao thành công.');
}

/* ===================================
   KIỂM TRA SẢN PHẨM CÓ THUỘC ĐƠN HÀNG
=================================== */

$stmt = $conn->prepare('
SELECT id
FROM order_details
WHERE order_id = ?
AND product_id = ?
');

$stmt->execute([
    $orderId,
    $productId,
]);

if (!$stmt->fetch()) {
    exit('Sản phẩm không thuộc đơn hàng.');
}

/* ===================================
   KIỂM TRA ĐÃ ĐÁNH GIÁ CHƯA
=================================== */

$stmt = $conn->prepare('
SELECT review_id
FROM product_reviews
WHERE product_id = ?
AND order_id = ?
AND user_id = ?
');

$stmt->execute([
    $productId,
    $orderId,
    $user['user_id'],
]);

if ($stmt->fetch()) {
    $_SESSION['review_error'] =
        'Bạn đã đánh giá sản phẩm này rồi!';

    header(
        'Location: ../views/order_detail.php?id='.$orderId
    );

    exit;
}

/* ===================================
   ẨN TÊN KHÁCH HÀNG
=================================== */

$name = trim($user['name']);

$length = mb_strlen($name);

if ($length <= 2) {
    $customerName = $name;
} else {
    $customerName =
        str_repeat('*', $length - 2).
        mb_substr($name, -2);
}

/* ===================================
   LƯU ĐÁNH GIÁ
=================================== */

try {
    $stmt = $conn->prepare('
    INSERT INTO product_reviews
    (
        product_id,
        order_id,
        user_id,
        customer_name,
        size,
        color,
        rating,
        comment
    )
    VALUES
    (
        ?,?,?,?,?,?,?,?
    )
    ');

    $stmt->execute([
        $productId,
        $orderId,
        $user['user_id'],
        $customerName,
        $size,
        $color,
        $rating,
        $comment,
    ]);

    $_SESSION['review_success'] =
        'Đánh giá sản phẩm thành công!';

    header(
        'Location: ../views/order_detail.php?id='.$orderId
    );

    exit;
} catch (PDOException $e) {
    $_SESSION['review_error'] =
        'Có lỗi xảy ra khi lưu đánh giá!';

    header(
        'Location: ../views/order_detail.php?id='.$orderId
    );

    exit;
}
