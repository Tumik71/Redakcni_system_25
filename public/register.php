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
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
  <div class="max-w-md mx-auto p-6">
    <h1 class="text-xl font-semibold mb-4">Registrace</h1>
    <?php if ($error): ?><div class="mb-3 p-3 bg-red-50 text-red-700 rounded"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($ok): ?><div class="mb-3 p-3 bg-green-50 text-green-700 rounded"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm mb-1">Uživatelské jméno</label>
        <input name="username" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Email</label>
        <input type="email" name="email" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block text-sm mb-1">Heslo</label>
        <input type="password" name="password" class="w-full border rounded p-2" required>
      </div>
      <button class="bg-blue-600 text-white px-3 py-2 rounded">Registrovat</button>
    </form>
  </div>
</body>
</html>
