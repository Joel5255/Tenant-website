<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Render backend URL
$renderUrl = "https://financial-literacy-backend.onrender.com";

// Get the API endpoint from the current filename
$currentFile = basename(__FILE__, '.php');
$apiEndpoint = "/api/" . $currentFile;

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request data
$data = json_decode(file_get_contents("php://input"), true);

// Get query parameters for GET requests
$queryString = $_SERVER['QUERY_STRING'];
$fullUrl = $renderUrl . $apiEndpoint;
if ($queryString) {
    $fullUrl .= '?' . $queryString;
}

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fullUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development

// Set request method
if ($method === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
} elseif ($method === 'PUT' || $method === 'DELETE') {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
}

// Set headers
$headers = ['Content-Type: application/json'];
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if ($authHeader) {
    $headers[] = 'Authorization: ' . $authHeader;
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die(json_encode(["error" => "Failed to connect to backend: " . $error]));
}

// Return the response from Render backend
echo $response;
?>
