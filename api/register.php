<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include database configuration
require_once '../config.php';

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    die(json_encode(["error" => "Invalid JSON data"]));
}

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// Validation
if (empty($name) || empty($email) || empty($password)) {
    die(json_encode(["error" => "All fields are required"]));
}

try {
    // Get database connection
    $conn = DatabaseConfig::getConnection();
    
    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        die(json_encode(["error" => "User already exists"]));
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Create user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword]);
    
    $userId = $conn->lastInsertId();
    
    echo json_encode([
        "success" => true,
        "message" => "User created successfully",
        "user_id" => $userId,
        "name" => $name,
        "email" => $email
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Database error: " . $e->getMessage()
    ]);
}
?>
