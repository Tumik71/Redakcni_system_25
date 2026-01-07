<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Media.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database; use Tumik\CMS\Media;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('editor');
$pdo = Database::conn();
$rows = $pdo->query('SELECT id, filename, path, mime, size, created_at FROM media ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Média</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between mb-4">
      <h1 class="text-xl font-semibold">Média</h1>
      <a href="/admin/media_upload.php" class="bg-blue-600 text-white px-3 py-2 rounded">Nahrát soubor</a>
    </div>
    <table class="w-full bg-white rounded border">
      <thead><tr class="text-left">
        <th class="p-3">Náhled</th><th class="p-3">Soubor</th><th class="p-3">Typ</th><th class="p-3">Velikost</th><th class="p-3">Akce</th>
      </tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="border-t">
            <td class="p-3">
              <?php if (str_starts_with($r['mime'],'image/')): ?>
                <img src="<?= htmlspecialchars(Media::thumbExists($r['filename']) ? Media::thumbPathFor($r['filename']) : $r['path']) ?>" alt="" class="h-12 w-12 object-cover rounded">
              <?php else: ?>
                <a href="<?= htmlspecialchars($r['path']) ?>" class="text-blue-600">Otevřít</a>
              <?php endif; ?>
            </td>
            <td class="p-3 font-mono text-sm"><?= htmlspecialchars($r['filename']) ?></td>
            <td class="p-3 text-gray-600"><?= htmlspecialchars($r['mime']) ?></td>
            <td class="p-3 text-gray-600"><?= number_format((int)$r['size']/1024, 1) ?> kB</td>
            <td class="p-3 space-x-2">
              <a class="text-red-600" href="/admin/media_delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Smazat soubor?')">Smazat</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="mt-4"><a class="text-sm text-gray-600" href="/admin/dashboard.php">Zpět na dashboard</a></div>
  </div>
</body>
</html>
