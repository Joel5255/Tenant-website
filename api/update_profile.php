<?php
// Update user profile
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['phone_number'])) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

$userId = $data['user_id'];
$phoneNumber = $data['phone_number'];

try {
    $conn = DatabaseConfig::getConnection();
    
    // Update user phone number
    $stmt = $conn->prepare("UPDATE users SET phone_number = ? WHERE id = ?");
    $stmt->execute([$phoneNumber, $userId]);
    
    echo json_encode([
        "success" => true,
        "message" => "Phone number updated successfully"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Failed to update phone number: " . $e->getMessage()
    ]);
}
?>
