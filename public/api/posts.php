<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Database.php';

use Tumik\CMS\Database;

header('Content-Type: application/json; charset=utf-8');
$pdo = Database::conn();
$limit = isset($_GET['limit']) ? max(1,(int)$_GET['limit']) : 10;
$rows = $pdo->prepare('SELECT id, title, slug, created_at FROM posts WHERE published = 1 ORDER BY created_at DESC LIMIT ?');
$rows->bindValue(1, $limit, PDO::PARAM_INT);
$rows->execute();
echo json_encode(['items'=>$rows->fetchAll()]);
