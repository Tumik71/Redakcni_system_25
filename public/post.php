<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';

use Tumik\CMS\Database;

$slug = $_GET['slug'] ?? '';
$pdo = Database::conn();
$stmt = $pdo->prepare('SELECT title, content, created_at FROM posts WHERE slug = ? AND published = 1 LIMIT 1');
$stmt->execute([$slug]);
$post = $stmt->fetch();
if (!$post) { http_response_code(404); echo 'Nenalezeno'; exit; }
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($post['title']) ?> - Tumik.cz</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <main class="max-w-3xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($post['title']) ?></h1>
    <article class="prose max-w-none bg-white p-6 rounded border">
      <?= $post['content'] ?>
    </article>
  </main>
</body>
</html>
