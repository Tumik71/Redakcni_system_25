<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Util.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database; use Tumik\CMS\Util;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('editor');
$pdo = Database::conn();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
if ($id) {
    $st = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $st->execute([$id]);
    $post = $st->fetch();
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $published = isset($_POST['published']) ? 1 : 0;
    $slug = trim($_POST['slug'] ?? '');
    if ($slug === '') { $slug = Util::slug($title); }
    if ($title === '') { $error = 'Vyplňte název'; }
    if (!$error) {
        if ($id) {
            $st = $pdo->prepare('UPDATE posts SET title=?, slug=?, content=?, published=? WHERE id=?');
            $st->execute([$title,$slug,$content,$published,$id]);
        } else {
            $st = $pdo->prepare('INSERT INTO posts (title, slug, content, published) VALUES (?,?,?,?)');
            $st->execute([$title,$slug,$content,$published]);
            $id = (int)$pdo->lastInsertId();
        }
        header('Location: /admin/posts.php'); exit;
    }
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Upravit článek</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-xl font-semibold mb-4"><?= $id ? 'Upravit článek' : 'Nový článek' ?></h1>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm mb-1">Název</label>
        <input name="title" class="w-full border rounded p-2" value="<?= htmlspecialchars($post['title'] ?? '') ?>" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Slug</label>
        <input name="slug" class="w-full border rounded p-2" value="<?= htmlspecialchars($post['slug'] ?? '') ?>">
      </div>
      <div>
        <label class="block text-sm mb-1">Obsah (HTML povolen)</label>
        <textarea name="content" class="w-full border rounded p-2 h-64"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
      </div>
      <label class="inline-flex items-center space-x-2"><input type="checkbox" name="published" <?= (isset($post['published']) && $post['published']) ? 'checked' : '' ?>><span>Publikovat</span></label>
      <div class="flex gap-2">
        <button class="bg-blue-600 text-white px-3 py-2 rounded">Uložit</button>
        <a href="/admin/posts.php" class="px-3 py-2 rounded border">Zpět</a>
      </div>
    </form>
  </div>
</body>
</html>
