session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

$userId = $_SESSION['user']['user_id'] ?? 0;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

case 'list':

    $stmt = $conn->prepare("
        SELECT p.*
        FROM user_vouchers uv
        JOIN promotions p ON p.promotion_id = uv.promotion_id
        WHERE uv.user_id = ?
        AND uv.is_used = 0
        AND p.status = 'active'
        AND CURDATE() BETWEEN p.start_date AND p.end_date
        AND p.used_count < p.quantity
    ");

    $stmt->execute([$userId]);

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
    break;

case 'apply':

    $code = trim($_POST['code'] ?? '');
    $total = (float)($_POST['total'] ?? 0);

    if ($code === '') {
        echo json_encode(['success' => false, 'message' => 'Thiếu mã voucher']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT p.*
        FROM promotions p
        JOIN user_vouchers uv ON uv.promotion_id = p.promotion_id
        WHERE uv.user_id = ?
        AND uv.is_used = 0
        AND p.code = ?
        AND p.status = 'active'
        AND CURDATE() BETWEEN p.start_date AND p.end_date
        AND p.used_count < p.quantity
        LIMIT 1
    ");

    $stmt->execute([$userId, $code]);
    $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$voucher) {
        echo json_encode(['success' => false, 'message' => 'Voucher không hợp lệ']);
        exit;
    }

    if ($voucher['discount_type'] == 'percent') {
        $discount = $total * $voucher['discount_value'] / 100;
    } else {
        $discount = $voucher['discount_value'];
    }

    if ($discount > $total) {
        $discount = $total;
    }

    $_SESSION['voucher'] = [
        'promotion_id' => $voucher['promotion_id'],
        'code' => $voucher['code'],
        'discount_type' => $voucher['discount_type'],
        'discount_value' => $voucher['discount_value'],
        'discount' => $discount
    ];

    echo json_encode([
        'success' => true,
        'discount' => $discount,
        'voucher' => $_SESSION['voucher']
    ]);

    break;

default:
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}