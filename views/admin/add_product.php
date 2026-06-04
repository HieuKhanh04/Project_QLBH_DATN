<?php

require_once '../../config/database.php';

$name = $_POST['name'];
$slug = $_POST['slug'];
$description = $_POST['description'];
$category_id = $_POST['category_id'];
$collection_id = $_POST['collection_id'];

$price = $_POST['price'];

$conn->beginTransaction();

try {
    /* PRODUCTS */

    $stmt = $conn->prepare('
        INSERT INTO products
        (
            name,
            slug,
            price,
            description,
            category_id,
            collection_id,
            status
        )
        VALUES
        (?, ?, ?, ?, ?, ?, 1)
    ');

    $stmt->execute([
        $name,
        $slug,
        $price,
        $description,
        $category_id,
        $collection_id,
    ]);

    $product_id = $conn->lastInsertId();

    /* VARIANT */

    $stmt = $conn->prepare('
        INSERT INTO product_variants
        (
            product_id,
            size,
            color,
            price,
            discount_price,
            quantity,
            image
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $product_id,
        $_POST['size'],
        $_POST['color'],
        $_POST['price'],
        $_POST['discount_price'],
        $_POST['quantity'],
        $_POST['variant_image'],
    ]);

    /* MAIN IMAGE */

    $stmt = $conn->prepare('
        INSERT INTO product_images
        (
            product_id,
            image_url,
            is_main
        )
        VALUES
        (?, ?, 1)
    ');

    $stmt->execute([
        $product_id,
        $_POST['main_image'],
    ]);

    $conn->commit();

    header('Location: products.php');
    exit;
} catch (Exception $e) {
    $conn->rollBack();

    echo $e->getMessage();
}
