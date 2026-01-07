<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Installer.php';

use Tumik\CMS\Database; use Tumik\CMS\Installer;

if (!Installer::isInstalled()) { header('Location: /install/?step=1'); exit; }

$pdo = Database::conn();
$posts = $pdo->query('SELECT id, title, slug, created_at, content FROM posts WHERE published = 1 ORDER BY created_at DESC LIMIT 6')->fetchAll();
$stats = [
  'posts' => (int)$pdo->query('SELECT COUNT(*) FROM posts WHERE published=1')->fetchColumn(),
  'pages' => (int)$pdo->query('SELECT COUNT(*) FROM pages')->fetchColumn(),
  'media' => (int)$pdo->query('SELECT COUNT(*) FROM media')->fetchColumn()
];
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tumik CMS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#0ea5e9',dark:'#0369a1'},ink:'#0f172a'}}}}</script>
  <script>(function(){var t=localStorage.getItem('theme');var d=t?t==='dark':window.matchMedia('(prefers-color-scheme: dark)').matches;if(d)document.documentElement.classList.add('dark');})();</script>
</head>
<body class="bg-white dark:bg-slate-900 text-ink dark:text-slate-100">
  <nav class="fixed top-0 inset-x-0 z-30 backdrop-blur bg-white/70 dark:bg-slate-900/70 border-b border-slate-200 dark:border-slate-700">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
      <a href="/" class="font-semibold tracking-tight">Tumik CMS</a>
      <div class="flex items-center gap-3">
        <button id="themeToggle" class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-gray-700 dark:text-slate-300">
          <svg class="block dark:hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
          <svg class="hidden dark:block" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
        <a href="/register.php" class="text-sm text-gray-700 dark:text-slate-300 hover:text-brand">Registrace</a>
        <a href="/admin/" class="px-3 py-1.5 rounded bg-brand text-white text-sm">Administrace</a>
      </div>
    </div>
  </nav>
  <header class="relative pt-24">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand/10 via-white to-brand/5 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800"></div>
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-8 items-center">
      <div class="space-y-4">
        <h1 class="text-4xl md:text-5xl font-bold">Moderní, rychlé a jednoduché CMS</h1>
        <p class="text-lg text-gray-700 dark:text-slate-300">Instalátor, administrace, API, média a CI/CD. Vše připraveno pro snadné nasazení na ISPConfig.</p>
        <div class="flex gap-3">
          <a href="/admin/" class="px-4 py-2 rounded bg-brand hover:bg-brand/dark text-white">Přejít do administrace</a>
          <a href="/api/posts.php" class="px-4 py-2 rounded border">Zobrazit API</a>
        </div>
        <div class="grid grid-cols-3 gap-4 pt-2">
          <div class="p-4 rounded border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700"><div class="text-2xl font-bold"><?= $stats['posts'] ?></div><div class="text-xs text-gray-600 dark:text-slate-400">Publikované články</div></div>
          <div class="p-4 rounded border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700"><div class="text-2xl font-bold"><?= $stats['pages'] ?></div><div class="text-xs text-gray-600 dark:text-slate-400">Stránky</div></div>
          <div class="p-4 rounded border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700"><div class="text-2xl font-bold"><?= $stats['media'] ?></div><div class="text-xs text-gray-600 dark:text-slate-400">Média</div></div>
        </div>
      </div>
      <div class="relative rounded-xl overflow-hidden ring-1 ring-gray-200 dark:ring-slate-700">
        <div class="bg-gradient-to-br from-brand to-brand/dark h-40"></div>
        <div class="p-6 bg-white dark:bg-slate-800">
          <ul class="grid grid-cols-2 gap-3 text-sm">
            <li class="p-3 rounded border border-slate-200 dark:border-slate-700">Instalační průvodce</li>
            <li class="p-3 rounded border border-slate-200 dark:border-slate-700">Role a oprávnění</li>
            <li class="p-3 rounded border border-slate-200 dark:border-slate-700">Správa médií</li>
            <li class="p-3 rounded border border-slate-200 dark:border-slate-700">Veřejné JSON API</li>
            <li class="p-3 rounded border border-slate-200 dark:border-slate-700">CI/CD přes GitHub</li>
            <li class="p-3 rounded border border-slate-200 dark:border-slate-700">Responzivní UI</li>
          </ul>
        </div>
      </div>
    </div>
  </header>
  <section class="max-w-6xl mx-auto px-6 py-12">
    <h2 class="text-2xl font-semibold mb-6">Poslední články</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($posts as $p): ?>
        <a href="/post.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="group block rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:shadow-lg transition">
          <div class="p-5">
            <div class="text-sm text-gray-500 dark:text-slate-400 mb-1"><?= htmlspecialchars(date('j.n.Y', strtotime($p['created_at']))) ?></div>
            <div class="font-semibold text-lg mb-2 group-hover:text-brand"><?= htmlspecialchars($p['title']) ?></div>
            <div class="text-sm text-gray-700 dark:text-slate-300 line-clamp-3"><?= htmlspecialchars(strip_tags($p['content'])) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="max-w-6xl mx-auto px-6 pb-16">
    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-6 bg-white dark:bg-slate-800 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <div class="font-semibold">Začni hned</div>
        <div class="text-sm text-gray-600 dark:text-slate-400">Vytvoř účet, přihlaš se do administrace a publikuj první článek.</div>
      </div>
      <div class="flex gap-3">
        <a href="/register.php" class="px-4 py-2 rounded border">Registrace</a>
        <a href="/admin/" class="px-4 py-2 rounded bg-brand text-white">Administrace</a>
      </div>
    </div>
  </section>
  <footer class="border-t border-slate-200 dark:border-slate-700">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between text-sm text-gray-600 dark:text-slate-400">
      <div>© <?= date('Y') ?> Tumik CMS</div>
      <div class="flex gap-4">
        <a href="/api/pages.php">API stránky</a>
        <a href="/api/posts.php">API články</a>
      </div>
    </div>
  </footer>
  <script>var b=document.getElementById('themeToggle');if(b){b.addEventListener('click',function(){var d=document.documentElement.classList.toggle('dark');localStorage.setItem('theme',d?'dark':'light');});}</script>
</body>
</html>
