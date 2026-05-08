<?php

require_once '../config/database.php';

// class UserModel {
//     private $conn;

//     public function __construct($db) {
//         $this->conn = $db;
//     }

//     public function login($email, $password) {
//         $sql = "SELECT * FROM users WHERE email = :email AND password = :password";
//         $stmt = $this->conn->prepare($sql);
//         $stmt->bindParam(':email', $email);
//         $stmt->bindParam(':password', $password);
//         $stmt->execute();

//         return $stmt->fetch(PDO::FETCH_ASSOC);
//     }
// }

require_once '../config/database.php';

class UserModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // LOGIN
    public function login($email, $password)
    {
        $sql = 'SELECT * FROM users 
                WHERE email = :email 
                AND password = :password';

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // CHECK EMAIL
    public function checkEmail($email)
    {
        $sql = 'SELECT * FROM users 
                WHERE email = :email';

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':email', $email);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // REGISTER
    public function register($name, $email, $password)
    {
        $sql = 'INSERT INTO users(name, email, password)
                VALUES(:name, :email, :password)';

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        return $stmt->execute();
    }
}
