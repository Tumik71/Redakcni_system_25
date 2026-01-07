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
    $st = $pdo->prepare('SELECT * FROM users WHERE id=?');
    $st->execute([$id]);
    $user = $st->fetch();
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? 'author';
    $active   = isset($_POST['active']) ? 1 : 0;
    $password = trim($_POST['password'] ?? '');
    if ($username === '') { $error = 'Vyplňte uživatelské jméno'; }
    if (!$error) {
        if ($id) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $st = $pdo->prepare('UPDATE users SET username=?, email=?, role=?, active=?, password_hash=? WHERE id=?');
                $st->execute([$username,$email,$role,$active,$hash,$id]);
            } else {
                $st = $pdo->prepare('UPDATE users SET username=?, email=?, role=?, active=? WHERE id=?');
                $st->execute([$username,$email,$role,$active,$id]);
            }
        } else {
            $hash = password_hash($password !== '' ? $password : 'change_me_123', PASSWORD_DEFAULT);
            $st = $pdo->prepare('INSERT INTO users (username,email,role,active,password_hash) VALUES (?,?,?,?,?)');
            $st->execute([$username,$email,$role,$active,$hash]);
            $id = (int)$pdo->lastInsertId();
        }
        header('Location: /admin/users.php'); exit;
    }
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $id ? 'Upravit uživatele' : 'Nový uživatel' ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-xl mx-auto p-6">
    <h1 class="text-xl font-semibold mb-4"><?= $id ? 'Upravit uživatele' : 'Nový uživatel' ?></h1>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm mb-1">Uživatel</label>
        <input name="username" class="w-full border rounded p-2" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Email</label>
        <input type="email" name="email" class="w-full border rounded p-2" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
      </div>
      <div>
        <label class="block text-sm mb-1">Role</label>
        <select name="role" class="w-full border rounded p-2">
          <?php foreach (['admin','editor','author'] as $r): ?>
            <option value="<?= $r ?>" <?= (($user['role'] ?? 'author') === $r) ? 'selected' : '' ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <label class="inline-flex items-center space-x-2"><input type="checkbox" name="active" <?= (isset($user['active']) && $user['active']) ? 'checked' : '' ?>><span>Aktivní</span></label>
      <div>
        <label class="block text-sm mb-1">Heslo (ponechte prázdné pro beze změny)</label>
        <input type="password" name="password" class="w-full border rounded p-2">
      </div>
      <div class="flex gap-2">
        <button class="bg-blue-600 text-white px-3 py-2 rounded">Uložit</button>
        <a href="/admin/users.php" class="px-3 py-2 rounded border">Zpět</a>
      </div>
    </form>
  </div>
</body>
</html>
