<?php
/**
 * Bootstrap for every public page and the admin panel.
 * Loads config, opens the database, starts the session and exposes helpers.
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

if (!is_file(__DIR__ . '/config.php')) {
    http_response_code(500);
    exit('Missing includes/config.php — copy includes/config.sample.php and fill in your database details.');
}
require __DIR__ . '/config.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}
date_default_timezone_set('America/Toronto');
mb_internal_encoding('UTF-8');

require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';
require __DIR__ . '/sms.php';
require __DIR__ . '/recaptcha.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('lah_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

try {
    db();
} catch (PDOException $ex) {
    http_response_code(500);
    exit('Database connection failed' . (APP_DEBUG ? ': ' . e($ex->getMessage()) : '.') . ' Import sql/schema.sql and sql/seed.sql first.');
}
