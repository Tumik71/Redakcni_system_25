<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Util.php';
require_once __DIR__ . '/../src/Theme.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database; use Tumik\CMS\Util;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('editor');
$pdo = Database::conn();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page = null;
if ($id) {
    $st = $pdo->prepare('SELECT * FROM pages WHERE id = ?');
    $st->execute([$id]);
    $page = $st->fetch();
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $slug = trim($_POST['slug'] ?? '');
    if ($slug === '') { $slug = Util::slug($title); }
    if ($title === '') { $error = 'Vyplňte název'; }
    if (!$error) {
        if ($id) {
            $st = $pdo->prepare('UPDATE pages SET title=?, slug=?, content=? WHERE id=?');
            $st->execute([$title,$slug,$content,$id]);
        } else {
            $st = $pdo->prepare('INSERT INTO pages (title, slug, content) VALUES (?,?,?)');
            $st->execute([$title,$slug,$content]);
            $id = (int)$pdo->lastInsertId();
        }
        header('Location: /admin/pages.php'); exit;
    }
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Upravit stránku</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{brand:{DEFAULT:'#0ea5e9',dark:'#0369a1'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui']}}}}</script>
  <?php \Tumik\CMS\Theme::injectHead(); ?>
</head>
<body class="bg-white dark:bg-slate-900 font-sans font-light">
  <div class="max-w-3xl mx-auto p-6 pt-12">
    <h1 class="text-xl font-semibold mb-4"><?= $id ? 'Upravit stránku' : 'Nová stránka' ?></h1>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" class="space-y-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 rounded">
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Název</label>
        <input name="title" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100" value="<?= htmlspecialchars($page['title'] ?? '') ?>" required>
      </div>
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Slug</label>
        <input name="slug" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100" value="<?= htmlspecialchars($page['slug'] ?? '') ?>">
      </div>
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Obsah (HTML povolen)</label>
        <textarea name="content" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 h-64 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
      </div>
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded p-3">
        <div class="text-sm mb-2">Vložit médium</div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <?php $m = $pdo->query("SELECT id, path, mime FROM media ORDER BY created_at DESC LIMIT 8")->fetchAll(); foreach ($m as $mi): ?>
            <div class="border border-slate-200 dark:border-slate-700 rounded p-2 text-center">
              <?php if (str_starts_with($mi['mime'],'image/')): ?>
                <img src="<?= htmlspecialchars($mi['path']) ?>" class="h-16 w-full object-cover rounded mb-2">
                <button type="button" class="text-blue-600 text-sm" onclick="insertTag('<img src=\'<?= htmlspecialchars($mi['path']) ?>\' alt=\''\'>')">Vložit</button>
              <?php else: ?>
                <button type="button" class="text-blue-600 text-sm" onclick="insertTag('<a href=\'<?= htmlspecialchars($mi['path']) ?>\'>Soubor</a>')">Vložit odkaz</button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="flex gap-2">
        <button class="bg-brand text-white px-3 py-2 rounded">Uložit</button>
        <a href="/admin/pages.php" class="px-3 py-2 rounded border">Zpět</a>
      </div>
    </form>
  <script>
    function insertTag(tag){
      var ta = document.querySelector('textarea[name="content"]');
      if(!ta) return; var start = ta.selectionStart, end = ta.selectionEnd;
      var text = ta.value; ta.value = text.substring(0,start) + tag + text.substring(end);
      ta.focus(); ta.selectionStart = ta.selectionEnd = start + tag.length;
    }
  </script>
  </div>
</body>
</html>
