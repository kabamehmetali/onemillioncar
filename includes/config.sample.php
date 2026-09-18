<?php
/**
 * Copy this file to includes/config.php and fill in your credentials.
 * config.php is git-ignored so local and production settings never collide.
 */
declare(strict_types=1);

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'onemillioncar_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Only used when the base path cannot be detected from the request.
define('BASE_URL', '/');

// Set to false if the host ignores .htaccess (links then use real .php endpoints).
define('CLEAN_URLS', true);

// Show PHP errors on screen. Keep false in production.
define('APP_DEBUG', true);
