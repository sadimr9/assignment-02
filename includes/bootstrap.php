<?php

declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/helpers.php';

if (!is_installed()) {
    redirect('install.php');
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/Sections.php';

$config = require config_file_path();
$pdo = Database::connect($config);
$users = new UserRepository($pdo);
$auth = new AuthService($users);
