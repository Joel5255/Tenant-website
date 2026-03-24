<?php
// Database viewer for checking contents
require_once 'config.php';

header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Viewer</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .no-data { color: #666; font-style: italic; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007bff; }
        .btn { background: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Viewer</h1>
        
        <?php
        try {
            $conn = DatabaseConfig::getConnection();
            echo '<p class="success">✅ Database connected successfully!</p>';
            
            // Get table statistics
            $tables = ['users', 'mpesa_transactions', 'daily_targets', 'sms_alerts'];
            echo '<div class="section">';
            echo '<h2>📊 Database Statistics</h2>';
            echo '<div class="stats">';
            
            foreach ($tables as $table) {
                try {
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
                    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    echo '<div class="stat-box">';
                    echo '<div class="stat-number">' . $count . '</div>';
                    echo '<div>' . ucfirst(str_replace('_', ' ', $table)) . '</div>';
                    echo '</div>';
                } catch (Exception $e) {
                    echo '<div class="stat-box">';
                    echo '<div class="stat-number">0</div>';
                    echo '<div>' . ucfirst(str_replace('_', ' ', $table)) . '</div>';
                    echo '</div>';
                }
            }
            
            echo '</div>';
            echo '</div>';
            
            // Show users table
            echo '<div class="section">';
            echo '<h2>👥 Users Table</h2>';
            try {
                $stmt = $conn->query("SELECT id, name, email, phone_number, created_at FROM users ORDER BY id DESC LIMIT 10");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($users) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Created</th></tr>';
                    foreach ($users as $user) {
                        echo '<tr>';
                        echo '<td>' . $user['id'] . '</td>';
                        echo '<td>' . $user['name'] . '</td>';
                        echo '<td>' . $user['email'] . '</td>';
                        echo '<td>' . ($user['phone_number'] ?: 'N/A') . '</td>';
                        echo '<td>' . $user['created_at'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p class="no-data">No users found in database.</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">Error reading users table: ' . $e->getMessage() . '</p>';
            }
            echo '</div>';
            
            // Show M-Pesa transactions
            echo '<div class="section">';
            echo '<h2>💰 M-Pesa Transactions</h2>';
            try {
                $stmt = $conn->query("SELECT * FROM mpesa_transactions ORDER BY transaction_time DESC LIMIT 10");
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($transactions) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>User ID</th><th>Transaction ID</th><th>Amount</th><th>Phone</th><th>Type</th><th>Time</th></tr>';
                    foreach ($transactions as $trans) {
                        echo '<tr>';
                        echo '<td>' . $trans['id'] . '</td>';
                        echo '<td>' . $trans['user_id'] . '</td>';
                        echo '<td>' . $trans['transaction_id'] . '</td>';
                        echo '<td>KES ' . $trans['amount'] . '</td>';
                        echo '<td>' . $trans['phone_number'] . '</td>';
                        echo '<td>' . $trans['transaction_type'] . '</td>';
                        echo '<td>' . $trans['transaction_time'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p class="no-data">No M-Pesa transactions found.</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">Error reading transactions table: ' . $e->getMessage() . '</p>';
            }
            echo '</div>';
            
            // Show daily targets
            echo '<div class="section">';
            echo '<h2>🎯 Daily Targets</h2>';
            try {
                $stmt = $conn->query("SELECT * FROM daily_targets ORDER BY target_date DESC LIMIT 10");
                $targets = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($targets) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>User ID</th><th>Target</th><th>Spent</th><th>Date</th><th>SMS Sent</th></tr>';
                    foreach ($targets as $target) {
                        echo '<tr>';
                        echo '<td>' . $target['id'] . '</td>';
                        echo '<td>' . $target['user_id'] . '</td>';
                        echo '<td>KES ' . $target['target_amount'] . '</td>';
                        echo '<td>KES ' . $target['current_spent'] . '</td>';
                        echo '<td>' . $target['target_date'] . '</td>';
                        echo '<td>' . ($target['sms_sent'] ? 'Yes' : 'No') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p class="no-data">No daily targets found.</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">Error reading targets table: ' . $e->getMessage() . '</p>';
            }
            echo '</div>';
            
            // Show SMS alerts
            echo '<div class="section">';
            echo '<h2>📱 SMS Alerts</h2>';
            try {
                $stmt = $conn->query("SELECT * FROM sms_alerts ORDER BY sent_at DESC LIMIT 10");
                $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($alerts) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>User ID</th><th>Type</th><th>Message</th><th>Phone</th><th>Status</th><th>Sent At</th></tr>';
                    foreach ($alerts as $alert) {
                        echo '<tr>';
                        echo '<td>' . $alert['id'] . '</td>';
                        echo '<td>' . $alert['user_id'] . '</td>';
                        echo '<td>' . $alert['alert_type'] . '</td>';
                        echo '<td>' . substr($alert['message'], 0, 50) . '...</td>';
                        echo '<td>' . $alert['phone_number'] . '</td>';
                        echo '<td>' . $alert['status'] . '</td>';
                        echo '<td>' . $alert['sent_at'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p class="no-data">No SMS alerts found.</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">Error reading alerts table: ' . $e->getMessage() . '</p>';
            }
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<p class="error">❌ Database connection failed: ' . $e->getMessage() . '</p>';
        }
        ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <button class="btn" onclick="window.location.reload()">🔄 Refresh</button>
            <button class="btn" onclick="window.location.href='/'">🏠 Back to Website</button>
        </div>
    </div>
</body>
</html>
