<?php
namespace Tumik\CMS;

class Activation {
    public static function create(int $userId, int $ttlHours = 24): string {
        $pdo = Database::conn();
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + $ttlHours * 3600);
        $pdo->prepare('INSERT INTO user_activations (user_id, token, expires_at) VALUES (?,?,?)')->execute([$userId,$token,$expires]);
        return $token;
    }

    public static function activate(string $token): bool {
        $pdo = Database::conn();
        $st = $pdo->prepare('SELECT user_id FROM user_activations WHERE token=? AND expires_at > NOW() LIMIT 1');
        $st->execute([$token]);
        $row = $st->fetch();
        if (!$row) { return false; }
        $uid = (int)$row['user_id'];
        $pdo->prepare('UPDATE users SET active=1 WHERE id=?')->execute([$uid]);
        $pdo->prepare('DELETE FROM user_activations WHERE token=?')->execute([$token]);
        return true;
    }
}
