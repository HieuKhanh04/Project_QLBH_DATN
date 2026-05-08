<?php

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../models/ProductModel.php';

$productModel = new ProductModel($conn);

// lấy keyword từ search
$keyword = $_GET['keyword'] ?? '';

if (!empty($keyword)) {
    // nếu có search
    $products = $productModel->searchProducts($keyword);
} else {
    // nếu không search
    $products = $productModel->getAllProducts();
}

// truyền sang view
require_once __DIR__.'/../views/index.php';
