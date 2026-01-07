<?php
namespace Tumik\CMS;

use PDO;

class Settings {
    public static function get(string $key, ?string $default = null): ?string {
        try {
            $pdo = Database::conn();
            $st = $pdo->prepare('SELECT `value` FROM `settings` WHERE `key` = ? LIMIT 1');
            $st->execute([$key]);
            $row = $st->fetch();
            return $row['value'] ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, string $value): void {
        try {
            $pdo = Database::conn();
            $pdo->prepare('INSERT INTO `settings`(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)')->execute([$key,$value]);
        } catch (\PDOException $e) {
            try {
                $pdo = Database::conn();
                $st = $pdo->prepare('SELECT id FROM `settings` WHERE `key`=? LIMIT 1');
                $st->execute([$key]);
                $id = $st->fetchColumn();
                if ($id) {
                    $pdo->prepare('UPDATE `settings` SET `value`=? WHERE id=?')->execute([$value,$id]);
                } else {
                    $pdo->prepare('INSERT INTO `settings`(`key`,`value`) VALUES(?,?)')->execute([$key,$value]);
                }
            } catch (\Throwable $e2) {
                // swallow
            }
        }
    }
}
