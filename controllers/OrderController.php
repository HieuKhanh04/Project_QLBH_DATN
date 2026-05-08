<?php

session_start();
require_once '../config/database.php';

$name = $_POST['name'];
$phone = $_POST['phone'];
$address = $_POST['address'];

$items = $_SESSION['checkout'] ?? [];

// tạo order
$stmt = $conn->prepare('INSERT INTO orders (name, phone, address, total) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $phone, $address, 0]);

$order_id = $conn->lastInsertId();

$total = 0;

// chi tiết đơn hàng
foreach ($items as $it) {
    $stmt2 = $conn->prepare('SELECT price FROM products WHERE id=?');
    $stmt2->execute([$it['id']]);
    $price = $stmt2->fetchColumn();

    $sub = $price * $it['qty'];
    $total += $sub;

    $stmt3 = $conn->prepare('
        INSERT INTO order_details (order_id, product_id, qty, price)
        VALUES (?, ?, ?, ?)
    ');

    $stmt3->execute([$order_id, $it['id'], $it['qty'], $price]);
}

// update total
$conn->prepare('UPDATE orders SET total=? WHERE id=?')
     ->execute([$total, $order_id]);

unset($_SESSION['checkout']);

echo 'Đặt hàng thành công!';
