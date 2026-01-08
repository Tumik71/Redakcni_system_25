<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Theme.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
$pdo = Database::conn();

function countTable($pdo, $table) {
    return (int)$pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
}

$counts = [
    'users' => countTable($pdo, 'users'),
    'posts' => countTable($pdo, 'posts'),
    'pages' => countTable($pdo, 'pages'),
];
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin – Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#0ea5e9',dark:'#0369a1'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui']}}}}</script>
  <?php \Tumik\CMS\Theme::injectHead(); ?>
</head>
<body class="bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 font-sans font-light">
  <nav class="fixed top-0 inset-x-0 z-30 backdrop-blur bg-white/70 dark:bg-slate-900/70 border-b border-slate-200 dark:border-slate-700">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
      <div class="font-semibold tracking-tight">Administrace</div>
      <div class="flex items-center gap-3">
        <a href="/" class="text-sm text-gray-700 dark:text-slate-300 hover:text-brand">Domů</a>
        <button id="themeToggle" data-theme-toggle class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-gray-700 dark:text-slate-300" aria-label="Přepnout režim">
          <svg id="iconSun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
          <svg id="iconMoon" class="hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
        <form action="/admin/logout.php" method="post"><button class="text-sm text-brand">Odhlásit</button></form>
        <a href="/admin/theme_settings.php" class="text-sm text-gray-700 dark:text-slate-300 hover:text-brand">Nastavení</a>
      </div>
    </div>
  </nav>
  <main class="max-w-6xl mx-auto p-6 pt-20 space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <a href="/admin/posts.php" class="bg-white dark:bg-slate-800 p-4 rounded border border-slate-200 dark:border-slate-700 block">
        <div class="text-sm text-gray-500 dark:text-slate-400">Články</div>
        <div class="text-2xl font-bold"><?= $counts['posts'] ?></div>
      </a>
      <a href="/admin/pages.php" class="bg-white dark:bg-slate-800 p-4 rounded border border-slate-200 dark:border-slate-700 block">
        <div class="text-sm text-gray-500 dark:text-slate-400">Stránky</div>
        <div class="text-2xl font-bold"><?= $counts['pages'] ?></div>
      </a>
      <a href="/admin/media.php" class="bg-white dark:bg-slate-800 p-4 rounded border border-slate-200 dark:border-slate-700 block">
        <div class="text-sm text-gray-500 dark:text-slate-400">Média</div>
        <div class="text-2xl font-bold"><?= countTable($pdo, 'media') ?></div>
      </a>
      <a href="/admin/users.php" class="bg-white dark:bg-slate-800 p-4 rounded border border-slate-200 dark:border-slate-700 block">
        <div class="text-sm text-gray-500 dark:text-slate-400">Uživatelé</div>
        <div class="text-2xl font-bold"><?= $counts['users'] ?></div>
      </a>
    </div>
  </main>
  <script>function syncIcons(){var d=document.documentElement.classList.contains('dark');var s=document.getElementById('iconSun');var m=document.getElementById('iconMoon');if(s&&m){s.classList.toggle('hidden', d);m.classList.toggle('hidden', !d);}}syncIcons();var b=document.getElementById('themeToggle');if(b){b.addEventListener('click',function(){var d=!document.documentElement.classList.contains('dark');document.documentElement.classList.toggle('dark', d);localStorage.setItem('theme',d?'dark':'light');syncIcons();});}</script>
</body>
</html>
