<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Media.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database; use Tumik\CMS\Media;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('editor');
$pdo = Database::conn();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $info = Media::saveUpload($_FILES['file'] ?? []);
        $st = $pdo->prepare('INSERT INTO media (filename, path, mime, size) VALUES (?,?,?,?)');
        $st->execute([$info['filename'],$info['path'],$info['mime'],$info['size']]);
        header('Location: /admin/media.php'); exit;
    } catch (\Throwable $e) {
        $error = 'Chyba nahrávání: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nahrát médium</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-md mx-auto p-6">
    <h1 class="text-xl font-semibold mb-4">Nahrát soubor</h1>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="space-y-4">
      <input type="file" name="file" accept="image/*,application/pdf" required class="w-full border rounded p-2">
      <button class="bg-blue-600 text-white px-3 py-2 rounded">Nahrát</button>
      <a href="/admin/media.php" class="px-3 py-2 rounded border">Zpět</a>
    </form>
  </div>
</body>
</html>
