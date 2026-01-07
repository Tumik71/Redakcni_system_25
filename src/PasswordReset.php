<?php
namespace Tumik\CMS;

use PDO;

class PasswordReset {
    public static function generate(int $userId, int $ttlMinutes = 30): string {
        $pdo = Database::conn();
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + $ttlMinutes * 60);
        $st = $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?,?,?)');
        $st->execute([$userId,$token,$expires]);
        return $token;
    }

    public static function validate(string $token): ?int {
        $pdo = Database::conn();
        $st = $pdo->prepare('SELECT user_id FROM password_resets WHERE token=? AND expires_at > NOW() LIMIT 1');
        $st->execute([$token]);
        $row = $st->fetch();
        return $row ? (int)$row['user_id'] : null;
    }

    public static function consume(string $token, string $newPassword): bool {
        $pdo = Database::conn();
        $userId = self::validate($token);
        if (!$userId) { return false; }
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$hash,$userId]);
        $pdo->prepare('DELETE FROM password_resets WHERE token=?')->execute([$token]);
        return true;
    }
}
