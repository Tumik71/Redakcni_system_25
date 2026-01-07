<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
$pdo = Database::conn();

function countTable($pdo, $table) {
    return (int)$pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
}

$counts = [
    'users' => countTable($pdo, 'users'),
    'posts' => countTable($pdo, 'posts'),
    'pages' => countTable($pdo, 'pages'),
];
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
  <main class="max-w-5xl mx-auto p-6 space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <a href="/admin/posts.php" class="bg-white p-4 rounded border block">
        <div class="text-sm text-gray-500">Články</div>
        <div class="text-2xl font-bold"><?= $counts['posts'] ?></div>
      </a>
      <a href="/admin/pages.php" class="bg-white p-4 rounded border block">
        <div class="text-sm text-gray-500">Stránky</div>
        <div class="text-2xl font-bold"><?= $counts['pages'] ?></div>
      </a>
      <div class="bg-white p-4 rounded border">
        <div class="text-sm text-gray-500">Uživatelé</div>
        <div class="text-2xl font-bold"><?= $counts['users'] ?></div>
      </div>
    </div>
  </main>
</body>
</html>
