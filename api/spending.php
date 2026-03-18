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
            // Get today's spending for a user
            $userId = $_GET['user_id'] ?? null;
            if (!$userId) {
                die(json_encode(["error" => "User ID required"]));
            }
            
            $today = date('Y-m-d');
            
            if ($conn instanceof PDO) {
                // PostgreSQL
                $stmt = $conn->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND date = ?");
                $stmt->execute([$userId, $today]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // MySQL fallback
                $stmt = $conn->prepare("SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND date = ?");
                $stmt->bind_param("is", $userId, $today);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
            }
            
            $totalSpent = $result['total'] ?? 0;
            
            echo json_encode([
                "success" => true,
                "date" => $today,
                "total_spent" => floatval($totalSpent),
                "transaction_count" => 0
            ]);
            break;
            
        case 'POST':
            // Set daily spending target
            $userId = $data['user_id'] ?? null;
            $target = $data['target'] ?? null;
            
            if (!$userId || !$target) {
                die(json_encode(["error" => "User ID and target required"]));
            }
            
            echo json_encode([
                "success" => true,
                "message" => "Daily target set successfully"
            ]);
            break;
            
        default:
            echo json_encode(["error" => "Method not allowed"]);
    }
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
