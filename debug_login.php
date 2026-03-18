<?php
// Debug login issue
require_once 'config.php';

echo "=== DEBUG LOGIN ===\n";

try {
    $conn = DatabaseConfig::getConnection();
    echo "✓ Database connected\n";
    
    // Check if user exists
    $email = "test@example.com";
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✓ User found:\n";
        echo "  ID: " . $user['id'] . "\n";
        echo "  Name: " . $user['name'] . "\n";
        echo "  Email: " . $user['email'] . "\n";
        echo "  Password hash: " . substr($user['password'], 0, 20) . "...\n";
        
        // Test password verification
        $password = "password123";
        if (password_verify($password, $user['password'])) {
            echo "✓ Password verification SUCCESSFUL\n";
        } else {
            echo "✗ Password verification FAILED\n";
        }
    } else {
        echo "✗ User NOT found with email: $email\n";
        
        // Show all users
        $stmt = $conn->query("SELECT id, name, email FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "All users in database:\n";
        foreach ($users as $user) {
            echo "  - " . $user['name'] . " (" . $user['email'] . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
