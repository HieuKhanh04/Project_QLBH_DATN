<?php

session_start();
require_once '../config/database.php';

/* ===== GET USER ===== */
$user_id = $_SESSION['user']['id'] ?? null;

if (!$user_id) {
    exit('Bạn chưa đăng nhập');
}

/* ===== LẤY ITEMS ===== */
$items = $_SESSION['checkout_ids'] ?? [];

if (empty($items)) {
    exit('Không có sản phẩm để thanh toán');
}

/* ===== TẠO ORDER ===== */
$stmt = $conn->prepare('
    INSERT INTO orders (user_id, total)
    VALUES (?, ?)
');

$stmt->execute([$user_id, 0]);

$order_id = $conn->lastInsertId();

$total = 0;

/* ===== ORDER DETAILS ===== */
foreach ($items as $product_id) {
    $stmt2 = $conn->prepare('
        SELECT price, category_id
        FROM products
        WHERE id = ?
    ');
    $stmt2->execute([$product_id]);
    $product = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        continue;
    }

    $qty = $_SESSION['cart'][$product_id] ?? 1;

    $sub = $product['price'] * $qty;
    $total += $sub;

    $stmt3 = $conn->prepare('
        INSERT INTO order_details
        (order_id, product_id, category_id, qty, price)
        VALUES (?, ?, ?, ?, ?)
    ');

    $stmt3->execute([
        $order_id,
        $product_id,
        $product['category_id'],
        $qty,
        $product['price'],
    ]);
}

/* ===== UPDATE TOTAL (SỬA LỖI QUAN TRỌNG) ===== */
$conn->prepare('
    UPDATE orders SET total = ?
    WHERE id = ?
')->execute([$total, $order_id]);

/* ===== CLEAR SESSION ===== */
unset($_SESSION['checkout_ids']);
unset($_SESSION['cart']);

/* ===== REDIRECT SANG SUCCESS ===== */
header('Location: ../views/order_success.php?order_id='.$order_id);
exit;
