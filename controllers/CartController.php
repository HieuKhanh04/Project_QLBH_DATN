<?php

session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$redirect = $_GET['redirect'] ?? '';

switch ($action) {
    case 'add':
        if (isset($_SESSION['cart'][$id])) {
            ++$_SESSION['cart'][$id];
        } else {
            $_SESSION['cart'][$id] = 1;
        }

        break;

    case 'increase':
        if (isset($_SESSION['cart'][$id])) {
            ++$_SESSION['cart'][$id];
        }

        break;

    case 'decrease':
        if (isset($_SESSION['cart'][$id])
            && $_SESSION['cart'][$id] > 1) {
            --$_SESSION['cart'][$id];
        }

        break;

    case 'remove':
        unset($_SESSION['cart'][$id]);

        break;
}

/* ===== AJAX ===== */
if (isset($_GET['ajax'])) {
    echo json_encode([
        'success' => true,
        'count' => array_sum($_SESSION['cart']),
    ]);

    exit;
}

/* ===== REDIRECT ===== */

if ($redirect == 'cart') {
    header('Location: ../views/cart.php');
} elseif ($redirect == 'detail') {
    header(
        'Location: ../views/product_detail.php?id='
        .$_GET['id_product']
    );
} elseif ($redirect == 'checkout') {
    $ids = $_GET['ids'] ?? '';

    header(
        'Location: ../views/checkout.php?ids='
        .$ids
    );
} else {
    header('Location: ../views/index.php');
}

exit;
