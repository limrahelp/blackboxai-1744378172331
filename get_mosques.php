<?php
require_once 'db.php';

header('Content-Type: application/json');

// Validate input parameters
$lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
$lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);

if ($lat === false || $lng === false) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid coordinates provided. Both latitude and longitude are required.'
    ]);
    exit;
}

// Validate coordinate ranges
if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid coordinate values. Latitude must be between -90 and 90, longitude between -180 and 180.'
    ]);
    exit;
}

try {
    $db = new Database();
    // Get mosques within 10km radius (can be adjusted as needed)
    $mosques = $db->getMosquesByLocation($lat, $lng, 10);
    
    if (isset($mosques['error'])) {
        throw new Exception($mosques['error']);
    }
    
    echo json_encode($mosques);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
