<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/PasswordReset.php';

use Tumik\CMS\PasswordReset;

$token = $_GET['token'] ?? '';
$error = null; $ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $p1 = trim($_POST['password'] ?? '');
    $p2 = trim($_POST['confirm'] ?? '');
    if ($p1 === '' || $p1 !== $p2) { $error = 'Hesla se neshodují'; }
    else if (!PasswordReset::consume($token, $p1)) { $error = 'Neplatný nebo expirovaný odkaz'; }
    else { $ok = 'Heslo změněno. Můžete se přihlásit.'; }
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
    <h1 class="text-xl font-semibold mb-4">Reset hesla</h1>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="mb-3 p-3 bg-green-50 text-green-700 rounded"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <div>
        <label class="block text-sm mb-1">Nové heslo</label>
        <input type="password" name="password" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Zopakovat heslo</label>
        <input type="password" name="confirm" class="w-full border rounded p-2" required>
      </div>
      <button class="bg-blue-600 text-white px-3 py-2 rounded">Změnit heslo</button>
    </form>
  </div>
</body>
</html>
