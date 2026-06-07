<?php

session_start();

require_once '../config/database.php';

/* =========================
   KIỂM TRA ĐĂNG NHẬP
========================= */

if (!isset($_SESSION['user'])) {
    header('Location: ../views/login.php');
    exit;
}

$userId = (int) ($_SESSION['user']['user_id'] ?? 0);

if ($userId <= 0) {
    exit('Không tìm thấy người dùng');
}

/* =========================
   NHẬN DỮ LIỆU FORM
========================= */

$receiverName = trim($_POST['receiver_name'] ?? '');
$receiverPhone = trim($_POST['receiver_phone'] ?? '');
$receiverEmail = trim($_POST['receiver_email'] ?? '');
$receiverAddress = trim($_POST['receiver_address'] ?? '');
$note = trim($_POST['note'] ?? '');

if (
    empty($receiverName)
    || empty($receiverPhone)
    || empty($receiverAddress)
) {
    exit('Vui lòng nhập đầy đủ thông tin giao hàng');
}

/* =========================
   GIỎ HÀNG ĐƯỢC CHỌN
========================= */

$checkoutIds = $_SESSION['checkout_ids'] ?? [];
$cart = $_SESSION['cart'] ?? [];

if (empty($checkoutIds)) {
    exit('Không có sản phẩm để thanh toán');
}

$orderItems = [];

$totalPrice = 0;
$totalQuantity = 0;

/* =========================
   LẤY THÔNG TIN SẢN PHẨM
========================= */

foreach ($checkoutIds as $cartKey) {
    if (!isset($cart[$cartKey])) {
        continue;
    }

    $item = $cart[$cartKey];

    $productId = (int) ($item['product_id'] ?? 0);
    $quantity = (int) ($item['quantity'] ?? 1);

    if ($productId <= 0 || $quantity <= 0) {
        continue;
    }

    $stmtProduct = $conn->prepare('
        SELECT *
        FROM products
        WHERE product_id = ?
        LIMIT 1
    ');

    $stmtProduct->execute([$productId]);

    $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        continue;
    }

    $price = (float) $product['price'];
    $subtotal = $price * $quantity;

    $totalPrice += $subtotal;
    $totalQuantity += $quantity;

    $stmtImage = $conn->prepare('
        SELECT image_url
        FROM product_images
        WHERE product_id = ?
        ORDER BY is_main DESC, id ASC
        LIMIT 1
    ');

    $stmtImage->execute([$productId]);

    $image = $stmtImage->fetchColumn();

    if (!$image) {
        $image = 'uploads/no-image.jpg';
    }

    $orderItems[] = [
        'product_id' => $productId,
        'name' => $product['name'],
        'quantity' => $quantity,
        'price' => $price,
        'subtotal' => $subtotal,
        'image' => $image,
        'size' => $item['size'] ?? '',
        'color' => $item['color'] ?? '',
    ];
}

/* =========================
   KHÔNG CÓ SẢN PHẨM
========================= */

if (empty($orderItems)) {
    exit('Không có sản phẩm hợp lệ');
}

/* =========================
   TẠO MÃ ĐƠN HÀNG
========================= */

$orderCode =
    'ORD'.
    date('YmdHis').
    strtoupper(substr(md5(uniqid()), 0, 4));

try {
    $conn->beginTransaction();

    /* =========================
       INSERT ORDER
    ========================= */

    $stmtOrder = $conn->prepare("
        INSERT INTO orders
        (
            order_code,
            user_id,
            total_price,
            total_quantity,
            status,
            receiver_name,
            receiver_phone,
            receiver_address,
            receiver_email,
            note,
            payment_method,
            payment_status
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            'COD',
            'unpaid'
        )
    ");

    $stmtOrder->execute([
        $orderCode,
        $userId,
        $totalPrice,
        $totalQuantity,
        'pending',
        $receiverName,
        $receiverPhone,
        $receiverAddress,
        $receiverEmail,
        $note,
    ]);

    $orderId = $conn->lastInsertId();

    /* =========================
       INSERT ORDER DETAILS
    ========================= */

    $stmtDetail = $conn->prepare('
        INSERT INTO order_details
        (
            order_id,
            product_id,
            name,
            size,
            color,
            quantity,
            price,
            subtotal,
            product_image
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ');

    foreach ($orderItems as $item) {
        $stmtDetail->execute([
            $orderId,
            $item['product_id'],
            $item['name'],
            $item['size'],
            $item['color'],
            $item['quantity'],
            $item['price'],
            $item['subtotal'],
            $item['image'],
        ]);
    }

    /* =========================
       XÓA GIỎ HÀNG ĐÃ THANH TOÁN
    ========================= */

    foreach ($checkoutIds as $cartKey) {
        if (isset($_SESSION['cart'][$cartKey])) {
            unset($_SESSION['cart'][$cartKey]);
        }
    }

    unset($_SESSION['checkout_ids']);

    $conn->commit();

    header(
        'Location: ../views/order_success.php?order_id='.
        $orderId
    );

    exit;
} catch (Exception $e) {
    $conn->rollBack();

    exit(
        'Lỗi tạo đơn hàng: '.
        $e->getMessage()
    );
}
