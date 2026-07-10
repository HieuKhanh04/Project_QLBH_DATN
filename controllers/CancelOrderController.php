<?php

session_start();
require_once '../config/database.php';

if (!isset($_SESSION['customer'])) {
    exit('Không đăng nhập');
}

$userId = $_SESSION['customer']['user_id'];

$orderId = (int) ($_POST['order_id'] ?? 0);

$reason = $_POST['cancel_reason'] ?? '';
$other = trim($_POST['other_reason'] ?? '');

if ($reason == 'other') {
    $reason = $other;
}

if (empty($reason)) {
    exit('Vui lòng chọn lý do');
}

$stmt = $conn->prepare('
    SELECT status
    FROM orders
    WHERE order_id = ?
    AND user_id = ?
');

$stmt->execute([
    $orderId,
    $userId,
]);

$order = $stmt->fetch();

if (!$order) {
    exit('Không tìm thấy đơn hàng');
}

if ($order['status'] != 'pending') {
    exit('Không thể hủy đơn hàng này');
}

$conn->beginTransaction();

try {
    // CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
    $stmt = $conn->prepare("
        UPDATE orders
        SET 
            status = 'cancelled',
            cancel_reason = ?
        WHERE order_id = ?
    ");

    $stmt->execute([
        $reason,
        $orderId,
    ]);

    // LẤY SẢN PHẨM TRONG ĐƠN
    $stmt = $conn->prepare('
        SELECT 
            product_id,
            size,
            color,
            quantity
        FROM order_details
        WHERE order_id = ?
    ');

    $stmt->execute([$orderId]);

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // HOÀN LẠI TỒN KHO
    $stmtStock = $conn->prepare('
        UPDATE product_variants
        SET quantity = quantity + ?
        WHERE product_id = ?
        AND size = ?
        AND color = ?
    ');

    foreach ($items as $item) {
        $stmtStock->execute([
            $item['quantity'],
            $item['product_id'],
            $item['size'],
            $item['color'],
        ]);
    }

    $conn->commit();

    $_SESSION['cancel_success'] = 'Đơn hàng đã được hủy thành công!';

    header('Location: ../views/profile.php?tab=orders');
    exit;
} catch (Exception $e) {
    $conn->rollBack();

    exit('Lỗi hủy đơn: '.$e->getMessage());
}
