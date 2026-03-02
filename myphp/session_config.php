<?php
// session_config.php

$sessionPath = __DIR__ . '/sessions';

if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0700, true);
}

if (session_status() === PHP_SESSION_NONE) {
    session_save_path($sessionPath);
    session_start();
}