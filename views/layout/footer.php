<?php
?>

<style>
.footer{
    background:#fff;
    margin-top:60px;
    padding:70px 0; /* ↑ tăng padding tổng */

    font-family:'Quicksand', sans-serif;
    font-weight:600;
}

.footer h5{
    color:#ff4fa3;
    font-weight:700;
    margin-bottom:18px;
    font-size:18px;
}

.footer a{
    color:#555;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    line-height:2; /* ↑ giãn dòng link */
}

.footer a:hover{
    color:#ff4fa3;
}

.footer p{
    font-size:14px;
    color:#555;
    font-weight:600;
    margin-bottom:8px; /* ↑ giãn dòng */
    line-height:1.8;
}

.social{
    display:flex;
    align-items:center;
    gap:14px;   /* khoảng cách giữa icon */
    flex-wrap:nowrap; /* KHÔNG xuống dòng */
}

.social i{
    font-size:18px;
    color:#ff4fa3;
    cursor:pointer;
    transition:.2s;
    margin-right:0; /* bỏ margin cũ */
}

.social i:hover{
    transform:translateY(-2px);
    color:#e63d8d;
}

.footer-bottom{
    border-top:1px solid #eee;
    margin-top:40px;
    padding-top:20px;
    text-align:center;
    font-size:13px;
    color:#999;
    font-weight:600;
}
</style>

<div class="footer">

    <div class="container">

        <!-- GIÃN CỘT BẰNG gx-5 -->
        <div class="row g-5">

            <!-- ABOUT -->
            <div class="col-12 col-md-3">
                <h5>HAN STORE</h5>
                <p>
                    Thời trang trẻ trung, hiện đại dành cho giới trẻ Việt Nam.
                    Chúng tôi mang đến sản phẩm chất lượng với giá tốt nhất.
                </p>
            </div>

            <!-- CONTACT -->
            <div class="col-12 col-md-3">
                <h5>Liên hệ</h5>
                <p>📍 Hà Nội, Việt Nam</p>
                <p>📞 0123 456 789</p>
                <p>✉ support@hanstore.com</p>
            </div>

            <!-- POLICY -->
            <div class="col-12 col-md-3">
                <h5>Chính sách</h5>
                <a href="#">Chính sách đổi trả</a><br>
                <a href="#">Chính sách bảo mật</a><br>
                <a href="#">Điều khoản sử dụng</a><br>
            </div>

            <!-- SOCIAL -->
            <div class="col-12 col-md-3">
                <h5>Kết nối</h5>
                <div class="social">
                    <i class="fa-brands fa-facebook"></i>
                    <i class="fa-brands fa-instagram"></i>
                    <i class="fa-brands fa-tiktok"></i>
                </div>
            </div>

        </div>

        <div class="footer-bottom">
            © 2026 HAN STORE. All rights reserved.
        </div>

    </div>

</div>