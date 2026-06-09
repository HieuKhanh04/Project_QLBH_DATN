<?php

require_once __DIR__.'/../config/database.php';

class ProductModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // =========================
    // LẤY TẤT CẢ SẢN PHẨM
    // (KHÔNG JOIN để tránh lỗi GROUP BY + variant rối)
    // =========================
    public function getAllProducts()
    {
        $sql = '
            SELECT 
                product_id,
                name,
                slug,
                price,
                description,
                status,
                category_id,
                created_at,
                updated_at
            FROM products
            ORDER BY product_id DESC
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // LẤY 1 SẢN PHẨM
    // =========================
    public function getProductById($id)
    {
        $sql = '
            SELECT * 
            FROM products 
            WHERE product_id = ?
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =========================
    // SEARCH SẢN PHẨM
    // =========================
    public function searchProducts($keyword)
    {
        $sql = '
            SELECT 
                product_id,
                name,
                slug,
                price,
                description,
                status,
                category_id,
                created_at,
                updated_at
            FROM products
            WHERE name LIKE :keyword
            ORDER BY product_id DESC
        ';

        $stmt = $this->conn->prepare($sql);

        $keyword = "%$keyword%";
        $stmt->bindValue(':keyword', $keyword, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // LẤY VARIANTS THEO PRODUCT
    // =========================
    public function getVariantsByProductId($productId)
    {
        $sql = '
            SELECT *
            FROM product_variants
            WHERE product_id = ?
            ORDER BY price ASC
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasVariant($productId)
    {
        $sql = "
            SELECT
                SUM(size IS NOT NULL AND size != '') AS has_size,
                SUM(color IS NOT NULL AND color != '') AS has_color
            FROM product_variants
            WHERE product_id = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return
            $row['has_size'] > 0
            || $row['has_color'] > 0
        ;
    }

    public function getVariantsByProduct($productId)
    {
        $stmt = $this->conn->prepare('
        SELECT size, color
        FROM product_variants
        WHERE product_id = ?
    ');

        $stmt->execute([$productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
