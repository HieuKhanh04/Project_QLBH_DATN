<?php

require_once '../config/database.php';

class ProductModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllProducts()
    {
        $sql = 'SELECT * FROM products';
        $stmt = $this->conn->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id)
    {
        $sql = 'SELECT * FROM products WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchProducts($keyword)
    {
        $sql = 'SELECT * FROM products WHERE name LIKE :keyword';
        $stmt = $this->conn->prepare($sql);

        $keyword = "%$keyword%";

        $stmt->bindValue(':keyword', $keyword, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
