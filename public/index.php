<?php
/**
 * CampusConnect - Front Controller
 * All requests are routed here by public/.htaccess
 */

// Security headers - sent on every response
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'");
header('Referrer-Policy: strict-origin-when-cross-origin');

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/core/Middleware.php';
require_once dirname(__DIR__) . '/core/App.php';

spl_autoload_register(function ($class) {
    $paths = [
        dirname(__DIR__) . '/app/models/' . $class . '.php',
        dirname(__DIR__) . '/app/controllers/' . $class . '.php',
        dirname(__DIR__) . '/core/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

$app = new App();
