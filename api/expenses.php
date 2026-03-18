<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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
            // Get expenses for a user
            $userId = $_GET['user_id'] ?? null;
            if (!$userId) {
                die(json_encode(["error" => "User ID required"]));
            }
            
            if ($conn instanceof PDO) {
                // PostgreSQL
                $stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC");
                $stmt->execute([$userId]);
                $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // MySQL fallback
                $stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $expenses = $result->fetch_all(MYSQLI_ASSOC);
            }
            
            echo json_encode(["success" => true, "expenses" => $expenses]);
            break;
            
        case 'POST':
            // Add new expense
            $userId = $data['user_id'] ?? null;
            $amount = $data['amount'] ?? null;
            $category = $data['category'] ?? null;
            $description = $data['description'] ?? '';
            $date = $data['date'] ?? date('Y-m-d');
            
            if (!$userId || !$amount || !$category) {
                die(json_encode(["error" => "User ID, amount, and category required"]));
            }
            
            if ($conn instanceof PDO) {
                // PostgreSQL
                $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, category, description, date) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $amount, $category, $description, $date]);
                $expenseId = $conn->lastInsertId();
            } else {
                // MySQL fallback
                $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, category, description, date) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("idsss", $userId, $amount, $category, $description, $date);
                $stmt->execute();
                $expenseId = $conn->insert_id;
            }
            
            echo json_encode([
                "success" => true,
                "message" => "Expense added successfully",
                "expense_id" => $expenseId
            ]);
            break;
            
        case 'DELETE':
            // Delete expense
            $expenseId = $_GET['id'] ?? null;
            if (!$expenseId) {
                die(json_encode(["error" => "Expense ID required"]));
            }
            
            if ($conn instanceof PDO) {
                // PostgreSQL
                $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
                $stmt->execute([$expenseId]);
            } else {
                // MySQL fallback
                $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
                $stmt->bind_param("i", $expenseId);
                $stmt->execute();
            }
            
            echo json_encode(["success" => true, "message" => "Expense deleted successfully"]);
            break;
            
        default:
            echo json_encode(["error" => "Method not allowed"]);
    }
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
