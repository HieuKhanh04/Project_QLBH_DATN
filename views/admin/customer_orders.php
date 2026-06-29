<?php

require_once '../../config/database.php';

$phone = $_GET['phone'] ?? '';

$stmt = $conn->prepare('
    SELECT *
    FROM orders
    WHERE receiver_phone = ?
    ORDER BY created_at DESC
');

$stmt->execute([$phone]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$orders) {
    exit('Không tìm thấy đơn hàng.');
}

$customerName = $orders[0]['receiver_name'];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đơn hàng của khách hàng</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>

        body{
            font-family:Arial;
            background:#fff5f9;
            margin:0;
            padding:30px;
        }

        .box{
            background:#fff;
            border-radius:20px;
            padding:30px;
            box-shadow:0 5px 15px rgba(0,0,0,.08);
        }

        h1{
            color:#ff4fa3;
        }

        .info{
            margin:20px 0;
            line-height:2;
        }

        .orders{
            display:flex;
            flex-direction:column;
            gap:25px;
        }

        .order-card{
            background:#fff;
            border-radius:18px;
            border:1px solid #eee;
            box-shadow:0 3px 10px rgba(0,0,0,.05);
            overflow:hidden;
        }

        .order-header{
            padding:18px 25px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            background:#fff8fc;
            border-bottom:1px solid #f1f1f1;
        }

        .order-body{
            padding:22px 25px;
        }

        .order-info{
            display:grid;
            grid-template-columns:repeat(2,1fr);
            gap:18px;
        }

        .order-info p{
            margin:0;
        }

        .order-footer{
            padding:18px 25px;
            border-top:1px solid #eee;
            text-align:right;
        }

        .detail-btn{
            display:inline-block;
            background:#0d6efd;
            color:#fff;
            text-decoration:none;
            padding:10px 20px;
            border-radius:10px;
            font-weight:bold;
        }

        .detail-btn:hover{
            background:#0b5ed7;
        }

        th{
            text-align:left;
            color:#888;
            padding:15px 0;
        }

        td{
            padding:18px 0;
            border-top:1px solid #eee;
        }

        .status{
            padding:7px 15px;
            border-radius:30px;
            font-size:13px;
            font-weight:bold;
        }

        .pending{
            background:#fff3cd;
            color:#d48806;
        }

        .confirmed{
            background:#d6eaff;
            color:#1677ff;
        }

        .shipping{
            background:#d9f7ff;
            color:#08979c;
        }

        .delivered{
            background:#d9f7be;
            color:#389e0d;
        }

        .cancelled{
            background:#ffd6d6;
            color:#cf1322;
        }

        .btn{
            background:#1677ff;
            color:#fff;
            text-decoration:none;
            padding:8px 15px;
            border-radius:8px;
        }

        .back{
            display:inline-block;
            margin-top:25px;
            background:#ff4fa3;
            color:#fff;
            text-decoration:none;
            padding:12px 20px;
            border-radius:10px;
        }

    </style>

</head>

<body>

<div class="box">

    <h1>Đơn hàng của khách hàng</h1>

    <div class="info">
        <b>Khách hàng:</b>
        <?php echo htmlspecialchars($customerName); ?>
        <br>

        <b>Số điện thoại:</b>
        <?php echo htmlspecialchars($phone); ?>
        <br>

        <b>Tổng số đơn:</b>
        <?php echo count($orders); ?>
    </div>

    <div class="orders">

<?php foreach ($orders as $o) { ?>

<?php

switch ($o['status']) {
    case 'pending':
        $text = 'Chờ xác nhận';
        $class = 'pending';
        break;

    case 'confirmed':
        $text = 'Đã xác nhận';
        $class = 'confirmed';
        break;

    case 'shipping':
        $text = 'Đang giao';
        $class = 'shipping';
        break;

    case 'delivered':
        $text = 'Đã giao';
        $class = 'delivered';
        break;

    case 'cancelled':
        $text = 'Đã hủy';
        $class = 'cancelled';
        break;
}

    ?>

<div class="order-card">

    <div class="order-header">

        <div>
            <h3>
                Đơn hàng #<?php echo $o['order_id']; ?>
            </h3>

            <small>
                <?php echo date('d/m/Y H:i', strtotime($o['created_at'])); ?>
            </small>
        </div>

        <span class="status <?php echo $class; ?>">
            <?php echo $text; ?>
        </span>

    </div>

    <div class="order-body">
        <div class="order-info">
            <p>
                <strong>Mã đơn:</strong><br>
                <?php echo htmlspecialchars($o['order_code']); ?>
            </p>

            <p>
                <strong>Tổng tiền:</strong><br>
                <?php echo number_format($o['total_price']); ?> đ
            </p>

            <p>
                <strong>Thanh toán:</strong><br>
                <?php echo htmlspecialchars($o['payment_method']); ?>
            </p>

            <p>
                <strong>Người nhận:</strong><br>
                <?php echo htmlspecialchars($o['receiver_name']); ?>
            </p>

            <p>
                <strong>SĐT:</strong><br>
                <?php echo htmlspecialchars($o['receiver_phone']); ?>
            </p>

            <p>
                <strong>Địa chỉ:</strong><br>
                <?php echo htmlspecialchars($o['receiver_address']); ?>
            </p>

            <?php if ($o['status'] == 'cancelled') { ?>

            <p>
                <strong>Hủy bởi:</strong><br>
                <?php echo htmlspecialchars($o['cancelled_by']); ?>
            </p>

            <p>
                <strong>Lý do:</strong><br>
                <?php echo htmlspecialchars($o['cancel_reason']); ?>
            </p>

            <?php } ?>

        </div>
    </div>

    <div class="order-footer">

        <a class="detail-btn"
           href="order_detail.php?id=<?php echo $o['order_id']; ?>">
            Xem chi tiết
        </a>

    </div>

</div>

<?php } ?>

</div>

    <a href="customers.php" class="back">
        <i class="fa-solid fa-arrow-left"></i>
        Quay lại
    </a>

</div>
</body>
</html>