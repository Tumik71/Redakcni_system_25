<?php
namespace Tumik\CMS;

class Auth {
    public static function attempt(string $username, string $password): bool {
        $pdo = Database::conn();
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? AND active = 1 LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password_hash'])) {
            $_SESSION['uid'] = $row['id'];
            $_SESSION['uname'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            return true;
        }
        return false;
    }

    public static function check(): bool {
        return isset($_SESSION['uid']);
    }

    public static function role(): string {
        return $_SESSION['role'] ?? 'author';
    }

    public static function hasRole(string $needed): bool {
        $order = ['author'=>1,'editor'=>2,'admin'=>3];
        $cur = $order[self::role()] ?? 1;
        $need = $order[$needed] ?? 1;
        return $cur >= $need;
    }

    public static function requireRole(string $needed): void {
        if (!self::check() || !self::hasRole($needed)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    public static function logout(): void {
        session_destroy();
    }
}
