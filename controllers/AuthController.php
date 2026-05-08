<!-- <?php

session_start();
require_once '../config/database.php';
require_once '../models/UserModel.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);
$products = $productModel->getAllProducts();
$userModel = new UserModel($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $userModel->login($email, $password);

    if ($user) {
        $_SESSION['user'] = $user;

        if ($user['role'] == 1) {
            header('Location: ../admin.php');
        } // USER
        else {
            // header('Location: ../controllers/HomeController.php');
            header('Location: ../views/index.php');
        }

        exit;
    } else {
        echo 'Sai email hoặc mật khẩu!';
    }
}
?> -->
<?php

session_start();

require_once '../config/database.php';
require_once '../models/UserModel.php';

$userModel = new UserModel($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $userModel->login($email, $password);

    if ($user) {
        $_SESSION['user'] = $user;

        if ($user['role'] == 1) {
            header('Location: ../admin.php');
        } else {
            header('Location: ../views/index.php');
        }

        exit;
    } else {
        echo 'Sai email hoặc mật khẩu!';
    }
}
?>