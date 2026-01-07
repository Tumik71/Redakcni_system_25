<?php
session_start();
require_once __DIR__ . '/../src/Env.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Installer.php';

use Tumik\CMS\Env; use Tumik\CMS\Database; use Tumik\CMS\Installer;

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$data = $_SESSION['install'] ?? [];
$errorMsg = null;

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function phpExt($ext){ return extension_loaded($ext); }
function writeEnv($pairs){
    $buf = '';
    foreach ($pairs as $k=>$v){ $buf .= $k.'='.$v."\n"; }
    file_put_contents(__DIR__.'/../.env', $buf);
}
function runSqlFile($path, $pdo){
    $sql = file_get_contents($path);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt){
        if ($stmt) { $pdo->exec($stmt); }
    }
}

function ensureUploads($relative){
    $base = realpath(__DIR__ . '/../');
    $path = $base . DIRECTORY_SEPARATOR . str_replace(['\\'], DIRECTORY_SEPARATOR, $relative);
    if (!is_dir($path)) { @mkdir($path, 0755, true); }
    $testFile = $path . DIRECTORY_SEPARATOR . '.write_test';
    $ok = @file_put_contents($testFile, 'ok') !== false;
    if ($ok) { @unlink($testFile); }
    return $ok;
}

function writeHtaccess($publicDir){
    $file = rtrim($publicDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.htaccess';
    if (is_file($file)) { return true; }
    $content = "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^ index.php [L]\n</IfModule>\n\nRedirectMatch 403 ^/(config|src)/\n";
    return file_put_contents($file, $content) !== false;
}

if ($step === 4 && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $data['APP_URL'] = trim($_POST['app_url'] ?? '');
    $data['DB_HOST'] = trim($_POST['db_host'] ?? 'localhost');
    $data['DB_PORT'] = trim($_POST['db_port'] ?? '3306');
    $data['DB_NAME'] = trim($_POST['db_name'] ?? '');
    $data['DB_USER'] = trim($_POST['db_user'] ?? '');
    $data['DB_PASS'] = trim($_POST['db_pass'] ?? '');
    $data['MAIL_FROM'] = trim($_POST['mail_from'] ?? 'noreply@tumik.cz');
    $data['MAIL_FROM_NAME'] = trim($_POST['mail_from_name'] ?? 'Tumik CMS');
    $data['UPLOAD_DIR'] = trim($_POST['upload_dir'] ?? 'public/uploads');
    $_SESSION['install'] = $data;
    header('Location: ?step=5'); exit;
}

if ($step === 6 && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminPass = trim($_POST['admin_pass'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $demo = isset($_POST['demo']) ? 1 : 0;
    $pairs = [
        'APP_ENV' => 'production',
        'APP_URL' => $data['APP_URL'] ?? '',
        'DB_HOST' => $data['DB_HOST'] ?? 'localhost',
        'DB_PORT' => $data['DB_PORT'] ?? '3306',
        'DB_NAME' => $data['DB_NAME'] ?? '',
        'DB_USER' => $data['DB_USER'] ?? '',
        'DB_PASS' => $data['DB_PASS'] ?? '',
        'SESSION_NAME' => 'tumik_session',
        'SESSION_SECURE' => 'true',
        'SESSION_HTTP_ONLY' => 'true',
        'ADMIN_DEFAULT_USER' => $adminUser,
        'ADMIN_DEFAULT_PASS' => $adminPass ?: 'change_me_123',
        'MAIL_FROM' => $data['MAIL_FROM'] ?? 'noreply@tumik.cz',
        'MAIL_FROM_NAME' => $data['MAIL_FROM_NAME'] ?? 'Tumik CMS',
        'UPLOAD_DIR' => $data['UPLOAD_DIR'] ?? 'public/uploads',
    ];
    try {
        writeEnv($pairs);
        Env::load(__DIR__ . '/../.env');
        $pdo = Database::conn();
        runSqlFile(__DIR__.'/../db/schema.sql', $pdo);
        if (is_file(__DIR__.'/../db/migrations/001_add_email_and_password_resets.sql')) {
            runSqlFile(__DIR__.'/../db/migrations/001_add_email_and_password_resets.sql', $pdo);
        }
        if (is_file(__DIR__.'/../db/migrations/002_user_activations.sql')) {
            runSqlFile(__DIR__.'/../db/migrations/002_user_activations.sql', $pdo);
        }
        if (is_file(__DIR__.'/../db/migrations/003_settings.sql')) {
            runSqlFile(__DIR__.'/../db/migrations/003_settings.sql', $pdo);
        }
        if ($demo && is_file(__DIR__.'/../db/seed.sql')) {
            runSqlFile(__DIR__.'/../db/seed.sql', $pdo);
        }
        ensureUploads($pairs['UPLOAD_DIR']);
        writeHtaccess(__DIR__.'/../public');
        $hash = password_hash($pairs['ADMIN_DEFAULT_PASS'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username,email,role,active,password_hash) VALUES (?,?,?,?,?)');
        $stmt->execute([$pairs['ADMIN_DEFAULT_USER'],$adminEmail,'admin',1,$hash]);
        $_SESSION['install_done'] = true;
        header('Location: ?step=7'); exit;
    } catch (\Throwable $e) {
        $errorMsg = 'Instalace se nezdařila: ' . $e->getMessage();
        $step = 6;
    }
}

?><!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Instalace Tumik CMS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Instalace Tumik CMS</h1>
    <?php if (Installer::isInstalled() && $step === 1): ?>
      <div class="mb-4 p-3 bg-green-50 text-green-700 rounded">Systém je již nainstalován.</div>
      <a class="text-blue-600" href="/">Přejít na web</a>
    <?php endif; ?>
    <?php if ($step === 1): ?>
      <div class="space-y-4">
        <p>Vítá vás průvodce instalací. Budeme potřebovat údaje k databázi, URL webu a administrátora.</p>
        <a href="?step=2" class="bg-blue-600 text-white px-3 py-2 rounded">Začít</a>
      </div>
    <?php elseif ($step === 2): ?>
      <div class="space-y-2">
        <div class="p-3 bg-white rounded border">PHP: <?= h(PHP_VERSION) ?></div>
        <div class="p-3 bg-white rounded border">PDO MySQL: <?= phpExt('pdo_mysql') ? 'OK' : 'Chybí' ?></div>
        <div class="p-3 bg-white rounded border">OpenSSL: <?= phpExt('openssl') ? 'OK' : 'Chybí' ?></div>
        <a href="?step=3" class="bg-blue-600 text-white px-3 py-2 rounded">Pokračovat</a>
      </div>
    <?php elseif ($step === 3): ?>
      <div class="space-y-2">
        <p>Zkontrolujte, že máte vytvořenou DB a uživatele v ISPConfig.</p>
        <a href="?step=4" class="bg-blue-600 text-white px-3 py-2 rounded">Pokračovat na konfiguraci</a>
      </div>
    <?php elseif ($step === 4): ?>
      <form method="post" class="space-y-4">
        <div>
          <label class="block text-sm mb-1">URL webu</label>
          <input name="app_url" class="w-full border rounded p-2" value="<?= h($data['APP_URL'] ?? 'https://tumik.cz') ?>" required>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm mb-1">DB host</label>
            <input name="db_host" class="w-full border rounded p-2" value="<?= h($data['DB_HOST'] ?? 'localhost') ?>" required>
          </div>
          <div>
            <label class="block text-sm mb-1">Port</label>
            <input name="db_port" class="w-full border rounded p-2" value="<?= h($data['DB_PORT'] ?? '3306') ?>" required>
          </div>
        </div>
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-sm mb-1">DB název</label>
            <input name="db_name" class="w-full border rounded p-2" value="<?= h($data['DB_NAME'] ?? '') ?>" required>
          </div>
          <div>
            <label class="block text-sm mb-1">DB uživatel</label>
            <input name="db_user" class="w-full border rounded p-2" value="<?= h($data['DB_USER'] ?? '') ?>" required>
          </div>
          <div>
            <label class="block text-sm mb-1">DB heslo</label>
            <input type="password" name="db_pass" class="w-full border rounded p-2" value="<?= h($data['DB_PASS'] ?? '') ?>" required>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm mb-1">E-mail odesílatele</label>
            <input name="mail_from" class="w-full border rounded p-2" value="<?= h($data['MAIL_FROM'] ?? 'noreply@tumik.cz') ?>" required>
          </div>
          <div>
            <label class="block text-sm mb-1">Název odesílatele</label>
            <input name="mail_from_name" class="w-full border rounded p-2" value="<?= h($data['MAIL_FROM_NAME'] ?? 'Tumik CMS') ?>" required>
          </div>
        </div>
        <div>
          <label class="block text-sm mb-1">Složka pro upload médií</label>
          <input name="upload_dir" class="w-full border rounded p-2" value="<?= h($data['UPLOAD_DIR'] ?? 'public/uploads') ?>" required>
          <div class="text-xs text-gray-500 mt-1">Relativně k kořeni projektu. Bude vytvořena a otestována pro zápis.</div>
        </div>
        <button class="bg-blue-600 text-white px-3 py-2 rounded">Uložit a pokračovat</button>
      </form>
    <?php elseif ($step === 5): ?>
      <form method="post" action="?step=6" class="space-y-4">
        <div>
          <label class="block text-sm mb-1">Admin uživatel</label>
          <input name="admin_user" class="w-full border rounded p-2" value="admin" required>
        </div>
        <div>
          <label class="block text-sm mb-1">Admin email</label>
          <input type="email" name="admin_email" class="w-full border rounded p-2">
        </div>
        <div>
          <label class="block text-sm mb-1">Admin heslo</label>
          <input type="password" name="admin_pass" class="w-full border rounded p-2" required>
        </div>
        <label class="inline-flex items-center space-x-2"><input type="checkbox" name="demo"><span>Přidat demo obsah (ukázkový admin a články)</span></label>
        <button class="bg-blue-600 text-white px-3 py-2 rounded">Instalovat</button>
      </form>
    <?php elseif ($step === 6): ?>
      <?php if ($errorMsg): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($errorMsg) ?></div><?php endif; ?>
      <a href="?step=5" class="bg-blue-600 text-white px-3 py-2 rounded">Zpět na nastavení admina</a>
    <?php elseif ($step === 7): ?>
      <div class="p-3 bg-green-50 text-green-700 rounded">Instalace dokončena.</div>
      <div class="mt-3 space-x-3">
        <a href="/" class="text-blue-600">Přejít na web</a>
        <a href="/admin/" class="text-blue-600">Přejít do administrace</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
