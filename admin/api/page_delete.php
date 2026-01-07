<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Json.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database; use Tumik\CMS\Json;

Auth::requireRole('editor');
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$id) { Json::error('missing_id', 422); exit; }
$pdo = Database::conn();
$st = $pdo->prepare('DELETE FROM pages WHERE id=?');
$st->execute([$id]);
Json::ok();
