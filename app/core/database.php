<?php
namespace Config;

use PDO;
use PDOException;

class DatabaseConfig {
    private static ?PDO $conn = null;

    public static function getConnection(): PDO {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO('mysql:host=localhost;dbname=ims2k25;charset=utf8mb4', 'root', '');
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]));
            }
        }
        return self::$conn;
    }
}
