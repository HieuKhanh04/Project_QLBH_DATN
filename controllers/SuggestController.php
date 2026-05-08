<?php

require_once '../config/database.php';
require_once '../models/ProductModel.php';

$productModel = new ProductModel($conn);

$keyword = $_GET['keyword'] ?? '';

$products = [];

if (!empty($keyword)) {
    $products = $productModel->searchProducts($keyword);
}

echo json_encode($products);
