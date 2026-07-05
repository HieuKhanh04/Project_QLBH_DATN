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

$userId = (int) ($_SESSION['customer']['user_id'] ?? 0);

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
   GIỎ HÀNG / BUY NOW
========================= */

$isBuyNow = !empty($_SESSION['buy_now']);
$buyNowItems = $_SESSION['buy_now'] ?? [];

$checkoutIds = $_SESSION['checkout_ids'] ?? [];
$cart = $_SESSION['cart'] ?? [];

if (!$isBuyNow && empty($checkoutIds)) {
    exit('Không có sản phẩm để thanh toán');
}

/* =========================
   TÍNH ORDER ITEMS
========================= */

$orderItems = [];
$totalPrice = 0;
$totalQuantity = 0;

if ($isBuyNow) {
    foreach ($buyNowItems as $item) {
        if (!is_array($item)) {
            continue;
        }

        $productId = (int) ($item['product_id'] ?? 0);
        $quantity = (int) ($item['quantity'] ?? 1);

        if ($productId <= 0 || $quantity <= 0) {
            continue;
        }

        $stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            continue;
        }

        $size = $item['size'] ?? '';
        $color = $item['color'] ?? '';

        $stmt = $conn->prepare("
            SELECT price
            FROM product_variants
            WHERE product_id = ?
            AND (size = ? OR (? = '' AND size IS NULL))
            AND (color = ? OR (? = '' AND color IS NULL))
            LIMIT 1
        ");

        $stmt->execute([$productId, $size, $size, $color, $color]);
        $price = $stmt->fetchColumn();

        if ($price === false) {
            $price = $product['price'];
        }

        $price = (float) $price;
        $subtotal = $price * $quantity;

        $totalPrice += $subtotal;
        $totalQuantity += $quantity;

        $stmt = $conn->prepare('
            SELECT image_url
            FROM product_images
            WHERE product_id = ?
            ORDER BY is_main DESC, id ASC
            LIMIT 1
        ');

        $stmt->execute([$productId]);
        $image = $stmt->fetchColumn() ?: 'uploads/no-image.jpg';

        $orderItems[] = [
            'product_id' => $productId,
            'name' => $product['name'],
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $subtotal,
            'image' => $image,
            'size' => $size,
            'color' => $color,
        ];
    }
} else {
    foreach ($checkoutIds as $cartKey) {
        if (!isset($cart[$cartKey])) {
            continue;
        }

        $item = $cart[$cartKey];

        $productId = (int) $item['product_id'];
        $quantity = (int) $item['quantity'];

        if ($productId <= 0 || $quantity <= 0) {
            continue;
        }

        $stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            continue;
        }

        $size = $item['size'] ?? '';
        $color = $item['color'] ?? '';

        $stmt = $conn->prepare("
            SELECT price
            FROM product_variants
            WHERE product_id = ?
            AND (size = ? OR (? = '' AND size IS NULL))
            AND (color = ? OR (? = '' AND color IS NULL))
            LIMIT 1
        ");

        $stmt->execute([$productId, $size, $size, $color, $color]);
        $price = $stmt->fetchColumn();

        if ($price === false) {
            $price = $product['price'];
        }

        $price = (float) $price;
        $subtotal = $price * $quantity;

        $totalPrice += $subtotal;
        $totalQuantity += $quantity;

        $stmt = $conn->prepare('
            SELECT image_url
            FROM product_images
            WHERE product_id = ?
            ORDER BY is_main DESC, id ASC
            LIMIT 1
        ');

        $stmt->execute([$productId]);
        $image = $stmt->fetchColumn() ?: 'uploads/no-image.jpg';

        $orderItems[] = [
            'product_id' => $productId,
            'name' => $product['name'],
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $subtotal,
            'image' => $image,
            'size' => $size,
            'color' => $color,
        ];
    }
}

/* =========================
   CHECK SẢN PHẨM
========================= */

if (empty($orderItems)) {
    exit('Không có sản phẩm hợp lệ');
}

/* =========================
   VOUCHER (SAU TOTAL)
========================= */

$discount = 0;
$promotionId = null;

if (!empty($_SESSION['voucher'])) {
    $promotionId = (int) ($_SESSION['voucher']['promotion_id'] ?? 0);

    $stmt = $conn->prepare('
        SELECT discount_type, discount_value
        FROM promotions
        WHERE promotion_id = ?
    ');

    $stmt->execute([$promotionId]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($promo) {
        if ($promo['discount_type'] === 'percent') {
            $discount = $totalPrice * $promo['discount_value'] / 100;
        } else {
            $discount = $promo['discount_value'];
        }
    }
}

/* APPLY DISCOUNT */
$totalPrice -= $discount;

if ($totalPrice < 0) {
    $totalPrice = 0;
}

/* =========================
   CREATE ORDER CODE
========================= */

$orderCode = 'ORD'.date('YmdHis').strtoupper(substr(md5(uniqid()), 0, 4));

try {
    $conn->beginTransaction();

    /* INSERT ORDER */
    $stmt = $conn->prepare("
        INSERT INTO orders (
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
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'COD', 'unpaid')
    ");

    $stmt->execute([
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

    /* INSERT ORDER DETAILS */
    $stmt = $conn->prepare('
        INSERT INTO order_details (
            order_id,
            product_id,
            name,
            size,
            color,
            quantity,
            price,
            subtotal,
            image
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    foreach ($orderItems as $item) {
        $stmt->execute([
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

    /* CLEAR VOUCHER */
    unset($_SESSION['voucher']);

    /* CLEAR CART */
    if ($isBuyNow) {
        unset($_SESSION['buy_now']);
    } else {
        foreach ($checkoutIds as $key) {
            unset($_SESSION['cart'][$key]);
        }
        unset($_SESSION['checkout_ids']);
    }

    $conn->commit();

    header("Location: ../views/order_success.php?order_id=$orderId");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    exit('Lỗi tạo đơn hàng: '.$e->getMessage());
}
