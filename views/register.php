<!DOCTYPE html>
<html lang="vi">
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<meta charset="UTF-8">
<title>Đăng ký</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background: linear-gradient(135deg,#fbc2eb,#f7c6e0);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

/* BOX */
.register-box{
    width:420px;
    background:#fff;
    padding:40px;
    border-radius:25px;
    box-shadow:0 5px 20px rgba(0,0,0,0.1);
}

/* TITLE */
.register-box h2{
    text-align:center;
    color:#e05297;
    margin-bottom:30px;
    font-size:40px;
}

/* INPUT GROUP */
.input-group{
    position:relative;
    margin-bottom:20px;
}

/* INPUT */
.input-group input{
    width:100%;
    padding:14px 45px;
    border:1px solid #f3a8cb;
    border-radius:12px;
    outline:none;
    box-sizing:border-box;
    font-size:15px;
}

/* ICON LEFT */
.input-group .left-icon{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    color:#e05297;
    font-size:18px;
}

/* EYE ICON */
.toggle-password{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:#888;
}

/* BUTTON */
.register-btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;
    background:#e05297;
    color:white;
    font-size:17px;
    font-weight:bold;
    cursor:pointer;
    transition:0.2s;
}

.register-btn:hover{
    background:#d13d84;
}

/* TEXT */
.bottom-text{
    margin-top:20px;
    text-align:center;
    font-size:15px;
}

.bottom-text a{
    color:#e05297;
    text-decoration:none;
    font-weight:bold;
}
</style>
</head>

<body>

<div class="register-box">

    <h2>Đăng ký</h2>

    <form action="../controllers/RegisterController.php" method="POST">

        <!-- NAME -->
        <div class="input-group">
            <i class="fa-regular fa-user left-icon"></i>
            <input type="text" name="name" placeholder="Nhập họ tên" required>
        </div>

        <!-- EMAIL -->
        <div class="input-group">
            <i class="fa-regular fa-envelope left-icon"></i>
            <input type="email" name="email" placeholder="Nhập email" required>
        </div>

        <!-- PASSWORD -->
        <div class="input-group">
            <i class="fa-solid fa-lock left-icon"></i>

            <input type="password"
                   name="password"
                   id="password"
                   placeholder="Nhập mật khẩu"
                   required>

            <span class="toggle-password" onclick="togglePassword()">
                <i class="fa-regular fa-eye"></i>
            </span>
        </div>

        <button type="submit" class="register-btn">
            Đăng ký
        </button>

    </form>

    <div class="bottom-text">
        Đã có tài khoản?
        <a href="login.php">Đăng nhập ngay</a>
    </div>

</div>

<script>
function togglePassword(){

    const password =
        document.getElementById("password");

    const icon =
        document.querySelector(".toggle-password i");

    if(password.type === "password"){

        password.type = "text";

        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");

    }else{

        password.type = "password";

        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>

</body>
</html>