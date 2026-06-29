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
                p.product_id,
                p.name,
                p.slug,
                p.price,
                p.description,
                p.status,
                p.category_id,
                p.created_at,
                p.updated_at,
                COALESCE(SUM(v.quantity),0) AS total_quantity
            FROM products p
            LEFT JOIN product_variants v
                ON p.product_id = v.product_id
            GROUP BY
                p.product_id,
                p.name,
                p.slug,
                p.price,
                p.description,
                p.status,
                p.category_id,
                p.created_at,
                p.updated_at
            ORDER BY p.product_id DESC
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
            SELECT
                p.*,
                COALESCE(SUM(v.quantity),0) AS total_quantity
            FROM products p
            LEFT JOIN product_variants v
                ON p.product_id = v.product_id
            WHERE p.product_id = ?
            GROUP BY p.product_id
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
                p.product_id,
                p.name,
                p.slug,
                p.price,
                p.description,
                p.status,
                p.category_id,
                p.created_at,
                p.updated_at,
                COALESCE(SUM(v.quantity),0) AS total_quantity
            FROM products p
            LEFT JOIN product_variants v
                ON p.product_id = v.product_id
            WHERE p.name LIKE :keyword
            GROUP BY
                p.product_id,
                p.name,
                p.slug,
                p.price,
                p.description,
                p.status,
                p.category_id,
                p.created_at,
                p.updated_at
            ORDER BY p.product_id DESC
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

    public function getProductsByCategory($categoryId)
    {
        $sql = '
            SELECT
                p.product_id,
                p.name,
                p.slug,
                p.price,
                p.description,
                p.status,
                p.category_id,
                p.created_at,
                p.updated_at,
                COALESCE(SUM(v.quantity),0) AS total_quantity
            FROM products p
            LEFT JOIN product_variants v
                ON p.product_id = v.product_id
            WHERE p.category_id = ?
            GROUP BY
                p.product_id,
                p.name,
                p.slug,
                p.price,
                p.description,
                p.status,
                p.category_id,
                p.created_at,
                p.updated_at
            ORDER BY p.product_id DESC
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$categoryId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
