<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('admin');
$pdo = Database::conn();
$rows = $pdo->query('SELECT id, username, email, role, active, created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Uživatelé</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between mb-4">
      <h1 class="text-xl font-semibold">Uživatelé</h1>
      <a href="/admin/user_edit.php" class="bg-blue-600 text-white px-3 py-2 rounded">Nový uživatel</a>
    </div>
    <table class="w-full bg-white rounded border">
      <thead><tr class="text-left">
        <th class="p-3">Uživatel</th><th class="p-3">Email</th><th class="p-3">Role</th><th class="p-3">Stav</th><th class="p-3">Akce</th>
      </tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="border-t">
            <td class="p-3 font-medium"><?= htmlspecialchars($r['username']) ?></td>
            <td class="p-3 text-gray-600"><?= htmlspecialchars($r['email'] ?? '') ?></td>
            <td class="p-3"><?= htmlspecialchars($r['role']) ?></td>
            <td class="p-3"><?= $r['active'] ? 'Aktivní' : 'Blokován' ?></td>
            <td class="p-3 space-x-2">
              <a class="text-blue-600" href="/admin/user_edit.php?id=<?= $r['id'] ?>">Upravit</a>
              <a class="text-amber-600" href="/admin/user_reset.php?id=<?= $r['id'] ?>">Reset hesla</a>
              <a class="text-red-600" href="/admin/user_delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Opravdu smazat?')">Smazat</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="mt-4"><a class="text-sm text-gray-600" href="/admin/dashboard.php">Zpět na dashboard</a></div>
  </div>
</body>
</html>
