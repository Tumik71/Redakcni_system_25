<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Media.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database; use Tumik\CMS\Media;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('editor');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $pdo = Database::conn();
    $st = $pdo->prepare('SELECT path FROM media WHERE id=?');
    $st->execute([$id]);
    $row = $st->fetch();
    if ($row) {
        $fs = Media::fsPathFromWeb($row['path']);
        @unlink($fs);
        $thumb = Media::thumbDirAbs() . DIRECTORY_SEPARATOR . basename($fs);
        @unlink($thumb);
        $pdo->prepare('DELETE FROM media WHERE id=?')->execute([$id]);
    }
}
header('Location: /admin/media.php');
