<?php
// Create M-Pesa transactions table
require_once 'config.php';

try {
    $conn = DatabaseConfig::getConnection();
    
    // Create M-Pesa transactions table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS mpesa_transactions (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            transaction_id VARCHAR(50) UNIQUE NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            phone_number VARCHAR(20) NOT NULL,
            transaction_type VARCHAR(50) NOT NULL,
            transaction_time TIMESTAMP NOT NULL,
            account_reference VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create daily targets table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS daily_targets (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            target_amount DECIMAL(10,2) NOT NULL,
            current_spent DECIMAL(10,2) DEFAULT 0,
            target_date DATE NOT NULL,
            sms_sent BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create SMS alerts table
    $conn->exec("
        CREATE TABLE IF NOT EXISTS sms_alerts (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            alert_type VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            phone_number VARCHAR(20) NOT NULL,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(20) DEFAULT 'pending'
        )
    ");
    
    echo "✅ M-Pesa and SMS tables created successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error creating tables: " . $e->getMessage() . "\n";
}
?>
