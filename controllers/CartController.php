<?php

session_start();
require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

/* INIT CART */
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* COMMON FUNCTIONS */
function calculateCartCount($cart)
{
    $count = 0;
    foreach ($cart as $item) {
        $count += (int) ($item['quantity'] ?? 0);
    }

    return $count;
}

/* AJAX ADD (POST ONLY - CLEAN) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_add_cart'])) {
    header('Content-Type: application/json');

    $productId = (int) ($_POST['product_id'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

    $price = $productModel->getFinalPrice($productId, $size, $color);

    if ($price < 0) {
        $price = 0;
    }

    if ($productId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Sản phẩm không hợp lệ',
        ]);
        exit;
    }

    // tạo key variant
    $cartKey = $productId.'_'.$size.'_'.$color;

    if (isset($_SESSION['cart'][$cartKey])) {
        $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cartKey] = [
            'product_id' => $productId,
            'size' => $size,
            'color' => $color,
            'price' => $price,   // THÊM QUAN TRỌNG
            'quantity' => $quantity,
        ];
    }

    echo json_encode([
        'success' => true,
        'count' => calculateCartCount($_SESSION['cart']),
        'total' => array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $_SESSION['cart'])),
    ]);
    exit;
}

/* =========================
   GET PARAMS (LEGACY SUPPORT)
========================= */
$id = (int) ($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
$redirect = $_GET['redirect'] ?? '';

$size = trim($_GET['size'] ?? '');
$color = trim($_GET['color'] ?? '');
$quantity = max(1, (int) ($_GET['quantity'] ?? 1));

/* =========================
   ACTION HANDLER
========================= */
switch ($action) {
    /* -------------------------
       ADD TO CART (GET LEGACY)
    ------------------------- */
    case 'add':
        $size = trim($_GET['size'] ?? '');
        $color = trim($_GET['color'] ?? '');
        $quantity = max(1, (int) ($_GET['quantity'] ?? 1));

        $price = $productModel->getFinalPrice($id, $size, $color);

        if ($id > 0) {
            $cartKey = $id.'_'.$size.'_'.$color;

            if (isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$cartKey] = [
                    'product_id' => $id,
                    'size' => $size,
                    'color' => $color,
                    'price' => $price,   // 🔥 THÊM
                    'quantity' => $quantity,
                ];
            }
        }
        break;

        /* -------------------------
           BUY NOW
        ------------------------- */
    case 'buy_now':
        $size = trim($_GET['size'] ?? '');
        $color = trim($_GET['color'] ?? '');
        $quantity = max(1, (int) ($_GET['quantity'] ?? 1));

        $price = $productModel->getFinalPrice($id, $size, $color);

        $key = $id.'_'.$size.'_'.$color;

        // $_SESSION['buy_now'] = [
        //     $key => [
        //         'product_id' => $id,
        //         'size' => $size,
        //         'color' => $color,
        //         'quantity' => $quantity,
        //     ],
        // ];
        // Xóa sản phẩm Mua ngay cũ
        $_SESSION['buy_now'] = [];

        // Lưu sản phẩm mới
        $_SESSION['buy_now'] = [
            $key => [
                'product_id' => $id,
                'size' => $size,
                'color' => $color,
                'price' => $price,
                'quantity' => $quantity,
            ],
        ];

        header('Location: ../views/checkout.php');
        exit;

        /* -------------------------
           INCREASE
        ------------------------- */
    case 'increase':
        $key = $_GET['key'] ?? '';

        if (isset($_SESSION['cart'][$key])) {
            ++$_SESSION['cart'][$key]['quantity'];
        }
        break;

        /* -------------------------
           DECREASE
        ------------------------- */
    case 'decrease':
        $key = $_GET['key'] ?? '';

        if (isset($_SESSION['cart'][$key])) {
            --$_SESSION['cart'][$key]['quantity'];

            if ($_SESSION['cart'][$key]['quantity'] <= 0) {
                unset($_SESSION['cart'][$key]);
            }
        }
        break;

        /* -------------------------
           REMOVE ITEM
        ------------------------- */
    case 'remove':
        $key = $_GET['key'] ?? '';

        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }
        break;
}

/* =========================
   AJAX GET CART COUNT
========================= */
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    echo json_encode([
        'success' => true,
        'count' => calculateCartCount($_SESSION['cart']),
        'total' => array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $_SESSION['cart'])),
        'cart' => $_SESSION['cart'],
    ]);
    exit;
}

/* =========================
   REDIRECT HANDLER
========================= */
switch ($redirect) {
    case 'cart':
        header('Location: ../views/cart.php');
        exit;

    case 'products':
        header('Location: ../views/products.php');
        exit;

    case 'promotion':
        header('Location: ../views/promotion.php');
        exit;

    case 'checkout':
        $ids = $_GET['ids'] ?? '';
        header('Location: ../views/checkout.php?ids='.$ids);
        exit;

    case 'detail':
        $idProduct = (int) ($_GET['id_product'] ?? 0);
        header('Location: ../views/product_detail.php?id='.$idProduct);
        exit;
}

/* =========================
   DEFAULT
========================= */
header('Location: ../views/index.php');
exit;
