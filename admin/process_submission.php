<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Get and validate input
$submission_id = filter_input(INPUT_POST, 'submission_id', FILTER_VALIDATE_INT);
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

if (!$submission_id || !in_array($action, ['approve', 'reject'])) {
    $_SESSION['error_message'] = 'Invalid request parameters.';
    header('Location: index.php');
    exit;
}

try {
    $db = new Database();
    
    if ($action === 'approve') {
        $result = $db->approveMosque($submission_id);
        if (isset($result['error'])) {
            throw new Exception($result['error']);
        }
        $_SESSION['success_message'] = 'Submission approved successfully.';
    } else {
        $result = $db->rejectSubmission($submission_id);
        if (isset($result['error'])) {
            throw new Exception($result['error']);
        }
        $_SESSION['success_message'] = 'Submission rejected successfully.';
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = 'Error processing submission: ' . $e->getMessage();
}

header('Location: index.php');
exit;
