<?php

session_start();

$id = (int) $_POST['id'];
$qty = (int) $_POST['qty'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'][$id] =
($_SESSION['cart'][$id] ?? 0) + $qty;

echo json_encode([
    'success' => true,
    'cartCount' => array_sum($_SESSION['cart']),
]);
