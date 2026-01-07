<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Installer.php';

use Tumik\CMS\Database; use Tumik\CMS\Installer;

if (!Installer::isInstalled()) { header('Location: /install/?step=1'); exit; }

$pdo = Database::conn();
$stmt = $pdo->query('SELECT id, title, slug, created_at FROM posts WHERE published = 1 ORDER BY created_at DESC LIMIT 10');
$posts = $stmt->fetchAll();
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tumik.cz</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <header class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold">Tumik.cz</h1>
    <p class="text-sm text-gray-600">Moderní CMS</p>
  </header>
  <main class="max-w-4xl mx-auto p-6">
    <h2 class="text-xl font-semibold mb-4">Poslední články</h2>
    <ul class="space-y-3">
      <?php foreach ($posts as $p): ?>
        <li class="p-4 bg-white rounded border">
          <a href="/post.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="text-blue-600 font-medium">
            <?= htmlspecialchars($p['title']) ?>
          </a>
          <div class="text-xs text-gray-500">Publikováno: <?= htmlspecialchars($p['created_at']) ?></div>
        </li>
      <?php endforeach; ?>
    </ul>
  </main>
  <footer class="max-w-4xl mx-auto p-6 text-sm text-gray-500">© <?= date('Y') ?> Tumik</footer>
</body>
</html>
