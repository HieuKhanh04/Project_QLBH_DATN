<?php
session_start();

$count = 0;
if (isset($_SESSION['cart'])) {
    $count = array_sum($_SESSION['cart']);
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<meta charset="UTF-8">
<title>Liên hệ</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

:root{
    --pink:#ff4fa3;
    --pink-hover:#e63d8d;
}

/* BASE */
body{
    background:#fff7fb;
    font-family:'Quicksand', sans-serif;
    font-weight:600;
}

/* CARD */
.contact-card{
    border:none;
    border-radius:20px;
    background:#fff;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
    transition:.3s ease;
}

.contact-card:hover{
    transform:translateY(-4px);
    box-shadow:0 10px 25px rgba(0,0,0,0.12);
}

/* TITLE */
.title{
    color:var(--pink);
    font-weight:700;
    font-size:24px;
}

/* ICON */
.contact-icon{
    color:var(--pink);
    width:28px;
    margin-right:8px;
}

/* INPUT */
.form-control{
    border-radius:12px;
    border:2px solid #eee;
    padding:12px 14px;
    font-weight:600;
    transition:.2s;
}

.form-control:focus{
    border-color:var(--pink);
    box-shadow:0 0 0 3px rgba(255,79,163,.15);
}

/* =========================
   BUTTON SYSTEM (SYNC SITE)
========================= */

.btn-pink{
    background:var(--pink);
    border:2px solid var(--pink);
    color:#fff;
    font-weight:700;
    height:46px;
    border-radius:12px;
    transition:.25s ease;
    display:flex;
    align-items:center;
    justify-content:center;
}

.btn-pink:hover{
    background:var(--pink-hover);
    border-color:var(--pink-hover);
    transform:translateY(-2px);
    box-shadow:0 6px 16px rgba(255,79,163,.25);
}

</style>
</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="container py-5">

    <div class="row g-4">

        <!-- LEFT -->
        <div class="col-12 col-lg-6">

            <div class="card contact-card p-4 h-100">

                <h3 class="title mb-4">Thông tin liên hệ</h3>

                <p class="mb-3">
                    <i class="fa-solid fa-location-dot contact-icon"></i>
                    Hà Nội, Việt Nam
                </p>

                <p class="mb-3">
                    <i class="fa-solid fa-phone contact-icon"></i>
                    0123 456 789
                </p>

                <p class="mb-3">
                    <i class="fa-solid fa-envelope contact-icon"></i>
                    hanstore@gmail.com
                </p>

                <hr>

                <p class="text-muted small">
                    Chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7
                </p>

            </div>

        </div>

        <!-- RIGHT -->
        <div class="col-12 col-lg-6">

            <div class="card contact-card p-4 h-100">

                <h3 class="title mb-4">Gửi tin nhắn</h3>

                <form>

                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Họ tên">
                    </div>

                    <div class="mb-3">
                        <input type="email" class="form-control" placeholder="Email">
                    </div>

                    <div class="mb-3">
                        <textarea class="form-control" rows="5" placeholder="Nội dung..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-pink w-100">
                        Gửi liên hệ
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<?php include 'layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>