<?php
namespace Tumik\CMS;

use PDO; use Exception;

class Installer {
    public static function isInstalled(): bool {
        Env::load(__DIR__ . '/../.env');
        $db = Env::get('DB_NAME');
        $user = Env::get('DB_USER');
        $pass = Env::get('DB_PASS');
        if (!$db || !$user || !$pass) { return false; }
        try {
            $pdo = Database::conn();
            $pdo->query('SELECT 1 FROM users LIMIT 1');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
