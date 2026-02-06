<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/helpers.php';

session_start();

if (!is_installed()) {
    redirect('install.php');
}

if (isset($_SESSION['user_id']) && is_int($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

redirect('login.php');
