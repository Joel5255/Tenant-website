<?php
// Add phone number column to users table
require_once 'config.php';

try {
    $conn = DatabaseConfig::getConnection();
    
    // Add phone number column if it doesn't exist
    $conn->exec("
        ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS phone_number VARCHAR(20)
    ");
    
    echo "✅ Phone number column added to users table!\n";
    
} catch (Exception $e) {
    echo "❌ Error adding phone number column: " . $e->getMessage() . "\n";
}
?>
