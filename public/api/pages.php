<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Database.php';

use Tumik\CMS\Database;

header('Content-Type: application/json; charset=utf-8');
$pdo = Database::conn();
$rows = $pdo->query('SELECT id, title, slug, created_at FROM pages ORDER BY created_at DESC LIMIT 50')->fetchAll();
echo json_encode(['items'=>$rows]);
