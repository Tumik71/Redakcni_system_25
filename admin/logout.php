<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Auth.php';

use Tumik\CMS\Auth;

Auth::logout();
header('Location: /admin/index.php');
