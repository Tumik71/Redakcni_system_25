<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Theme.php';
require_once __DIR__ . '/../src/Media.php';

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

$error = null; $success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? 'author';
    $active   = isset($_POST['active']) ? 1 : 0;
    $password = trim($_POST['password'] ?? '');
    $avatarPath = $user['avatar'] ?? null;
    if ($username === '') { $error = 'Vyplňte uživatelské jméno'; }
    if (!$error) {
        if (!empty($_FILES['avatar']['name'] ?? '')) {
            try {
                $up = Tumik\CMS\Media::saveUploadTo($_FILES['avatar'], 'avatars', true);
                $avatarPath = $up['path'];
            } catch (\Throwable $e) {
                $error = 'Nahrání avataru se nezdařilo';
            }
        }
        if ($id) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $st = $pdo->prepare('UPDATE users SET username=?, email=?, role=?, active=?, password_hash=?, avatar=? WHERE id=?');
                $st->execute([$username,$email,$role,$active,$hash,$avatarPath,$id]);
            } else {
                $st = $pdo->prepare('UPDATE users SET username=?, email=?, role=?, active=?, avatar=? WHERE id=?');
                $st->execute([$username,$email,$role,$active,$avatarPath,$id]);
            }
        } else {
            $hash = password_hash($password !== '' ? $password : 'change_me_123', PASSWORD_DEFAULT);
            $st = $pdo->prepare('INSERT INTO users (username,email,role,active,password_hash,avatar) VALUES (?,?,?,?,?,?)');
            $st->execute([$username,$email,$role,$active,$hash,$avatarPath]);
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#0ea5e9',dark:'#0369a1'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui']}}}}</script>
  <?php Tumik\CMS\Theme::injectHead(); ?>
</head>
<body class="bg-white dark:bg-slate-900 font-sans font-light">
  <div class="max-w-xl mx-auto p-6 pt-12">
    <h1 class="text-xl font-semibold mb-4"><?= $id ? 'Upravit uživatele' : 'Nový uživatel' ?></h1>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="space-y-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 rounded">
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Uživatel</label>
        <input name="username" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
      </div>
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Email</label>
        <input type="email" name="email" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
      </div>
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Role</label>
        <select name="role" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100">
          <?php foreach (['admin','editor','author'] as $r): ?>
            <option value="<?= $r ?>" <?= (($user['role'] ?? 'author') === $r) ? 'selected' : '' ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <label class="inline-flex items-center space-x-2"><input type="checkbox" name="active" <?= (isset($user['active']) && $user['active']) ? 'checked' : '' ?>><span>Aktivní</span></label>
      <div class="grid md:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Avatar</label>
          <input type="file" name="avatar" accept="image/*" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100">
          <div class="text-xs text-gray-600 dark:text-slate-400 mt-1">Obrázek bude zmenšen pro náhled.</div>
        </div>
        <div class="flex items-center">
          <?php if (!empty($user['avatar'])): ?>
            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="avatar" class="h-16 w-16 rounded-full object-cover border border-slate-200 dark:border-slate-700">
          <?php else: ?>
            <div class="h-16 w-16 rounded-full bg-slate-300 dark:bg-slate-700 flex items-center justify-center text-sm">N/A</div>
          <?php endif; ?>
        </div>
      </div>
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Heslo (ponechte prázdné pro beze změny)</label>
        <input type="password" name="password" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100">
      </div>
      <div class="flex gap-2">
        <button class="bg-brand text-white px-3 py-2 rounded">Uložit</button>
        <a href="/admin/users.php" class="px-3 py-2 rounded border">Zpět</a>
      </div>
    </form>
  </div>
</body>
</html>
