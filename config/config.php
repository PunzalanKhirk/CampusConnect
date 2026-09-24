<?php
/**
 * CampusConnect - Global Configuration
 * -------------------------------------------------
 * Localhost / XAMPP defaults. When you move to staging or production,
 * override these via environment variables instead of editing this file.
 */

// ---- Database (XAMPP defaults: root / no password) ----
define('DB_HOST', getenv('CC_DB_HOST') ?: 'localhost');
define('DB_USER', getenv('CC_DB_USER') ?: 'root');
define('DB_PASS', getenv('CC_DB_PASS') ?: '');            // empty string = no password
define('DB_NAME', getenv('CC_DB_NAME') ?: 'campusconnect');
define('DB_CHARSET', 'utf8mb4');

// ---- Application URL root ----
// Change 'CampusConnect' below to '' if this app lives at the htdocs root instead of a subfolder.
define('URLROOT', 'http://localhost/CampusConnect/public');
define('SITE_NAME', 'CampusConnect - Command Hub');
define('APP_ENV', getenv('CC_APP_ENV') ?: 'development'); // 'development' | 'production'

// ---- Upload settings ----
define('UPLOAD_DIR', dirname(__DIR__) . '/public/uploads/'); // absolute filesystem path
define('UPLOAD_URL', URLROOT . '/uploads/');
define('ALLOWED_UPLOAD_TYPES', ['pdf' => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'png'  => 'image/png']);
define('MAX_UPLOAD_BYTES', 10 * 1024 * 1024); // 10 MB

// ---- Security ----
// Generate your own random 32+ char string for production.
define('APP_KEY', getenv('CC_APP_KEY') ?: 'CHANGE_ME_32_CHAR_MIN_RANDOM_SECRET_KEY');
define('SESSION_NAME', 'campusconnect_session');
define('SESSION_LIFETIME', 60 * 60 * 2); // 2 hours, in seconds

// ---- Error reporting ----
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ---- Hardened session cookie params (must run before any session_start()) ----
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'domain'   => '',
        'secure'   => APP_ENV === 'production', // requires HTTPS in prod
        'httponly' => true,                     // JS cannot read the cookie
        'samesite' => 'Lax',
    ]);
}
