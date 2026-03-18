<?php
// Test registration directly
require_once 'config.php';

echo "=== TEST REGISTRATION ===\n";

try {
    $conn = DatabaseConfig::getConnection();
    echo "✓ Database connected\n";
    
    // Test user data
    $name = "Test User";
    $email = "test" . time() . "@example.com"; // Unique email
    $password = "password123";
    
    echo "Creating user:\n";
    echo "  Name: $name\n";
    echo "  Email: $email\n";
    echo "  Password: $password\n";
    
    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo "✗ User already exists\n";
    } else {
        echo "✓ User doesn't exist, proceeding...\n";
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        echo "✓ Password hashed\n";
        
        // Create user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $result = $stmt->execute([$name, $email, $hashedPassword]);
        
        if ($result) {
            $userId = $conn->lastInsertId();
            echo "✓ User created successfully! ID: $userId\n";
        } else {
            echo "✗ Failed to create user\n";
            print_r($stmt->errorInfo());
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "✗ Error code: " . $e->getCode() . "\n";
}
?>
