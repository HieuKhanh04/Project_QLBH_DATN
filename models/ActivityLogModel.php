<?php

class ActivityLogModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /* Ghi nhật ký */
    public function addLog(
        $userId,
        $action,
        $module,
        $description
    ) {
        $sql = '
            INSERT INTO activity_logs
            (
                user_id,
                action,
                module,
                description,
                ip_address
            )
            VALUES (?, ?, ?, ?, ?)
        ';

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $userId,
            $action,
            $module,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    /* Lấy danh sách */
    public function getAllLogs()
    {
        $sql = '
            SELECT
                activity_logs.*,
                users.name
            FROM activity_logs
            LEFT JOIN users
                ON users.user_id = activity_logs.user_id
            ORDER BY created_at DESC
        ';

        return $this->conn
            ->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Xóa log */
    public function clearLogs()
    {
        return $this->conn->exec('TRUNCATE TABLE activity_logs');
    }
}
