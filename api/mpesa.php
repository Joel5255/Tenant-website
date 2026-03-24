<?php
// M-Pesa API integration
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../config.php';

class MpesaService {
    private $consumerKey = "YOUR_CONSUMER_KEY";
    private $consumerSecret = "YOUR_CONSUMER_SECRET";
    private $shortcode = "174379";
    private $passkey = "YOUR_PASSKEY";
    private $baseUrl = "https://sandbox.safaricom.co.ke"; // Use sandbox for testing
    
    public function getAccessToken() {
        $url = $this->baseUrl . "/oauth/v1/generate?grant_type=client_credentials";
        $credentials = base64_encode($this->consumerKey . ":" . $this->consumerSecret);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $data = json_decode($response);
        return $data->access_token;
    }
    
    public function simulateTransaction($userId, $amount, $phoneNumber) {
        // Simulate M-Pesa transaction for school project
        $transactionId = "TEST" . time() . rand(1000, 9999);
        
        try {
            $conn = DatabaseConfig::getConnection();
            
            // Insert simulated transaction
            $stmt = $conn->prepare("
                INSERT INTO mpesa_transactions 
                (user_id, transaction_id, amount, phone_number, transaction_type, transaction_time, account_reference)
                VALUES (?, ?, ?, ?, ?, NOW(), ?)
            ");
            
            $stmt->execute([
                $userId,
                $transactionId,
                $amount,
                $phoneNumber,
                "PayBill",
                "Financial Literacy App"
            ]);
            
            // Update daily spending
            $this->updateDailySpending($userId, $amount);
            
            return [
                "success" => true,
                "transaction_id" => $transactionId,
                "message" => "Transaction simulated successfully"
            ];
            
        } catch (Exception $e) {
            return [
                "success" => false,
                "error" => "Transaction failed: " . $e->getMessage()
            ];
        }
    }
    
    public function updateDailySpending($userId, $amount) {
        try {
            $conn = DatabaseConfig::getConnection();
            
            // Get or create today's target
            $stmt = $conn->prepare("
                SELECT id, target_amount, current_spent, sms_sent 
                FROM daily_targets 
                WHERE user_id = ? AND target_date = CURRENT_DATE
            ");
            $stmt->execute([$userId]);
            $target = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($target) {
                // Update existing target
                $newSpent = $target['current_spent'] + $amount;
                $stmt = $conn->prepare("
                    UPDATE daily_targets 
                    SET current_spent = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$newSpent, $target['id']]);
                
                // Check if target reached and send SMS
                if ($newSpent >= $target['target_amount'] && !$target['sms_sent']) {
                    $this->sendTargetAlert($userId, $newSpent, $target['target_amount']);
                }
            }
            
        } catch (Exception $e) {
            error_log("Error updating daily spending: " . $e->getMessage());
        }
    }
    
    public function sendTargetAlert($userId, $currentSpent, $targetAmount) {
        try {
            $conn = DatabaseConfig::getConnection();
            
            // Get user phone number
            $stmt = $conn->prepare("SELECT phone_number FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && $user['phone_number']) {
                $message = "Alert: You have reached your daily spending target of KES " . $targetAmount . 
                          ". Current spending: KES " . $currentSpent . ". Financial Literacy App";
                
                // Log SMS alert (in real implementation, use SMS service like Twilio)
                $stmt = $conn->prepare("
                    INSERT INTO sms_alerts (user_id, alert_type, message, phone_number, status)
                    VALUES (?, 'target_reached', ?, ?, 'sent')
                ");
                $stmt->execute([$userId, $message, $user['phone_number']]);
                
                // Mark SMS as sent
                $stmt = $conn->prepare("
                    UPDATE daily_targets SET sms_sent = TRUE 
                    WHERE user_id = ? AND target_date = CURRENT_DATE
                ");
                $stmt->execute([$userId]);
                
                return true;
            }
            
        } catch (Exception $e) {
            error_log("Error sending SMS alert: " . $e->getMessage());
        }
        
        return false;
    }
}

// Handle API requests
$requestMethod = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

$mpesa = new MpesaService();

switch ($requestMethod) {
    case 'POST':
        if (isset($data['action'])) {
            switch ($data['action']) {
                case 'simulate_transaction':
                    $userId = $data['user_id'] ?? 1;
                    $amount = $data['amount'] ?? 100;
                    $phoneNumber = $data['phone_number'] ?? '254700000000';
                    
                    $result = $mpesa->simulateTransaction($userId, $amount, $phoneNumber);
                    echo json_encode($result);
                    break;
                    
                default:
                    echo json_encode(["error" => "Unknown action"]);
            }
        }
        break;
        
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'transactions':
                    $userId = $_GET['user_id'] ?? 1;
                    
                    try {
                        $conn = DatabaseConfig::getConnection();
                        $stmt = $conn->prepare("
                            SELECT * FROM mpesa_transactions 
                            WHERE user_id = ? 
                            ORDER BY transaction_time DESC 
                            LIMIT 10
                        ");
                        $stmt->execute([$userId]);
                        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        echo json_encode(["success" => true, "transactions" => $transactions]);
                    } catch (Exception $e) {
                        echo json_encode(["error" => $e->getMessage()]);
                    }
                    break;
                    
                case 'daily_target':
                    $userId = $_GET['user_id'] ?? 1;
                    
                    try {
                        $conn = DatabaseConfig::getConnection();
                        $stmt = $conn->prepare("
                            SELECT * FROM daily_targets 
                            WHERE user_id = ? AND target_date = CURRENT_DATE
                        ");
                        $stmt->execute([$userId]);
                        $target = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        echo json_encode(["success" => true, "target" => $target]);
                    } catch (Exception $e) {
                        echo json_encode(["error" => $e->getMessage()]);
                    }
                    break;
                    
                default:
                    echo json_encode(["error" => "Unknown action"]);
            }
        }
        break;
        
    default:
        echo json_encode(["error" => "Invalid request method"]);
}
?>
