<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng nhập</title>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    margin: 0;
    font-family: Arial;
    background: linear-gradient(135deg, #fbc2eb, #f7c6e0);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-box {
    background: #f7eef2;
    padding: 40px;
    border-radius: 20px;
    width: 400px;
    text-align: center;
}

.login-box h2 {
    color: #e05297;
    margin-bottom: 30px;
}

/* INPUT GROUP */
.input-group {
    position: relative;
    margin-bottom: 20px;
    width: 100%;
}

/* ICON LEFT */
.input-group i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #e05297;
}

/* INPUT */
.input-group input {
    width: 100%;
    padding: 12px 40px 12px 40px; /* chừa chỗ 2 bên */
    border-radius: 10px;
    border: 1px solid #e9a6c4;
    outline: none;
    box-sizing: border-box;
}

/* ICON MẮT */
.toggle-password {
    position: absolute;
    right: 50px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #e05297;
    font-size: 16px;
}

.toggle-password:hover {
    color: #d13d84;
}

/* BUTTON */
.login-box button {
    width: 100%;
    padding: 12px;
    background: #e05297;
    border: none;
    color: white;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
}

.login-box button:hover {
    background: #d13d84;
}

/* TEXT */
.login-box p {
    margin-top: 20px;
    font-size: 14px;
}

.login-box a {
    color: #e05297;
    text-decoration: none;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="login-box">
    <h2>Đăng nhập</h2>

    <form method="POST" action="../controllers/AuthController.php">

        <!-- EMAIL -->
        <div class="input-group">
            <i class="fa-regular fa-envelope"></i>
            <input type="email" name="email" placeholder="Nhập email" required>
        </div>

        <!-- PASSWORD -->
        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>

            <span class="toggle-password" onclick="togglePassword()">
                <i class="fa-regular fa-eye"></i>
            </span>
        </div>

        <button type="submit">Đăng nhập</button>
    </form>

    <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
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

</body>
</html>