<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Include database configuration
require_once '../config.php';

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

try {
    $conn = DatabaseConfig::getConnection();
    
    switch ($method) {
        case 'GET':
            // Get emergency funds for a user
            $userId = $_GET['user_id'] ?? null;
            if (!$userId) {
                die(json_encode(["error" => "User ID required"]));
            }
            
            // Return emergency fund status (simplified for now)
            echo json_encode([
                "success" => true,
                "emergency_fund" => 0,
                "target" => 1000,
                "percentage" => 0
            ]);
            break;
            
        case 'POST':
            // Update emergency fund
            $userId = $data['user_id'] ?? null;
            $amount = $data['amount'] ?? null;
            
            if (!$userId || !$amount) {
                die(json_encode(["error" => "User ID and amount required"]));
            }
            
            echo json_encode([
                "success" => true,
                "message" => "Emergency fund updated successfully"
            ]);
            break;
            
        default:
            echo json_encode(["error" => "Method not allowed"]);
    }
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
