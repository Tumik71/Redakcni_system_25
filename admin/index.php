<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

use Tumik\CMS\Auth;

if (Auth::check()) { header('Location: /admin/dashboard.php'); exit; }

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if (Auth::attempt($u, $p)) { header('Location: /admin/dashboard.php'); exit; }
    $error = 'Neplatné přihlašovací údaje';
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Přihlášení</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
  <form method="post" class="bg-white p-6 rounded shadow w-full max-w-sm">
    <h1 class="text-xl font-semibold mb-4">Administrace</h1>
    <?php if ($error): ?>
      <div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <label class="block mb-2 text-sm">Uživatel</label>
    <input name="username" class="w-full border rounded p-2 mb-4" required>
    <label class="block mb-2 text-sm">Heslo</label>
    <input type="password" name="password" class="w-full border rounded p-2 mb-4" required>
    <button class="w-full bg-blue-600 text-white py-2 rounded">Přihlásit</button>
    <div class="mt-3 flex justify-between text-sm">
      <a href="/forgot.php" class="text-blue-600">Zapomenuté heslo</a>
      <a href="/register.php" class="text-blue-600">Registrace</a>
    </div>
  </form>
</body>
</html>
