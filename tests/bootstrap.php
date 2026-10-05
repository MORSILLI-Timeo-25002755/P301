<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Stub template helper functions normally defined in public/index.php
if (!function_exists('begin_page')) {
    function begin_page($title = '', $style = '', $navbar = true): void
    {
    }
}

if (!function_exists('end_page')) {
    function end_page(): void
    {
    }
}

// Enable testing mode in DatabaseController
\Controllers\DatabaseController::$isTesting = true;

// Default test environment variables
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? '127.0.0.1';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? 'test_user';
$_ENV['DB_PASSWORD'] = $_ENV['DB_PASSWORD'] ?? 'test_password';
$_ENV['DB_DBNAME'] = $_ENV['DB_DBNAME'] ?? 'test_db';
$_ENV['APP_URL'] = $_ENV['APP_URL'] ?? 'http://localhost';

$_SERVER['MAIL_HOST'] = $_SERVER['MAIL_HOST'] ?? 'localhost';
$_SERVER['MAIL_ADDRESS'] = $_SERVER['MAIL_ADDRESS'] ?? 'test@example.com';
$_SERVER['MAIL_PASSWORD'] = $_SERVER['MAIL_PASSWORD'] ?? 'secret';
$_SERVER['SMTP_PORT'] = $_SERVER['SMTP_PORT'] ?? 587;
