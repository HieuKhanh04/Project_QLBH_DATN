<!-- <?php

session_start();

require_once '../config/database.php';

$cartKey = $_POST['cart_key'] ?? '';
$action = $_POST['action'] ?? '';

if (!isset($_SESSION['cart'][$cartKey])) {
    exit;
}

if ($action === 'increase') {
    ++$_SESSION['cart'][$cartKey]['quantity'];
}

if (
    $action === 'decrease'
    && $_SESSION['cart'][$cartKey]['quantity'] > 1
) {
    --$_SESSION['cart'][$cartKey]['quantity'];
}

$quantity = $_SESSION['cart'][$cartKey]['quantity'];

$productId =
    $_SESSION['cart'][$cartKey]['product_id'];

$stmt = $conn->prepare('
    SELECT price
    FROM products
    WHERE product_id = ?
');

$stmt->execute([$productId]);

$price = (float) $stmt->fetchColumn();

$subtotal = $price * $quantity;

echo json_encode([
    'success' => true,
    'quantity' => $quantity,
    'subtotal' => $subtotal,
]);
?> -->

<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

$cartKey = $_POST['cart_key'] ?? '';
$action = $_POST['action'] ?? '';

if (!isset($_SESSION['cart'][$cartKey])) {
    echo json_encode([
        'success' => false,
        'message' => 'Item not found',
    ]);
    exit;
}

// update quantity
if ($action === 'increase') {
    ++$_SESSION['cart'][$cartKey]['quantity'];
}

if ($action === 'decrease') {
    --$_SESSION['cart'][$cartKey]['quantity'];

    if ($_SESSION['cart'][$cartKey]['quantity'] < 1) {
        unset($_SESSION['cart'][$cartKey]);

        echo json_encode([
            'success' => true,
            'quantity' => 0,
            'subtotal' => 0,
        ]);
        exit;
    }
}

$item = $_SESSION['cart'][$cartKey];

$productId = $item['product_id'];
$quantity = $item['quantity'];

// lấy giá DB cho chuẩn
$stmt = $conn->prepare('SELECT price FROM products WHERE product_id = ?');
$stmt->execute([$productId]);
$price = (float) $stmt->fetchColumn();

$_SESSION['cart'][$cartKey]['price'] = $price;

echo json_encode([
    'success' => true,
    'quantity' => $quantity,
    'subtotal' => $price * $quantity,
]);
