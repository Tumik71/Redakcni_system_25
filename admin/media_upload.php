<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Media.php';
require_once __DIR__ . '/../src/Settings.php';

use Tumik\CMS\Auth; use Tumik\CMS\Database; use Tumik\CMS\Media; use Tumik\CMS\Settings;

if (!Auth::check()) { header('Location: /admin/index.php'); exit; }
Auth::requireRole('editor');
$pdo = Database::conn();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = isset($_GET['ajax']);
    try {
        $info = Media::saveUpload($_FILES['file'] ?? []);
        $st = $pdo->prepare('INSERT INTO media (filename, path, mime, size) VALUES (?,?,?,?)');
        $st->execute([$info['filename'],$info['path'],$info['mime'],$info['size']]);
        if ($isAjax) { header('Content-Type: application/json; charset=utf-8'); echo json_encode(['ok'=>true,'path'=>$info['path']]); exit; }
        header('Location: /admin/media.php'); exit;
    } catch (\\Throwable $e) {
        if ($isAjax) { header('Content-Type: application/json; charset=utf-8'); http_response_code(400); echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); exit; }
        $error = 'Chyba nahrávání: ' . $e->getMessage();
    }
}
$allowed = Settings::get('media_allowed_mime','image/jpeg,image/png,image/gif,image/webp,application/pdf');
$maxMb = (int)Settings::get('media_max_upload_mb','10');
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nahrát médium</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-md mx-auto p-6">
    <h1 class="text-xl font-semibold mb-4">Nahrát soubor</h1>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form id="uploadForm" method="post" enctype="multipart/form-data" class="space-y-4">
      <input id="fileInput" type="file" name="file" accept="image/*,application/pdf" required class="w-full border rounded p-2">
      <div class="text-xs text-gray-600">Povolené typy: <?= htmlspecialchars($allowed) ?>, max <?= htmlspecialchars($maxMb) ?> MB</div>
      <div class="w-full h-2 bg-gray-200 rounded"><div id="bar" class="h-2 bg-blue-600 rounded" style="width:0%"></div></div>
      <div id="msg" class="text-sm"></div>
      <button id="btnUpload" class="bg-blue-600 text-white px-3 py-2 rounded">Nahrát</button>
      <a href="/admin/media.php" class="px-3 py-2 rounded border">Zpět</a>
    </form>
    <script>
      const allowed = "<?= htmlspecialchars($allowed, ENT_QUOTES) ?>".split(',').map(s=>s.trim());
      const maxMb = <?= (int)$maxMb ?>;
      const form = document.getElementById('uploadForm');
      const fileInput = document.getElementById('fileInput');
      const bar = document.getElementById('bar');
      const msg = document.getElementById('msg');
      form.addEventListener('submit', function(e){
        e.preventDefault();
        const f = fileInput.files[0];
        if(!f){ msg.textContent='Vyberte soubor'; msg.className='text-sm text-red-600'; return; }
        if(allowed.length && !allowed.includes(f.type)){ msg.textContent='Nepovolený typ souboru'; msg.className='text-sm text-red-600'; return; }
        if(maxMb>0 && f.size > maxMb*1024*1024){ msg.textContent='Soubor je příliš velký'; msg.className='text-sm text-red-600'; return; }
        const xhr = new XMLHttpRequest();
        xhr.upload.onprogress = function(e){ if(e.lengthComputable){ bar.style.width = (e.loaded/e.total*100)+'%'; } };
        xhr.onreadystatechange = function(){ if(xhr.readyState===4){ if(xhr.status===200){ msg.textContent='Hotovo'; msg.className='text-sm text-green-600'; window.location.href='/admin/media.php'; } else { try{ const r=JSON.parse(xhr.responseText); msg.textContent='Chyba: '+(r.error||'neznámá'); }catch(_){ msg.textContent='Chyba nahrávání'; } msg.className='text-sm text-red-600'; } } };
        const fd = new FormData(form);
        xhr.open('POST','/admin/media_upload.php?ajax=1');
        xhr.send(fd);
      });
    </script>
  </div>
</body>
</html>
