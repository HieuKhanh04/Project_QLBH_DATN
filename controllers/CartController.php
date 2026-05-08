<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$redirect = $_GET['redirect'] ?? ''; // 🔥 thêm dòng này

switch ($action) {

    case 'add':
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        } else {
            $_SESSION['cart'][$id] = 1;
        }
        break;

    case 'increase':
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        }
        break;

    case 'decrease':
        if (isset($_SESSION['cart'][$id]) && $_SESSION['cart'][$id] > 1) {
            $_SESSION['cart'][$id]--;
        }
        break;

    case 'remove':
        unset($_SESSION['cart'][$id]);
        break;
}

/* 🔥 PHẦN QUAN TRỌNG NHẤT */
if ($redirect == 'cart') {
    header("Location: ../views/cart.php");
} else {
    header("Location: ../controllers/HomeController.php");
}

exit();