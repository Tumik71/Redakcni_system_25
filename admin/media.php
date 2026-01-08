<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Media.php';
require_once __DIR__ . '/../src/Theme.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database; use Tumik\CMS\Media;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('editor');
$pdo = Database::conn();
$rows = $pdo->query('SELECT id, filename, path, mime, size, created_at FROM media ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Média</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#0ea5e9',dark:'#0369a1'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui']}}}}</script>
  <?php \Tumik\CMS\Theme::injectHead(); ?>
</head>
<body class="bg-white dark:bg-slate-900 font-sans font-light">
  <nav class="fixed top-0 inset-x-0 z-30 backdrop-blur bg-white/70 dark:bg-slate-900/70 border-b border-slate-200 dark:border-slate-700">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
      <div class="font-semibold tracking-tight">Média</div>
      <div class="flex items-center gap-3">
        <a href="/admin/dashboard.php" class="text-sm text-gray-700 dark:text-slate-300 hover:text-brand">Dashboard</a>
        <button id="themeToggle" data-theme-toggle class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-gray-700 dark:text-slate-300" aria-label="Přepnout režim">
          <svg id="iconSun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
          <svg id="iconMoon" class="hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
      </div>
    </div>
  </nav>
  <div class="max-w-6xl mx-auto p-6 pt-20">
    <div class="flex justify-between mb-4">
      <h1 class="text-xl font-semibold">Média</h1>
      <div class="space-x-2">
        <a href="/admin/media_upload.php" class="bg-brand text-white px-3 py-2 rounded">Nahrát soubor</a>
        <?php if (Tumik\CMS\Auth::hasRole('admin')): ?>
        <a href="/admin/media_settings.php" class="px-3 py-2 rounded border">Nastavení</a>
        <?php endif; ?>
      </div>
    </div>
    <table class="w-full bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700">
      <thead><tr class="text-left">
        <th class="p-3">Náhled</th><th class="p-3">Soubor</th><th class="p-3">Typ</th><th class="p-3">Velikost</th><th class="p-3">Akce</th>
      </tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="border-t border-slate-200 dark:border-slate-700">
            <td class="p-3">
              <?php if (str_starts_with($r['mime'],'image/')): ?>
                <img src="<?= htmlspecialchars(Media::thumbExists($r['filename']) ? Media::thumbPathFor($r['filename']) : $r['path']) ?>" alt="" class="h-12 w-12 object-cover rounded">
              <?php else: ?>
                <a href="<?= htmlspecialchars($r['path']) ?>" class="text-brand">Otevřít</a>
              <?php endif; ?>
            </td>
            <td class="p-3 font-mono text-sm"><?= htmlspecialchars($r['filename']) ?></td>
            <td class="p-3 text-gray-600 dark:text-slate-300"><?= htmlspecialchars($r['mime']) ?></td>
            <td class="p-3 text-gray-600 dark:text-slate-300"><?= number_format((int)$r['size']/1024, 1) ?> kB</td>
            <td class="p-3 space-x-2">
              <a class="text-red-500" href="/admin/media_delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Smazat soubor?')">Smazat</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="mt-4"><a class="text-sm text-gray-600" href="/admin/dashboard.php">Zpět na dashboard</a></div>
  </div>
  <script>function syncIcons(){var d=document.documentElement.classList.contains('dark');var s=document.getElementById('iconSun');var m=document.getElementById('iconMoon');if(s&&m){s.classList.toggle('hidden', d);m.classList.toggle('hidden', !d);}}syncIcons();var b=document.getElementById('themeToggle');if(b){b.addEventListener('click',function(){var d=!document.documentElement.classList.contains('dark');document.documentElement.classList.toggle('dark', d);localStorage.setItem('theme',d?'dark':'light');syncIcons();});}</script>
</body>
</html>
