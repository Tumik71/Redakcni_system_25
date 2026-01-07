<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Activation.php';

use Tumik\CMS\Activation;

$token = $_GET['token'] ?? '';
$ok = false;
if ($token) { $ok = Activation::activate($token); }
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Aktivace účtu</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-md mx-auto p-6">
    <h1 class="text-xl font-semibold mb-4">Aktivace účtu</h1>
    <?php if ($ok): ?>
      <div class="mb-3 p-3 bg-green-50 text-green-700 rounded">Účet byl aktivován. Můžete se přihlásit.</div>
    <?php else: ?>
      <div class="mb-3 p-3 bg-red-50 text-red-700 rounded">Neplatný nebo expirovaný odkaz.</div>
    <?php endif; ?>
    <a href="/admin/index.php" class="text-blue-600">Přejít na přihlášení</a>
  </div>
</body>
</html>
