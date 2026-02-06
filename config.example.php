<?php

return [
    'app_name' => 'Profile Management',
    'driver' => 'sqlite',
    'sqlite' => [
        'path' => __DIR__ . '/storage/profile_management.db',
    ],
    'mysql' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'profile_management',
        'username' => 'root',
        'password' => '',
    ],
];
