<?php
session_start();
require_once '../config.php';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Get and validate input
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

if (!$username || !$password) {
    $_SESSION['login_error'] = 'Please provide both username and password.';
    header('Location: login.php');
    exit;
}

// Verify credentials
if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
    // Set session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    header('Location: index.php');
    exit;
} else {
    $_SESSION['login_error'] = 'Invalid username or password.';
    header('Location: login.php');
    exit;
}
