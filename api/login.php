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

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// Validation
if (empty($email) || empty($password)) {
    die(json_encode(["error" => "Email and password are required"]));
}

try {
    // Get database connection
    $conn = DatabaseConfig::getConnection();
    
    // Find user by email
    if ($conn instanceof PDO) {
        // PostgreSQL
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "user" => [
                    "id" => $user['id'],
                    "name" => $user['name'],
                    "email" => $user['email']
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "error" => "Invalid email or password"]);
        }
    } else {
        // MySQL fallback
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "user" => [
                    "id" => $user['id'],
                    "name" => $user['name'],
                    "email" => $user['email']
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "error" => "Invalid email or password"]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
