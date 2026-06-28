<?php
session_start();

$error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng ký</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body{
    margin:0;
    font-family:Arial;

    background:url("https://spacet-release.s3.ap-southeast-1.amazonaws.com/img/blog/2024-06-10/tong-hop-25-mau-thiet-ke-shop-quan-ao-dep-thu-hut-nhieu-khach-hang-1-6666776ac53bd68990a0fc8c.webp")
    no-repeat center center / cover;

    min-height:100vh;
    position:relative;
}

.overlay{
    position:fixed;
    inset:0;
    background:rgba(255,182,193,0.25);
    backdrop-filter:blur(4px);
    z-index:0;
}

.register-wrapper{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    position:relative;
    z-index:1;
}

/* REGISTER BOX */
.register-box{
    background:rgba(247,238,242,0.95);
    padding:40px;
    border-radius:20px;
    width:400px;
    text-align:center;
}

/* TITLE */
.register-box h2{
    color:#e05297;
    margin-bottom:30px;
}

/* INPUT GROUP */
.input-group{
    position:relative;
    margin-bottom:20px;
    width:100%;
}

/* LEFT ICON */
.input-group .left-icon{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    color:#e05297;
}

/* INPUT */
.input-group input{
    width:100%;
    padding:12px 40px 12px 40px;
    border-radius:10px;
    border:1px solid #e9a6c4;
    outline:none;
    box-sizing:border-box;
}

/* PASSWORD TOGGLE */
.toggle-password{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:#e05297;
    font-size:16px;
}

.toggle-password:hover{
    color:#d13d84;
}

/* BUTTON */
.register-btn{
    width:100%;
    padding:12px;
    background:#e05297;
    border:none;
    color:white;
    border-radius:10px;
    font-size:16px;
    cursor:pointer;
}

.register-btn:hover{
    background:#d13d84;
}

/* TEXT */
.bottom-text{
    margin-top:20px;
    font-size:14px;
}

.bottom-text a{
    color:#e05297;
    text-decoration:none;
    font-weight:bold;
}

.popup{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.35);
    z-index:9999;

    justify-content:center;
    align-items:center;
}

.popup-content{
    background:#fff;
    width:320px;
    padding:25px;
    border-radius:16px;
    text-align:center;
    box-shadow:0 10px 25px rgba(0,0,0,.15);
}

.popup-content p{
    margin-bottom:20px;
    color:#444;
    font-weight:600;
}

.popup-content button{
    border:none;
    background:#e05297;
    color:#fff;
    padding:10px 25px;
    border-radius:10px;
    cursor:pointer;
}

.popup-content button:hover{
    background:#d13d84;
}

</style>
</head>

<body>

<div class="overlay"></div>

<div class="register-wrapper">

    <div class="register-box">

        <h2>Đăng ký</h2>

        <form action="../controllers/RegisterController.php" method="POST">

            <!-- NAME -->
            <div class="input-group">
                <i class="fa-regular fa-user left-icon"></i>

                <input type="text"
                       name="name"
                       placeholder="Nhập họ tên"
                       required>
            </div>

            <!-- EMAIL -->
            <div class="input-group">
                <i class="fa-regular fa-envelope left-icon"></i>

                <input type="email"
                       name="email"
                       placeholder="Nhập email"
                       required>
            </div>

            <!-- PASSWORD -->
            <div class="input-group">
                <i class="fa-solid fa-lock left-icon"></i>

                <input type="password"
                       id="password"
                       name="password"
                       placeholder="Nhập mật khẩu"
                       required>

                <span class="toggle-password"
                      onclick="togglePassword()">
                    <i class="fa-regular fa-eye"></i>
                </span>
            </div>

            <!-- CONFIRM PASSWORD -->
            <div class="input-group">
                <i class="fa-solid fa-lock left-icon"></i>

                <input type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Xác nhận mật khẩu"
                    required>

                <span class="toggle-password"
                    onclick="toggleConfirmPassword()">
                    <i class="fa-regular fa-eye"></i>
                </span>
            </div>

            <button type="submit"
                    class="register-btn">
                Đăng ký
            </button>

        </form>

        <div class="bottom-text">
            Đã có tài khoản?
            <a href="login.php">Đăng nhập ngay</a>
        </div>

    </div>

</div>

<script>
function togglePassword() {

    const pass = document.getElementById("password");
    const icon = document.querySelector(".toggle-password i");

    if (pass.type === "password") {

        pass.type = "text";

        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");

    } else {

        pass.type = "password";

        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>

<script>

function togglePassword() {

    const pass = document.getElementById("password");
    const icon = document.querySelectorAll(".toggle-password i")[0];

    if (pass.type === "password") {
        pass.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        pass.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

function toggleConfirmPassword() {

    const pass = document.getElementById("confirm_password");
    const icon = document.querySelectorAll(".toggle-password i")[1];

    if (pass.type === "password") {
        pass.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        pass.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

document.querySelector("form").addEventListener("submit", function(e){

    const password =
        document.getElementById("password").value;

    const confirmPassword =
        document.getElementById("confirm_password").value;

    if(password !== confirmPassword){

        e.preventDefault();

        showPopup("Mật khẩu xác nhận không khớp!");
    }
});
</script>

<div id="popup" class="popup">
    <div class="popup-content">
        <p id="popup-message"></p>

        <button onclick="closePopup()">
            OK
        </button>
    </div>
</div>

<script>

function showPopup(message){

    document.getElementById("popup-message").innerText =
        message;

    document.getElementById("popup").style.display =
        "flex";
}

function closePopup(){

    document.getElementById("popup").style.display =
        "none";
}

</script>

<?php if (!empty($error)) { ?>
<script>
window.onload = function(){
    showPopup("<?php echo $error; ?>");
};
</script>
<?php } ?>

</body>
</html>
