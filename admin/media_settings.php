<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Settings.php';

use Tumik\CMS\Auth; use Tumik\CMS\Settings;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('admin');

$msg = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mime = trim($_POST['media_allowed_mime'] ?? '');
    $max = trim($_POST['media_max_upload_mb'] ?? '');
    if ($mime === '' || $max === '' || !preg_match('/^\d+$/',$max)) { $error = 'Vyplňte povolené typy a maximální velikost (MB).'; }
    else {
        Settings::set('media_allowed_mime', $mime);
        Settings::set('media_max_upload_mb', $max);
        $msg = 'Uloženo.';
    }
}

$curMime = Settings::get('media_allowed_mime','image/jpeg,image/png,image/gif,image/webp,application/pdf');
$curMax = Settings::get('media_max_upload_mb','10');
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nastavení médií</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-xl mx-auto p-6">
    <h1 class="text-xl font-semibold mb-4">Nastavení médií</h1>
    <?php if ($msg): ?><div class="mb-3 p-3 bg-green-50 text-green-700 rounded"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm mb-1">Povolené MIME typy (čárkami)</label>
        <input name="media_allowed_mime" class="w-full border rounded p-2" value="<?= htmlspecialchars($curMime) ?>" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Max. velikost souboru (MB)</label>
        <input name="media_max_upload_mb" class="w-full border rounded p-2" value="<?= htmlspecialchars($curMax) ?>" required>
      </div>
      <div class="flex gap-2">
        <button class="bg-blue-600 text-white px-3 py-2 rounded">Uložit</button>
        <a href="/admin/media.php" class="px-3 py-2 rounded border">Zpět</a>
      </div>
    </form>
  </div>
</body>
</html>
