<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Database.php';

use Tumik\CMS\Database;

header('Content-Type: application/json; charset=utf-8');
$slug = $_GET['slug'] ?? '';
$pdo = Database::conn();
$st = $pdo->prepare('SELECT id, title, slug, content, created_at FROM posts WHERE slug = ? AND published = 1 LIMIT 1');
$st->execute([$slug]);
$row = $st->fetch();
if (!$row) { http_response_code(404); echo json_encode(['error'=>'not_found']); exit; }
echo json_encode($row);
