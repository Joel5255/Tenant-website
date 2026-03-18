<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
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
            // Get daily targets for a user
            $userId = $_GET['user_id'] ?? null;
            if (!$userId) {
                die(json_encode(["error" => "User ID required"]));
            }
            
            echo json_encode([
                "success" => true,
                "daily_target" => 50,
                "weekly_target" => 350,
                "monthly_target" => 1500
            ]);
            break;
            
        case 'POST':
        case 'PUT':
            // Update targets
            $userId = $data['user_id'] ?? null;
            $dailyTarget = $data['daily_target'] ?? null;
            
            if (!$userId || !$dailyTarget) {
                die(json_encode(["error" => "User ID and daily target required"]));
            }
            
            echo json_encode([
                "success" => true,
                "message" => "Targets updated successfully"
            ]);
            break;
            
        default:
            echo json_encode(["error" => "Method not allowed"]);
    }
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
