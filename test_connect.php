<?php
$host = "localhost";
$dbname = "shop_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    echo "Kết nối thành công!";
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>