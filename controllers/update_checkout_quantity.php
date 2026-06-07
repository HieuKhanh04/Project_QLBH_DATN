<?php

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
