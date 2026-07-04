<?php

require_once __DIR__.'/../config/database.php';

class ProductModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /* =========================
       GET ALL PRODUCTS
    ========================= */
    public function getAllProducts()
    {
        $sql = '
            SELECT
                p.product_id,
                p.name,
                p.slug,
                p.description,
                p.status,
                p.category_id,
                p.created_at,
                p.updated_at,
                img.image_url,
                COALESCE(SUM(v.quantity),0) AS total_quantity
            FROM products p

            LEFT JOIN product_variants v
                ON p.product_id = v.product_id

            LEFT JOIN product_images img
                ON p.product_id = img.product_id
                AND img.is_main = 1

            GROUP BY
                p.product_id,
                p.name,
                p.slug,
                p.description,
                p.status,
                p.category_id,
                p.created_at,
                p.updated_at,
                img.image_url

            ORDER BY p.product_id DESC
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       GET PRODUCT BY ID
    ========================= */
    public function getProductById($id)
    {
        $sql = '
            SELECT
                p.*,
                img.image_url,
                COALESCE(SUM(v.quantity),0) AS total_quantity
            FROM products p

            LEFT JOIN product_variants v
                ON p.product_id = v.product_id

            LEFT JOIN product_images img
                ON p.product_id = img.product_id
                AND img.is_main = 1

            WHERE p.product_id = ?

            GROUP BY p.product_id, img.image_url
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       SEARCH
    ========================= */
    public function searchProducts($keyword)
    {
        $sql = '
            SELECT
                p.product_id,
                p.name,
                p.slug,
                p.description,
                p.status,
                p.category_id,
                img.image_url,
                COALESCE(SUM(v.quantity),0) AS total_quantity
            FROM products p

            LEFT JOIN product_variants v
                ON p.product_id = v.product_id

            LEFT JOIN product_images img
                ON p.product_id = img.product_id
                AND img.is_main = 1

            WHERE p.name LIKE :keyword

            GROUP BY
                p.product_id,
                p.name,
                p.slug,
                p.description,
                p.status,
                p.category_id,
                img.image_url

            ORDER BY p.product_id DESC
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':keyword', "%$keyword%", PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       GET BY CATEGORY
    ========================= */
    public function getProductsByCategory($categoryId)
    {
        $sql = '
            SELECT
                p.product_id,
                p.name,
                p.slug,
                p.description,
                p.status,
                p.category_id,
                img.image_url,
                COALESCE(SUM(v.quantity),0) AS total_quantity
            FROM products p

            LEFT JOIN product_variants v
                ON p.product_id = v.product_id

            LEFT JOIN product_images img
                ON p.product_id = img.product_id
                AND img.is_main = 1

            WHERE p.category_id = ?

            GROUP BY
                p.product_id,
                p.name,
                p.slug,
                p.description,
                p.status,
                p.category_id,
                img.image_url

            ORDER BY p.product_id DESC
        ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$categoryId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       VARIANTS
    ========================= */
    public function getVariantsByProduct($productId)
    {
        $stmt = $this->conn->prepare('
            SELECT size, color, price, quantity
            FROM product_variants
            WHERE product_id = ?
        ');

        $stmt->execute([$productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasVariant($productId)
    {
        $stmt = $this->conn->prepare("
            SELECT
                SUM(size IS NOT NULL AND size != '') AS has_size,
                SUM(color IS NOT NULL AND color != '') AS has_color
            FROM product_variants
            WHERE product_id = ?
        ");

        $stmt->execute([$productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['has_size'] > 0 || $row['has_color'] > 0;
    }

    /* =========================
       PRICE RANGE (BASE)
    ========================= */
    public function getPriceRange($productId)
    {
        $stmt = $this->conn->prepare('
            SELECT 
                MIN(price) AS min_price,
                MAX(price) AS max_price
            FROM product_variants
            WHERE product_id = ?
        ');

        $stmt->execute([$productId]);
        $range = $stmt->fetch(PDO::FETCH_ASSOC);

        return $range ?: ['min_price' => 0, 'max_price' => 0];
    }

    /* =========================
       PROMOTION
    ========================= */
    public function getActivePromotion($productId)
    {
        $stmt = $this->conn->prepare("
            SELECT p.*
            FROM promotions p
            JOIN product_promotions pp 
                ON p.promotion_id = pp.promotion_id
            WHERE pp.product_id = ?
              AND p.status = 'active'
              AND CURDATE() BETWEEN p.start_date AND p.end_date
            LIMIT 1
        ");

        $stmt->execute([$productId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       FINAL PRICE RANGE (AUTO APPLY PROMO)
    ========================= */
    public function getFinalPriceRange($productId)
    {
        $range = $this->getPriceRange($productId);
        $promo = $this->getActivePromotion($productId);

        $min = (float) $range['min_price'];
        $max = (float) $range['max_price'];

        if ($promo) {
            if ($promo['discount_type'] === 'percent') {
                $min -= ($min * $promo['discount_value'] / 100);
                $max -= ($max * $promo['discount_value'] / 100);
            } else {
                $min -= $promo['discount_value'];
                $max -= $promo['discount_value'];
            }

            $min = max(0, $min);
            $max = max(0, $max);
        }

        return [
            'min_price' => $min,
            'max_price' => $max,
        ];
    }

    /* =========================
       FINAL PRICE SINGLE VARIANT
    ========================= */
    public function getFinalPrice($productId, $size = '', $color = '')
    {
        $stmt = $this->conn->prepare('
            SELECT price
            FROM product_variants
            WHERE product_id = ?
              AND size = ?
              AND color = ?
            LIMIT 1
        ');

        $stmt->execute([$productId, $size, $color]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $price = $row['price'] ?? 0;

        if ($price < 0) {
            $price = 0;
        }

        // apply promo
        $promo = $this->getActivePromotion($productId);

        if ($promo) {
            if ($promo['discount_type'] === 'percent') {
                $price -= ($price * $promo['discount_value'] / 100);
            } else {
                $price -= $promo['discount_value'];
            }
        }

        return max(0, $price);
    }
}
