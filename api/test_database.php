<?php
// Test database connectivity from API
require_once '../config.php';

header('Content-Type: application/json');

echo "=== DATABASE CONNECTIVITY TEST ===\n";

try {
    echo "1. Testing database connection...\n";
    $conn = DatabaseConfig::getConnection();
    echo "✓ Database connected successfully\n";
    
    echo "2. Testing users table access...\n";
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✓ Users table accessible. Total users: $count\n";
    
    echo "3. Testing user lookup...\n";
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ?");
    $stmt->execute(['test@example.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✓ User found:\n";
        echo "  ID: " . $user['id'] . "\n";
        echo "  Name: " . $user['name'] . "\n";
        echo "  Email: " . $user['email'] . "\n";
        
        echo "4. Testing password verification...\n";
        $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && password_verify('password123', $result['password'])) {
            echo "✓ Password verification successful\n";
        } else {
            echo "✗ Password verification failed\n";
        }
    } else {
        echo "✗ User test@example.com not found\n";
        
        echo "4. Showing all users:\n";
        $stmt = $conn->query("SELECT id, name, email FROM users ORDER BY id DESC LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) {
            echo "  - " . $user['name'] . " (" . $user['email'] . ")\n";
        }
    }
    
    echo "\n=== TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    echo "✗ Error code: " . $e->getCode() . "\n";
}

echo "\n=== API RESPONSE FORMAT ===\n";
echo json_encode([
    "success" => true,
    "message" => "Database connectivity test complete",
    "details" => "Check console output for full results"
]);
?>
