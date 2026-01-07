<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
$pdo = Database::conn();

$counts = [];
foreach (['users','posts','pages','media'] as $t) {
    $counts[$t] = (int)$pdo->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <header class="max-w-5xl mx-auto p-6 flex justify-between">
    <div class="font-semibold">Administrace</div>
    <form action="/admin/logout.php" method="post"><button class="text-sm text-blue-600">Odhlásit</button></form>
  </header>
  <main class="max-w-5xl mx-auto p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
    <?php foreach ($counts as $k=>$v): ?>
      <div class="bg-white p-4 rounded border">
        <div class="text-sm text-gray-500"><?= htmlspecialchars(strtoupper($k)) ?></div>
        <div class="text-2xl font-bold"><?= $v ?></div>
      </div>
    <?php endforeach; ?>
  </main>
</body>
</html>
