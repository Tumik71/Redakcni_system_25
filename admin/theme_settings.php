<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Settings.php';
require_once __DIR__ . '/../src/Theme.php';

use Tumik\CMS\Auth; use Tumik\CMS\Settings; use Tumik\CMS\Theme;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('admin');

$msg = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $family = trim($_POST['ui_font_family'] ?? '');
    $size = trim($_POST['ui_font_size'] ?? '');
    $weight = trim($_POST['ui_font_weight'] ?? '');
    if ($family === '' || $size === '' || $weight === '') { $error = 'Vyplňte rodinu písma, velikost a váhu.'; }
    else {
        Settings::set('ui_font_family', $family);
        Settings::set('ui_font_size', $size);
        Settings::set('ui_font_weight', $weight);
        $msg = 'Uloženo.';
    }
}

$curFamily = Settings::get('ui_font_family', 'Inter');
$curSize = Settings::get('ui_font_size', '16px');
$curWeight = Settings::get('ui_font_weight', '300');
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nastavení vzhledu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#0ea5e9',dark:'#0369a1'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui']}}}}</script>
  <?php Theme::injectHead(); ?>
</head>
<body class="bg-white dark:bg-slate-900 font-sans font-light">
  <nav class="fixed top-0 inset-x-0 z-30 backdrop-blur bg-white/70 dark:bg-slate-900/70 border-b border-slate-200 dark:border-slate-700">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
      <div class="font-semibold tracking-tight">Nastavení vzhledu</div>
      <a href="/admin/dashboard.php" class="text-sm text-gray-700 dark:text-slate-300 hover:text-brand">Dashboard</a>
    </div>
  </nav>
  <div class="max-w-3xl mx-auto p-6 pt-20">
    <?php if ($msg): ?><div class="mb-3 p-3 bg-green-50 text-green-700 rounded"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" class="space-y-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 rounded">
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Rodina písma</label>
        <input name="ui_font_family" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100" value="<?= htmlspecialchars($curFamily) ?>" required>
        <div class="text-xs text-gray-600 dark:text-slate-400 mt-1">Např. Inter, Roboto, Source Sans 3</div>
      </div>
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Velikost písma (px, rem)</label>
        <input name="ui_font_size" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100" value="<?= htmlspecialchars($curSize) ?>" required>
      </div>
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Váha písma</label>
        <input name="ui_font_weight" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100" value="<?= htmlspecialchars($curWeight) ?>" required>
        <div class="text-xs text-gray-600 dark:text-slate-400 mt-1">Např. 300 (light), 400 (normal), 600 (semibold)</div>
      </div>
      <div class="flex gap-3">
        <button class="bg-brand text-white px-3 py-2 rounded">Uložit</button>
        <a href="/admin/dashboard.php" class="px-3 py-2 rounded border">Zpět</a>
      </div>
    </form>
  </div>
</body>
</html>
