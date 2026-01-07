<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Activation.php';
require_once __DIR__ . '/../src/Mail.php';

use Tumik\CMS\Database; use Tumik\CMS\Activation; use Tumik\CMS\Mail; use Tumik\CMS\Env;

$pdo = Database::conn();
$error = null; $ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username === '' || $email === '' || $password === '') { $error = 'Vyplňte všechna pole'; }
    else {
        $exists = $pdo->prepare('SELECT 1 FROM users WHERE username=? OR email=? LIMIT 1');
        $exists->execute([$username,$email]);
        if ($exists->fetch()) { $error = 'Uživatel nebo email již existuje'; }
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (username,email,role,active,password_hash) VALUES (?,?,?,?,?)')
                ->execute([$username,$email,'author',0,$hash]);
            $uid = (int)$pdo->lastInsertId();
            $token = Activation::create($uid);
            $link = rtrim(Env::get('APP_URL',''),'/') . '/activate.php?token=' . urlencode($token);
            $sent = Mail::send($email, 'Aktivace účtu', '<p>Dobrý den,</p><p>aktivujte prosím svůj účet kliknutím na odkaz:</p><p><a href="'.$link.'">'.$link.'</a></p>');
            $ok = $sent ? 'Registrace proběhla, zkontrolujte email pro aktivaci.' : 'Registrace proběhla, ale odeslání emailu se nezdařilo. Kontaktujte administrátora.';
        }
    }
}
?>
<!doctype html>
<html lang="cs">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrace</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{DEFAULT:'#0ea5e9',dark:'#0369a1'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui']}}}}</script>
  <script>(function(){var t=localStorage.getItem('theme');var d=t==='dark';document.documentElement.classList.toggle('dark', d);})();</script>
</head>
<body class="bg-white dark:bg-slate-900 font-sans font-light">
  <nav class="fixed top-0 inset-x-0 z-30 backdrop-blur bg-white/70 dark:bg-slate-900/70 border-b border-slate-200 dark:border-slate-700">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
      <a href="/" class="font-semibold tracking-tight">Tumik CMS</a>
      <div class="flex items-center gap-3">
        <a href="/" class="text-sm text-gray-700 dark:text-slate-300 hover:text-brand">Domů</a>
        <button id="themeToggle" class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-gray-700 dark:text-slate-300">
          <svg id="iconSun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
          <svg id="iconMoon" class="hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
      </div>
    </div>
  </nav>
  <div class="max-w-md mx-auto p-6 pt-24">
    <h1 class="text-2xl font-semibold mb-4">Registrace</h1>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="mb-3 p-3 bg-green-50 text-green-700 rounded"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <form method="post" class="space-y-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 rounded">
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Uživatelské jméno</label>
        <input name="username" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-400" required>
      </div>
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Email</label>
        <input type="email" name="email" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-400" required>
      </div>
      <div>
        <label class="block text-sm mb-1 text-gray-800 dark:text-slate-200">Heslo</label>
        <input type="password" name="password" class="w-full border border-slate-200 dark:border-slate-600 rounded p-2 bg-white dark:bg-slate-800 text-gray-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-400" required>
      </div>
      <div class="flex gap-3">
        <button class="bg-brand hover:bg-brand/dark text-white px-3 py-2 rounded">Registrovat</button>
        <a href="/admin/" class="px-3 py-2 rounded border">Přihlásit se</a>
      </div>
    </form>
  </div>
  <script>function syncIcons(){var d=document.documentElement.classList.contains('dark');var s=document.getElementById('iconSun');var m=document.getElementById('iconMoon');if(s&&m){s.classList.toggle('hidden', d);m.classList.toggle('hidden', !d);}}syncIcons();var b=document.getElementById('themeToggle');if(b){b.addEventListener('click',function(){var d=!document.documentElement.classList.contains('dark');document.documentElement.classList.toggle('dark', d);localStorage.setItem('theme',d?'dark':'light');syncIcons();});}</script>
</body>
</html>
