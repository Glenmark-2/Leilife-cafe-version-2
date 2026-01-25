<?php
// backend/api/proxy_route.php
require_once __DIR__ . '/../helpers/EnvLoader.php';

// Load .env variables
EnvLoader::load(__DIR__ . '/../../.env');

// Set headers for JSON response
header('Content-Type: application/json');

// Check if coordinates are provided
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;

if (!$start || !$end) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing start or end coordinates.']);
    exit;
}

// Get API key from environment
$apiKey = getenv('ORS_API_KEY');

if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'ORS API Key not configured on server.']);
    exit;
}

// Construct ORS URL
$url = "https://api.openrouteservice.org/v2/directions/driving-car?api_key=" . urlencode($apiKey) . "&start=" . urlencode($start) . "&end=" . urlencode($end);

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8',
    'User-Agent: LeiLife-App'
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'Proxy request failed: ' . curl_error($ch)]);
} else {
    http_response_code($httpCode);
    echo $response;
}

curl_close($ch);
