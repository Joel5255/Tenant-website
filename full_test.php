<?php
// Comprehensive database test
require_once 'config.php';

echo "=== COMPREHENSIVE DATABASE TEST ===\n";

try {
    // Test 1: Database connection
    echo "1. Testing database connection...\n";
    $conn = DatabaseConfig::getConnection();
    echo "✓ Database connection successful\n";
    
    // Test 2: Check if users table exists
    echo "2. Checking if users table exists...\n";
    $result = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users'");
    if ($result->rowCount() > 0) {
        echo "✓ Users table exists\n";
    } else {
        echo "✗ Users table does NOT exist!\n";
        echo "Creating users table...\n";
        
        $conn->exec("
            CREATE TABLE users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✓ Users table created\n";
    }
    
    // Test 3: Check table structure
    echo "3. Checking table structure...\n";
    $result = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users' ORDER BY ordinal_position");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['column_name'] . ": " . $row['data_type'] . "\n";
    }
    
    // Test 4: Count current users
    echo "4. Counting current users...\n";
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
    echo "  Current users in database: $count\n";
    
    // Test 5: Insert test user
    echo "5. Inserting test user...\n";
    $name = "Test User " . time();
    $email = "test" . time() . "@example.com";
    $password = "password123";
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $result = $stmt->execute([$name, $email, $hashedPassword]);
    
    if ($result) {
        echo "✓ Test user inserted successfully\n";
        echo "  Email: $email\n";
        echo "  Password: $password\n";
    } else {
        echo "✗ Failed to insert test user\n";
        print_r($stmt->errorInfo());
    }
    
    // Test 6: Verify insertion
    echo "6. Verifying insertion...\n";
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✓ User found in database:\n";
        echo "  ID: " . $user['id'] . "\n";
        echo "  Name: " . $user['name'] . "\n";
        echo "  Email: " . $user['email'] . "\n";
    } else {
        echo "✗ User NOT found after insertion\n";
    }
    
    echo "\n=== TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "✗ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "✗ Error code: " . $e->getCode() . "\n";
    echo "✗ Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
