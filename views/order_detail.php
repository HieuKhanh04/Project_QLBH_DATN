<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['customer'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['customer']['user_id'];
$orderId = (int) ($_GET['id'] ?? 0);

if ($orderId <= 0) {
    exit('Đơn hàng không tồn tại');
}

/* THÔNG TIN ĐƠN HÀNG */
$stmt = $conn->prepare('
    SELECT *
    FROM orders
    WHERE order_id = ?
    AND user_id = ?
');

$stmt->execute([
    $orderId,
    $userId,
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    exit('Không tìm thấy đơn hàng');
}

/* CHI TIẾT SẢN PHẨM */
$stmt = $conn->prepare('
    SELECT *
    FROM order_details
    WHERE order_id = ?
');

$stmt->execute([$orderId]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// echo '<pre>';
// print_r($items);
// echo '</pre>';
// exit;

function statusBadge($status)
{
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'confirmed':
            return 'info';
        case 'shipping':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

function statusText($status)
{
    switch ($status) {
        case 'pending':
            return 'Chờ xác nhận';

        case 'confirmed':
            return 'Đã xác nhận';

        case 'shipping':
            return 'Đang giao';

        case 'delivered':
            return 'Đã giao';

        case 'cancelled':
            return 'Đã hủy';

        default:
            return 'Không xác định';
    }
}
?>

<!DOCTYPE html>

<html lang="vi">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Chi tiết đơn hàng</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap"
rel="stylesheet">

<style>

body{
    background:#fff7fb;
    font-family:'Quicksand',sans-serif;
}

.card-custom{
    border:none;
    border-radius:20px;
    box-shadow:0 8px 25px rgba(0,0,0,.08);
    overflow:hidden;
}

.section-title{
    color:#ff4fa3;
    font-weight:700;
}

.product-img{
    width:80px;
    height:80px;
    object-fit:cover;
    border-radius:12px;
}

.total-price{
    color:#ff4fa3;
    font-size:24px;
    font-weight:700;
}

.btn-pink{
    background:#ff4fa3;
    color:#fff;
    border:none;
    border-radius:12px;
}

.btn-pink:hover{
    background:#e63d8d;
    color:#fff;
}

.cancel-modal .modal-dialog{
    max-width:550px;
}

.cancel-modal .modal-content{
    border-radius:25px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,.15);
}

.cancel-modal .modal-title{
    font-size:24px;
}

.cancel-modal select,
.cancel-modal textarea{
    border-radius:14px;
    padding:12px;
}

.cancel-modal .modal-footer{
    justify-content:center;
    gap:15px;
}

.btn-danger{
    border-radius:12px;
    font-weight:700;
}

</style>

</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container py-5">

<div class="card card-custom p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h3 class="section-title mb-0">
            Chi tiết đơn hàng
        </h3>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-<?php echo statusBadge($order['status']); ?>">
                <?php echo statusText($order['status']); ?>
            </span>

            <?php if ($order['status'] == 'pending') { ?>
                <button
                    class="btn btn-danger btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#cancelModal">

                    <i class="fa-solid fa-xmark"></i>
                    Hủy đơn
                </button>
            <?php } ?>
        </div>
    </div>

    <div class="row mb-4">

        <div class="col-md-6">

            <p>
                <b>Mã đơn:</b>
                <?php echo $order['order_code']; ?>
            </p>

            <p>
                <b>Ngày đặt:</b>
                <?php echo date(
                    'd/m/Y H:i',
                    strtotime($order['created_at'])
                ); ?>
            </p>

            <p>
                <b>Phương thức thanh toán:</b>
                <?php echo $order['payment_method']; ?>
            </p>

            <?php if ($order['status'] == 'cancelled') { ?>
                <p class="text-danger">
                    <b>Lý do hủy:</b>
                    <?php echo htmlspecialchars($order['cancel_reason']); ?>
                </p>
            <?php } ?>
        </div>

        <div class="col-md-6">

            <p>
                <b>Người nhận:</b>
                <?php echo htmlspecialchars($order['receiver_name']); ?>
            </p>

            <p>
                <b>Số điện thoại:</b>
                <?php echo htmlspecialchars($order['receiver_phone']); ?>
            </p>

            <p>
                <b>Địa chỉ:</b>
                <?php echo htmlspecialchars($order['receiver_address']); ?>
            </p>

        </div>

    </div>

    <hr>

    <h5 class="section-title mb-3">
        Sản phẩm đã đặt
    </h5>

    <div class="table-responsive">

        <table class="table align-middle">

            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>SL</th>
                    <th>Thành tiền</th>
                    <th>Đánh giá</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($items as $item) { ?>
                <?php
                    $check = $conn->prepare('
                    SELECT review_id
                    FROM product_reviews
                    WHERE
                    product_id = ?
                    AND order_id = ?
                    AND user_id = ?
                    ');

                    $check->execute([
                        $item['product_id'],
                        $orderId,
                        $userId,
                    ]);

                    $reviewed = $check->fetch();
                    ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img
                                src="../<?php echo htmlspecialchars($item['image']); ?>"
                                class="product-img"
                                onerror="this.src='../uploads/no-image.png';">

                            <div>
                                <div class="fw-bold">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </div>

                                <small class="text-muted">
                                    Size:
                                    <?php echo htmlspecialchars($item['size']); ?>
                                </small>

                                <br>

                                <small class="text-muted">
                                    Màu:
                                    <?php echo htmlspecialchars($item['color']); ?>
                                </small>
                            </div>
                        </div>
                    </td>

                    <td>
                        <?php echo number_format($item['price'], 0, ',', '.'); ?> đ
                    </td>

                    <td>
                        <?php echo $item['quantity']; ?>
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $item['price'] * $item['quantity'],
                            0,
                            ',',
                            '.'
                        );
                    ?> đ
                    </td>

                    <td>
                        <?php if ($order['status'] == 'delivered') { ?>
                        <?php if (!$reviewed) { ?>
                            <a href="review.php?product=<?php echo $item['product_id']; ?>&order=<?php echo $orderId; ?>&size=<?php echo urlencode($item['size']); ?>&color=<?php echo urlencode($item['color']); ?>"
                            class="btn btn-sm btn-pink">
                                <i class="fa-solid fa-star"></i>
                                Đánh giá
                            </a>
                        <?php } else { ?>
                            <a href="my_review.php?
                            product=<?php echo $item['product_id']; ?>
                            &order=<?php echo $orderId; ?>"
                            class="btn btn-sm btn-outline-success">

                                <i class="fa-solid fa-eye"></i>
                                Xem đánh giá
                            </a>
                        <?php } ?>
                        <?php } else { ?>

                        <span class="text-muted">
                            Chưa thể đánh giá
                        </span>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <hr>
    <div class="text-end">
        <div class="total-price">
            Tổng cộng:
            <?php
            echo number_format(
                $order['total_price'],
                0,
                ',',
                '.'
            ); ?> đ
        </div>
    </div>
    <div class="mt-4">

        <a href="profile.php?tab=orders"
           class="btn btn-pink">
            ← Quay lại đơn mua
        </a>
    </div>
</div>
</div>

<?php if (isset($_SESSION['review_success'])) { ?>

<div class="modal fade"
     id="reviewSuccessModal"
     tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content rounded-4">

            <div class="modal-body text-center p-5">

                <i class="fa-solid fa-circle-check text-success"
                   style="font-size:70px">
                </i>

                <h3 class="mt-3">
                    Thành công
                </h3>

                <p>
                    <?php
                    echo $_SESSION['review_success'];
    unset($_SESSION['review_success']); ?>
                </p>
                <button
                    class="btn btn-pink"
                    data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>

document.addEventListener("DOMContentLoaded", function(){

    let modalElement = document.getElementById(
        "reviewSuccessModal"
    );

    if(modalElement){

        let modal = new bootstrap.Modal(modalElement);

        modal.show();

    }

});

</script>

<?php } ?>

<?php if (isset($_SESSION['review_error'])) { ?>

<div class="modal fade"
     id="reviewErrorModal"
     tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">

            <div class="modal-body text-center p-5">
                <i class="fa-solid fa-circle-xmark text-danger"
                   style="font-size:70px">
                </i>

                <h3 class="mt-3">
                    Thông báo
                </h3>

                <p>
                    <?php
                        echo $_SESSION['review_error'];
    unset($_SESSION['review_error']); ?>
                </p>

                <button
                    class="btn btn-pink"
                    data-bs-dismiss="modal">

                    OK

                </button>
            </div>
        </div>
    </div>
</div>
<script>

document.addEventListener("DOMContentLoaded", function(){

    let modalElement = document.getElementById(
        "reviewErrorModal"
    );

    if(modalElement){

        let modal = new bootstrap.Modal(modalElement);

        modal.show();

    }

});

</script>

<?php } ?>

<?php if ($order['status'] == 'pending') { ?>

<div class="modal fade cancel-modal"
     id="cancelModal"
     tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <form action="../controllers/CancelOrderController.php" method="POST">
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title text-danger fw-bold">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>

                        Hủy đơn hàng

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body px-4">
                    <input
                        type="hidden"
                        name="order_id"
                        value="<?php echo $orderId; ?>">

                    <p class="text-center">
                        Bạn có chắc muốn hủy đơn hàng này?
                    </p>

                    <label class="fw-bold mb-2">
                        Lý do hủy
                    </label>

                    <select
                        name="cancel_reason"
                        id="cancelReason"
                        class="form-control"
                        onchange="showOtherReason()"
                        required>

                        <option value="">
                            -- Chọn lý do --
                        </option>

                        <option value="Đổi ý không muốn mua nữa">
                            Đổi ý không muốn mua nữa
                        </option>

                        <option value="Đặt nhầm sản phẩm">
                            Đặt nhầm sản phẩm
                        </option>

                        <option value="Muốn thay đổi sản phẩm">
                            Muốn thay đổi sản phẩm
                        </option>

                        <option value="Thời gian giao hàng lâu">
                            Thời gian giao hàng lâu
                        </option>

                        <option value="other">
                            Khác
                        </option>
                    </select>

                    <textarea
                        name="other_reason"
                        id="otherReason"
                        class="form-control mt-3"
                        placeholder="Nhập lý do khác"
                        style="display:none">
                    </textarea>
                </div>

                <div class="modal-footer border-0 justify-content-center gap-3 pb-4">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Đóng

                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger">
                        Xác nhận hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

function showOtherReason(){
    let reason =
        document.getElementById('cancelReason');
    let other =
        document.getElementById('otherReason');
    if(reason.value == 'other'){
        other.style.display='block';
        other.required=true;
    }else{
        other.style.display='none';
        other.required=false;
    }
}
</script>
<?php } ?>

<?php if (isset($_SESSION['cancel_success'])) { ?>

<div class="modal fade" id="cancelSuccessModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-body text-center p-5">
                <i class="fa-solid fa-circle-check text-success"
                   style="font-size:70px;">
                </i>

                <h3 class="mt-3">
                    Thành công
                </h3>

                <p>
                    <?php
                    echo $_SESSION['cancel_success'];
    unset($_SESSION['cancel_success']);
    ?>
                </p>

                <button
                    class="btn btn-pink"
                    data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function(){
        let modalElement =
            document.getElementById("cancelSuccessModal");
        if(modalElement){
            let modal =
                new bootstrap.Modal(modalElement);
            modal.show();

            setTimeout(function(){
                modal.hide();
            },2000);
        }
    });

</script>
<?php } ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'layout/footer.php'; ?>

</body>
</html>
