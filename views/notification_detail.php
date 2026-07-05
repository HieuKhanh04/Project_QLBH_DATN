<?php

session_start();

require_once '../config/database.php';

/* KIỂM TRA ĐĂNG NHẬP */
if (!isset($_SESSION['customer'])) {
    header('Location: login.php');
    exit;
}

$customerId = $_SESSION['customer']['user_id'];

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('Thông báo không tồn tại');
}

/* LẤY THÔNG TIN THÔNG BÁO */
$stmt = $conn->prepare('
    SELECT *
    FROM notifications
    WHERE notification_id = ?
');

$stmt->execute([$id]);

$notification = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$notification) {
    exit('Không tìm thấy thông báo');
}

/* ĐÁNH DẤU ĐÃ ĐỌC */
$stmt = $conn->prepare('
    SELECT *
    FROM notification_reads
    WHERE notification_id = ?
    AND customer_id = ?
');

$stmt->execute([
    $id,
    $customerId,
]);

$read = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$read) {
    $stmt = $conn->prepare('
        INSERT INTO notification_reads
        (
            notification_id,
            customer_id,
            is_read,
            read_at
        )
        VALUES
        (
            ?,
            ?,
            1,
            NOW()
        )
    ');

    $stmt->execute([
        $id,
        $customerId,
    ]);
} else {
    $stmt = $conn->prepare('
        UPDATE notification_reads
        SET
            is_read = 1,
            read_at = NOW()
        WHERE notification_id = ?
        AND customer_id = ?
    ');

    $stmt->execute([
        $id,
        $customerId,
    ]);
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>
    <?php echo htmlspecialchars($notification['title']); ?>
</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap"
rel="stylesheet">

<style>

:root{
    --pink:#ff4fa3;
    --pink-hover:#e63d8d;
}

body{
    background:#fff7fb;
    font-family:'Quicksand',sans-serif;
}

/* CARD */
.notification-card{
    border:none;
    border-radius:24px;
    overflow:hidden;
    background:#fff;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
}

/* HEADER */
.notification-header{
    padding:30px;
    background:linear-gradient(
        135deg,
        #ff4fa3,
        #ff85c1
    );
    color:#fff;
}

.notification-header h1{
    font-size:32px;
    font-weight:700;
    margin-bottom:10px;
}

.notification-date{
    opacity:.9;
    font-size:14px;
}

/* BODY */
.notification-body{
    padding:35px;
}

.notification-content{
    font-size:16px;
    line-height:1.9;
    color:#555;
    white-space:pre-wrap;
}

/* BUTTON */
.btn-pink{
    background:var(--pink);
    border:2px solid var(--pink);
    color:#fff;
    font-weight:700;
    border-radius:12px;
    padding:10px 22px;
}

.btn-pink:hover{
    background:var(--pink-hover);
    border-color:var(--pink-hover);
    color:#fff;
}

.badge-system{
    background:#ffe3f1;
    color:#ff4fa3;
    font-size:13px;
    padding:8px 12px;
    border-radius:999px;
    font-weight:700;
}

</style>

</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-10">

            <div class="notification-card">

                <div class="notification-header">

                    <span class="badge-system">
                        THÔNG BÁO HỆ THỐNG
                    </span>

                    <h1 class="mt-3">
                        <?php
                        echo htmlspecialchars(
                            $notification['title']
                        );
?>
                    </h1>

                    <div class="notification-date">

                        <i class="fa-regular fa-clock"></i>

                        <?php
echo date(
    'd/m/Y H:i',
    strtotime(
        $notification['created_at']
    )
);
?>

                    </div>

                </div>

                <div class="notification-body">

                    <div class="notification-content">

                        <?php
echo nl2br(
    htmlspecialchars(
        $notification['content']
    )
);
?>

                    </div>
                    <hr class="my-4">
                    <a href="<?php echo $_SERVER['HTTP_REFERER'] ?? 'index.php'; ?>"
                        class="btn btn-pink">
                        <i class="fa-solid fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>