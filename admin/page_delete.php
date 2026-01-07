<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('editor');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $pdo = Database::conn();
    $st = $pdo->prepare('DELETE FROM pages WHERE id = ?');
    $st->execute([$id]);
}
header('Location: /admin/pages.php');
