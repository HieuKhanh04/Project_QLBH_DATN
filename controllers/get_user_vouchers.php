<?php

session_start();
require_once '../config/database.php';

$userId = $_SESSION['user']['id'] ?? null;

if (!$userId) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare('
    SELECT v.*
    FROM vouchers v
    JOIN user_vouchers uv ON uv.voucher_id = v.id
    WHERE uv.user_id = ?
      AND uv.used = 0
      AND v.status = 1
      AND (v.expire_date IS NULL OR v.expire_date >= CURDATE())
');

$stmt->execute([$userId]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
