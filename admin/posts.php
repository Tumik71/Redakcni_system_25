<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('editor');
$pdo = Database::conn();
$rows = $pdo->query('SELECT id, title, slug, published, created_at FROM posts ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Články</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-5xl mx-auto p-6">
    <div class="flex justify-between mb-4">
      <h1 class="text-xl font-semibold">Články</h1>
      <a href="/admin/post_edit.php" class="bg-blue-600 text-white px-3 py-2 rounded">Nový článek</a>
    </div>
    <table class="w-full bg-white rounded border">
      <thead><tr class="text-left">
        <th class="p-3">Název</th><th class="p-3">Slug</th><th class="p-3">Stav</th><th class="p-3">Akce</th>
      </tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="border-t">
            <td class="p-3"><?= htmlspecialchars($r['title']) ?></td>
            <td class="p-3 text-gray-600"><?= htmlspecialchars($r['slug']) ?></td>
            <td class="p-3"><?= $r['published'] ? 'Publikováno' : 'Koncept' ?></td>
            <td class="p-3 space-x-2">
              <a class="text-blue-600" href="/admin/post_edit.php?id=<?= $r['id'] ?>">Upravit</a>
              <a class="text-red-600" href="/admin/post_delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Opravdu smazat?')">Smazat</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="mt-4"><a class="text-sm text-gray-600" href="/admin/dashboard.php">Zpět na dashboard</a></div>
  </div>
</body>
</html>
