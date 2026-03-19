<?php
// Simple database users viewer
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Users</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .back-link { color: #007bff; text-decoration: none; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
    <h2>📊 Database Users</h2>
    <a href='/' class='back-link'>← Back to Website</a>";

try {
    $conn = DatabaseConfig::getConnection();
    
    echo "<h3>✅ Database Connected</h3>";
    
    // Count users
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p><strong>Total Users:</strong> $count</p>";
    
    // Show users
    $stmt = $conn->query("SELECT id, name, email, created_at FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($count > 0) {
        echo "<table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Created At</th>
            </tr>";
        
        foreach ($users as $user) {
            echo "<tr>
                <td>" . htmlspecialchars($user['id']) . "</td>
                <td>" . htmlspecialchars($user['name']) . "</td>
                <td>" . htmlspecialchars($user['email']) . "</td>
                <td>" . htmlspecialchars($user['created_at']) . "</td>
            </tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='error'>❌ No users found in database</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
