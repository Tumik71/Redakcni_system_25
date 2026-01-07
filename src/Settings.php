<?php
namespace Tumik\CMS;

use PDO;

class Settings {
    public static function get(string $key, ?string $default = null): ?string {
        try {
            $pdo = Database::conn();
            $st = $pdo->prepare('SELECT value FROM settings WHERE key = ? LIMIT 1');
            $st->execute([$key]);
            $row = $st->fetch();
            return $row['value'] ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, string $value): void {
        $pdo = Database::conn();
        $pdo->prepare('INSERT INTO settings(key,value) VALUES(?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)')->execute([$key,$value]);
    }
}
