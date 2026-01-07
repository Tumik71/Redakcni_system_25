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
</head>
<body class="bg-white text-ink">
  <nav class="fixed top-0 inset-x-0 z-30 backdrop-blur bg-white/70 border-b">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
      <a href="/" class="font-semibold tracking-tight">Tumik CMS</a>
      <div class="flex items-center gap-3">
        <a href="/register.php" class="text-sm text-gray-700 hover:text-brand">Registrace</a>
        <a href="/admin/" class="px-3 py-1.5 rounded bg-brand text-white text-sm">Administrace</a>
      </div>
    </div>
  </nav>
  <header class="relative pt-24">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand/10 via-white to-brand/5"></div>
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-8 items-center">
      <div class="space-y-4">
        <h1 class="text-4xl md:text-5xl font-bold">Moderní, rychlé a jednoduché CMS</h1>
        <p class="text-lg text-gray-700">Instalátor, administrace, API, média a CI/CD. Vše připraveno pro snadné nasazení na ISPConfig.</p>
        <div class="flex gap-3">
          <a href="/admin/" class="px-4 py-2 rounded bg-brand hover:bg-brand/dark text-white">Přejít do administrace</a>
          <a href="/api/posts.php" class="px-4 py-2 rounded border">Zobrazit API</a>
        </div>
        <div class="grid grid-cols-3 gap-4 pt-2">
          <div class="p-4 rounded border bg-white"><div class="text-2xl font-bold"><?= $stats['posts'] ?></div><div class="text-xs text-gray-600">Publikované články</div></div>
          <div class="p-4 rounded border bg-white"><div class="text-2xl font-bold"><?= $stats['pages'] ?></div><div class="text-xs text-gray-600">Stránky</div></div>
          <div class="p-4 rounded border bg-white"><div class="text-2xl font-bold"><?= $stats['media'] ?></div><div class="text-xs text-gray-600">Média</div></div>
        </div>
      </div>
      <div class="relative rounded-xl overflow-hidden ring-1 ring-gray-200">
        <div class="bg-gradient-to-br from-brand to-brand/dark h-40"></div>
        <div class="p-6 bg-white">
          <ul class="grid grid-cols-2 gap-3 text-sm">
            <li class="p-3 rounded border">Instalační průvodce</li>
            <li class="p-3 rounded border">Role a oprávnění</li>
            <li class="p-3 rounded border">Správa médií</li>
            <li class="p-3 rounded border">Veřejné JSON API</li>
            <li class="p-3 rounded border">CI/CD přes GitHub</li>
            <li class="p-3 rounded border">Responzivní UI</li>
          </ul>
        </div>
      </div>
    </div>
  </header>
  <section class="max-w-6xl mx-auto px-6 py-12">
    <h2 class="text-2xl font-semibold mb-6">Poslední články</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($posts as $p): ?>
        <a href="/post.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="group block rounded-xl border bg-white hover:shadow-lg transition">
          <div class="p-5">
            <div class="text-sm text-gray-500 mb-1"><?= htmlspecialchars(date('j.n.Y', strtotime($p['created_at']))) ?></div>
            <div class="font-semibold text-lg mb-2 group-hover:text-brand"><?= htmlspecialchars($p['title']) ?></div>
            <div class="text-sm text-gray-700 line-clamp-3"><?= htmlspecialchars(strip_tags($p['content'])) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="max-w-6xl mx-auto px-6 pb-16">
    <div class="rounded-xl border p-6 bg-white flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <div class="font-semibold">Začni hned</div>
        <div class="text-sm text-gray-600">Vytvoř účet, přihlaš se do administrace a publikuj první článek.</div>
      </div>
      <div class="flex gap-3">
        <a href="/register.php" class="px-4 py-2 rounded border">Registrace</a>
        <a href="/admin/" class="px-4 py-2 rounded bg-brand text-white">Administrace</a>
      </div>
    </div>
  </section>
  <footer class="border-t">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between text-sm text-gray-600">
      <div>© <?= date('Y') ?> Tumik CMS</div>
      <div class="flex gap-4">
        <a href="/api/pages.php">API stránky</a>
        <a href="/api/posts.php">API články</a>
      </div>
    </div>
  </footer>
</body>
</html>
