<?php
// Test database connection
require_once 'config.php';

echo "Testing database connection...\n";

try {
    $conn = DatabaseConfig::getConnection();
    echo "✓ Database connection successful!\n";
    
    // Test if we can create a table
    $conn->exec("CREATE TABLE IF NOT EXISTS test_table (id SERIAL PRIMARY KEY)");
    echo "✓ Can create tables!\n";
    
    // Test if we can insert data
    $conn->exec("INSERT INTO test_table (id) VALUES (1) ON CONFLICT (id) DO NOTHING");
    echo "✓ Can insert data!\n";
    
    // Test if we can read data
    $result = $conn->query("SELECT COUNT(*) FROM test_table");
    $count = $result->fetchColumn();
    echo "✓ Can read data! Found $count records\n";
    
    echo "✓ Database is fully working!\n";
    
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    echo "✗ Connection failed!\n";
}
?>
