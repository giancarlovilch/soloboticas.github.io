<?php

require_once __DIR__ . '/env.php';

return [
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', '3306'),
    'dbname' => env('DB_NAME', 'sb'),
    'username' => env('DB_USER'),
    'password' => env('DB_PASS'),
    'charset' => 'utf8mb4'
];