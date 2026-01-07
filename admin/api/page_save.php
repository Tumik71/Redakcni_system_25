<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Util.php';
require_once __DIR__ . '/../../src/Json.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database; use Tumik\CMS\Util; use Tumik\CMS\Json;

Auth::requireRole('editor');
$pdo = Database::conn();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$title = trim($_POST['title'] ?? '');
$content = $_POST['content'] ?? '';
$slug = trim($_POST['slug'] ?? '');
if ($title === '') { Json::error('missing_title', 422); exit; }
if ($slug === '') { $slug = Util::slug($title); }
if ($id) {
    $st = $pdo->prepare('UPDATE pages SET title=?, slug=?, content=? WHERE id=?');
    $st->execute([$title,$slug,$content,$id]);
    Json::ok(['id'=>$id]);
} else {
    $st = $pdo->prepare('INSERT INTO pages (title, slug, content) VALUES (?,?,?)');
    $st->execute([$title,$slug,$content]);
    Json::ok(['id'=>(int)$pdo->lastInsertId()]);
}
