<?php
require_once __DIR__ . '/../src/Env.php';

use Tumik\CMS\Env;

Env::load(__DIR__ . '/../.env');

// Session settings
$sessionName = Env::get('SESSION_NAME', 'tumik_session');
session_name($sessionName);
session_start([
    'cookie_secure' => Env::get('SESSION_SECURE', 'true') === 'true',
    'cookie_httponly' => Env::get('SESSION_HTTP_ONLY', 'true') === 'true',
]);

// Constants
define('APP_URL', Env::get('APP_URL', ''));
