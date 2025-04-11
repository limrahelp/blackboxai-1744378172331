<?php
require_once 'db.php';

session_start();

// Function to validate time format
function isValidTime($time) {
    return preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $time);
}

// Function to validate coordinates
function isValidCoordinate($lat, $lng) {
    return is_numeric($lat) && is_numeric($lng) &&
           $lat >= -90 && $lat <= 90 &&
           $lng >= -180 && $lng <= 180;
}

try {
    $db = new Database();
    
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and sanitize input data
    $data = [
        'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
        'address' => filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING),
        'latitude' => filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT),
        'longitude' => filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT),
        'fajar' => filter_input(INPUT_POST, 'fajar', FILTER_SANITIZE_STRING),
        'zuhar' => filter_input(INPUT_POST, 'zuhar', FILTER_SANITIZE_STRING),
        'asar' => filter_input(INPUT_POST, 'asar', FILTER_SANITIZE_STRING),
        'maghrib' => filter_input(INPUT_POST, 'maghrib', FILTER_SANITIZE_STRING),
        'ishaa' => filter_input(INPUT_POST, 'ishaa', FILTER_SANITIZE_STRING),
        'juma' => filter_input(INPUT_POST, 'juma', FILTER_SANITIZE_STRING),
        'submission_type' => filter_input(INPUT_POST, 'submission_type', FILTER_SANITIZE_STRING),
        'mosque_id' => filter_input(INPUT_POST, 'mosque_id', FILTER_VALIDATE_INT)
    ];

    // Validate required fields
    foreach ($data as $key => $value) {
        if ($value === false || $value === null || $value === '') {
            if ($key !== 'mosque_id' || $data['submission_type'] === 'revision') {
                throw new Exception("Missing or invalid $key");
            }
        }
    }

    // Validate coordinates
    if (!isValidCoordinate($data['latitude'], $data['longitude'])) {
        throw new Exception('Invalid coordinates');
    }

    // Validate prayer times
    $timeFields = ['fajar', 'zuhar', 'asar', 'maghrib', 'ishaa', 'juma'];
    foreach ($timeFields as $field) {
        if (!isValidTime($data[$field])) {
            throw new Exception("Invalid time format for $field");
        }
    }

    // Handle delete action
    if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        $data['submission_type'] = 'delete';
    }

    // Process the submission
    $result = $db->addPendingSubmission($data);

    if (isset($result['error'])) {
        throw new Exception($result['error']);
    }

    // Set success message
    $_SESSION['success_message'] = 'Your submission has been received and is pending approval.';
    
    // Redirect back to appropriate page
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    
    // Redirect back with error
    $redirect_url = 'add_mosque.php';
    if (isset($data['mosque_id'])) {
        $redirect_url .= '?id=' . $data['mosque_id'];
    }
    header("Location: $redirect_url");
    exit;
}
