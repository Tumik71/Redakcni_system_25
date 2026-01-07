<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/PasswordReset.php';

use Tumik\CMS\PasswordReset; use Tumik\CMS\Database;

$pdo = Database::conn();
$info = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $st = $pdo->prepare('SELECT id, email FROM users WHERE username=? AND active=1');
    $st->execute([$username]);
    $u = $st->fetch();
    if ($u) {
        $token = PasswordReset::generate((int)$u['id']);
        $link = '/reset.php?token=' . urlencode($token);
        $info = 'Resetovací odkaz: ' . htmlspecialchars($link) . ' (v produkci zaslat e-mailem)';
    } else {
        $error = 'Uživatel nenalezen nebo blokován';
    }
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Zapomenuté heslo</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-md mx-auto p-6">
    <h1 class="text-xl font-semibold mb-4">Zapomenuté heslo</h1>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($info): ?><div class="mb-3 p-3 bg-green-50 text-green-700 rounded"><?= $info ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm mb-1">Uživatelské jméno</label>
        <input name="username" class="w-full border rounded p-2" required>
      </div>
      <button class="bg-blue-600 text-white px-3 py-2 rounded">Vygenerovat odkaz</button>
    </form>
  </div>
</body>
</html>
