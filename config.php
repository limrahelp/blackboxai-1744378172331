<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mosque_db');

// Admin credentials
define('ADMIN_USERNAME', 'admin');
// Password: admin123 (hashed)
define('ADMIN_PASSWORD_HASH', '$2y$10$YourSecureHashHere');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// Start the session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone setting
date_default_timezone_set('UTC');
