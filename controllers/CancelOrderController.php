<?php

session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
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

$_SESSION['cancel_success'] = 'Đơn hàng đã được hủy thành công!';

header('Location: ../views/profile.php?tab=orders');

exit;
