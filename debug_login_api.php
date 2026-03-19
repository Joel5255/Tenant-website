<?php
// Debug login API
require_once 'config.php';

echo "=== DEBUG LOGIN API ===\n";

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

echo "Received data:\n";
echo "  Email: " . ($data['email'] ?? 'NULL') . "\n";
echo "  Password: " . ($data['password'] ?? 'NULL') . "\n";

if (!$data) {
    echo "Error: Invalid JSON data\n";
    die(json_encode(["error" => "Invalid JSON data"]));
}

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

echo "Validation:\n";
echo "  Email empty: " . (empty($email) ? 'YES' : 'NO') . "\n";
echo "  Password empty: " . (empty($password) ? 'YES' : 'NO') . "\n";

if (empty($email) || empty($password)) {
    echo "Error: Email and password are required\n";
    die(json_encode(["error" => "Email and password are required"]));
}

try {
    echo "Getting database connection...\n";
    $conn = DatabaseConfig::getConnection();
    echo "✓ Database connected\n";
    
    echo "Looking for user: $email\n";
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✓ User found in database\n";
        echo "  User ID: " . $user['id'] . "\n";
        echo "  User name: " . $user['name'] . "\n";
        echo "  User email: " . $user['email'] . "\n";
        echo "  Password hash: " . substr($user['password'], 0, 20) . "...\n";
        
        if (password_verify($password, $user['password'])) {
            echo "✓ Password verification SUCCESSFUL\n";
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
            echo "✗ Password verification FAILED\n";
            echo json_encode(["success" => false, "error" => "Invalid email or password"]);
        }
    } else {
        echo "✗ User NOT found in database\n";
        
        // Show all users for debugging
        $stmt = $conn->query("SELECT id, name, email FROM users ORDER BY id DESC LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Recent users in database:\n";
        foreach ($users as $user) {
            echo "  - " . $user['name'] . " (" . $user['email'] . ")\n";
        }
        
        echo json_encode(["success" => false, "error" => "Invalid email or password"]);
    }
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}
?>
