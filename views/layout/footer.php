<?php
?>

<style>
.footer{
    background:#fff;
    margin-top:60px;
    padding:50px 60px;
    box-shadow:0 -2px 10px rgba(0,0,0,0.05);
}

.footer-container{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:40px;
}

.footer h3{
    color:#ff4fa3;
    margin-bottom:15px;
}

.footer p,
.footer a{
    color:#555;
    font-size:14px;
    text-decoration:none;
    line-height:1.8;
}

.footer a:hover{
    color:#ff4fa3;
}

/* bottom */
.footer-bottom{
    text-align:center;
    margin-top:40px;
    padding-top:20px;
    border-top:1px solid #eee;
    color:#999;
    font-size:13px;
}

.social i{
    margin-right:10px;
    font-size:18px;
    color:#ff4fa3;
    cursor:pointer;
}
</style>

<div class="footer">

    <div class="footer-container">

        <!-- ABOUT -->
        <div>
            <h3>HAN STORE</h3>
            <p>
                Thời trang trẻ trung, hiện đại dành cho giới trẻ Việt Nam.
                Chúng tôi mang đến sản phẩm chất lượng với giá tốt nhất.
            </p>
        </div>

        <!-- CONTACT -->
        <div>
            <h3>Liên hệ</h3>
            <p>📍 Hà Nội, Việt Nam</p>
            <p>📞 0123 456 789</p>
            <p>✉ support@hanstore.com</p>
        </div>

        <!-- POLICY -->
        <div>
            <h3>Chính sách</h3>
            <a href="#">Chính sách đổi trả</a><br>
            <a href="#">Chính sách bảo mật</a><br>
            <a href="#">Điều khoản sử dụng</a><br>
        </div>

        <!-- SOCIAL -->
        <div>
            <h3>Kết nối</h3>
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