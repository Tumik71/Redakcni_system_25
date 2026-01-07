<?php
namespace Tumik\CMS;

use PDO; use PDOException;

class Database {
    private static ?PDO $pdo = null;

    public static function conn(): PDO {
        if (self::$pdo) { return self::$pdo; }
        $host = Env::get('DB_HOST', 'localhost');
        $port = Env::get('DB_PORT', '3306');
        $db   = Env::get('DB_NAME', '');
        $user = Env::get('DB_USER', '');
        $pass = Env::get('DB_PASS', '');
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('DB connection failed');
        }
        return self::$pdo;
    }
}
