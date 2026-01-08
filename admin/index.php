<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Installer.php';

use Tumik\CMS\Auth; use Tumik\CMS\Installer;

if (!Installer::isInstalled()) { header('Location: /install/?step=1'); exit; }

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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{brand:{DEFAULT:'#0ea5e9',dark:'#0369a1'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui']}}}}</script>
  <script>(function(){var t=localStorage.getItem('theme');var d=t==='dark';document.documentElement.classList.toggle('dark', d);})();</script>
</head>
<body class="min-h-screen bg-white dark:bg-slate-900 font-sans font-light">
  <nav class="fixed top-0 inset-x-0 z-30 backdrop-blur bg-white/70 dark:bg-slate-900/70 border-b border-slate-200 dark:border-slate-700">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
      <a href="/" class="font-semibold tracking-tight">Tumik CMS</a>
      <div class="flex items-center gap-3">
        <a href="/" class="text-sm text-gray-700 dark:text-slate-300 hover:text-brand">Domů</a>
        <button id="themeToggle" data-theme-toggle class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-gray-700 dark:text-slate-300" aria-label="Přepnout režim">
          <svg id="iconSun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
          <svg id="iconMoon" class="hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
      </div>
    </div>
  </nav>
  <div class="flex items-center justify-center pt-24">
  <form method="post" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-6 rounded w-full max-w-sm">
    <h1 class="text-xl font-semibold mb-4">Administrace</h1>
    <?php if ($error): ?>
      <div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <label class="block mb-2 text-sm text-gray-800 dark:text-slate-200">Uživatel</label>
    <input name="username" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 mb-4 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-400" required>
    <label class="block mb-2 text-sm text-gray-800 dark:text-slate-200">Heslo</label>
    <input type="password" name="password" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 mb-4 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-400" required>
    <button class="w-full bg-brand text-white py-2 rounded">Přihlásit</button>
    <div class="mt-3 flex justify-between text-sm">
      <a href="/forgot.php" class="text-brand">Zapomenuté heslo</a>
      <a href="/register.php" class="text-brand">Registrace</a>
    </div>
  </form>
  </div>
  <script>function syncIcons(){var d=document.documentElement.classList.contains('dark');var s=document.getElementById('iconSun');var m=document.getElementById('iconMoon');if(s&&m){s.classList.toggle('hidden', d);m.classList.toggle('hidden', !d);}}syncIcons();var b=document.getElementById('themeToggle');if(b){b.addEventListener('click',function(){var d=!document.documentElement.classList.contains('dark');document.documentElement.classList.toggle('dark', d);localStorage.setItem('theme',d?'dark':'light');syncIcons();});}}</script>
</body>
</html>
