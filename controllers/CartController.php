<?php

session_start();

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* ÉP KIỂU ID */
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$action = $_GET['action'] ?? '';
$redirect = $_GET['redirect'] ?? '';

switch ($action) {
    case 'add':
        if ($id > 0) {
            if (isset($_SESSION['cart'][$id])) {
                ++$_SESSION['cart'][$id];
            } else {
                $_SESSION['cart'][$id] = 1;
            }
        }
        break;

    case 'increase':
        if ($id > 0 && isset($_SESSION['cart'][$id])) {
            ++$_SESSION['cart'][$id];
        }
        break;

    case 'decrease':
        if ($id > 0 && isset($_SESSION['cart'][$id])) {
            --$_SESSION['cart'][$id];

            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
        break;

    case 'remove':
        if ($id > 0) {
            unset($_SESSION['cart'][$id]);
        }
        break;
}

/* ===== AJAX ===== */
if (isset($_GET['ajax'])) {
    echo json_encode([
        'success' => true,
        'count' => array_sum($_SESSION['cart']),
        'cart' => $_SESSION['cart'],
    ]);
    exit;
}

/* ===== REDIRECT ===== */

if ($redirect === 'cart') {
    header('Location: ../views/cart.php');
    exit;
}

if ($redirect === 'promotion') {
    header('Location: ../views/promotion.php');
    exit;
}

if ($redirect === 'products') {
    header('Location: ../views/products.php');
    exit;
}

if ($redirect === 'detail') {
    $idProduct = $_GET['id_product'] ?? 0;
    header('Location: ../views/product_detail.php?id='.(int) $idProduct);
    exit;
}

if ($redirect === 'checkout') {
    $ids = $_GET['ids'] ?? '';
    header('Location: ../views/checkout.php?ids='.$ids);
    exit;
}

header('Location: ../views/index.php');
exit;
