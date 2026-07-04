<?php

session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

$cartKey = $_POST['cart_key'] ?? '';
$action = $_POST['action'] ?? '';

/* Xác định đang checkout từ Buy Now hay Cart */

if (isset($_SESSION['buy_now'][$cartKey])) {
    $item = &$_SESSION['buy_now'][$cartKey];
} elseif (isset($_SESSION['cart'][$cartKey])) {
    $item = &$_SESSION['cart'][$cartKey];
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Item not found',
    ]);
    exit;
}

/* Update quantity */

if ($action === 'increase') {
    ++$item['quantity'];
}

if (
    $action === 'decrease'
    && $item['quantity'] > 1
) {
    --$item['quantity'];
}

$productId = $item['product_id'];
$quantity = $item['quantity'];

$size = $item['size'] ?? '';
$color = $item['color'] ?? '';

// lấy giá DB cho chuẩn
$stmt = $conn->prepare("
SELECT price
FROM product_variants
WHERE product_id = ?
AND (size = ? OR (? = '' AND size IS NULL))
AND (color = ? OR (? = '' AND color IS NULL))
LIMIT 1
");

$stmt->execute([
    $productId,
    $size,
    $size,
    $color,
    $color,
]);
$price = $stmt->fetchColumn();

if ($price === false) {
    $stmt = $conn->prepare('
        SELECT price
        FROM products
        WHERE product_id = ?
    ');
    $stmt->execute([$productId]);
    $price = $stmt->fetchColumn();
}

$price = (float) $price;

echo json_encode([
    'success' => true,
    'quantity' => $quantity,
    'price' => $price,
    'subtotal' => $price * $quantity,
]);
