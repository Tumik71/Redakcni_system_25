<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('admin');
$pdo = Database::conn();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = null;
if ($id) {
    $st = $pdo->prepare('SELECT id, username FROM users WHERE id=?');
    $st->execute([$id]);
    $user = $st->fetch();
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm'] ?? '');
    if ($password === '' || $password !== $confirm) { $error = 'Hesla se neshodují'; }
    if (!$error && $id) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $st = $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?');
        $st->execute([$hash,$id]);
        header('Location: /admin/users.php'); exit;
    }
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset hesla</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-md mx-auto p-6">
    <h1 class="text-xl font-semibold mb-4">Reset hesla: <?= htmlspecialchars($user['username'] ?? '') ?></h1>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm mb-1">Nové heslo</label>
        <input type="password" name="password" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Zopakovat heslo</label>
        <input type="password" name="confirm" class="w-full border rounded p-2" required>
      </div>
      <div class="flex gap-2">
        <button class="bg-blue-600 text-white px-3 py-2 rounded">Uložit</button>
        <a href="/admin/users.php" class="px-3 py-2 rounded border">Zpět</a>
      </div>
    </form>
  </div>
</body>
</html>
