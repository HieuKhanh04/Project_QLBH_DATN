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

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

body{
    margin:0;
    font-family:Arial;
    background:#fff7fb;
}

/* CONTACT */
.contact-container{

    width:90%;

    max-width:1200px;

    margin:50px auto;

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:40px;
}

.contact-info,
.contact-form{

    background:white;

    padding:35px;

    border-radius:20px;

    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

.contact-info h2,
.contact-form h2{

    color:#ff4fa3;

    margin-bottom:25px;
}

.contact-info p{

    margin-bottom:18px;

    font-size:16px;

    color:#555;
}

.contact-info i{

    color:#ff4fa3;

    width:30px;
}

.contact-form input,
.contact-form textarea{

    width:100%;

    padding:14px;

    margin-bottom:18px;

    border:1px solid #ffd1e8;

    border-radius:12px;

    outline:none;
}

.contact-form textarea{

    height:140px;

    resize:none;
}

.contact-form button{

    border:none;

    background:#ff4fa3;

    color:white;

    padding:14px 25px;

    border-radius:12px;

    cursor:pointer;

    font-size:16px;
}

</style>

</head>

<body>

<?php include 'layout/header.php'; ?>

<div class="contact-container">

    <!-- LEFT -->
    <div class="contact-info">

        <h2>Thông tin liên hệ</h2>

        <p>
            <i class="fa-solid fa-location-dot"></i>
            Hà Nội, Việt Nam
        </p>

        <p>
            <i class="fa-solid fa-phone"></i>
            0123 456 789
        </p>

        <p>
            <i class="fa-solid fa-envelope"></i>
            hanstore@gmail.com
        </p>

    </div>

    <!-- RIGHT -->
    <div class="contact-form">

        <h2>Gửi tin nhắn</h2>

        <form>

            <input type="text"
                placeholder="Họ tên">

            <input type="email"
                placeholder="Email">

            <textarea
                placeholder="Nội dung..."></textarea>

            <button type="submit">
                Gửi liên hệ
            </button>

        </form>

    </div>

</div>

</body>
</html>