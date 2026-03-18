<?php
// Database setup script for Render.com
require_once 'config.php';

try {
    $conn = DatabaseConfig::getConnection();
    
    // Create users table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create transactions table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS transactions (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            amount DECIMAL(10,2) NOT NULL,
            category VARCHAR(100) NOT NULL,
            description TEXT,
            date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create budgets table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS budgets (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            category VARCHAR(100) NOT NULL,
            limit_amount DECIMAL(10,2) NOT NULL,
            spent_amount DECIMAL(10,2) DEFAULT 0,
            month VARCHAR(7) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "✓ Database tables created successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error setting up database: " . $e->getMessage() . "\n";
}
?>
