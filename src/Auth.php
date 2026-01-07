<?php
namespace Tumik\CMS;

class Auth {
    public static function attempt(string $username, string $password): bool {
        $pdo = Database::conn();
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ? AND active = 1 LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password_hash'])) {
            $_SESSION['uid'] = $row['id'];
            $_SESSION['uname'] = $row['username'];
            return true;
        }
        return false;
    }

    public static function check(): bool {
        return isset($_SESSION['uid']);
    }

    public static function logout(): void {
        session_destroy();
    }
}
