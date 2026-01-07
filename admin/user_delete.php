<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('admin');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $pdo = Database::conn();
    $st = $pdo->prepare('DELETE FROM users WHERE id=?');
    $st->execute([$id]);
}
header('Location: /admin/users.php');
