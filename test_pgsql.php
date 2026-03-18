<?php
// Test if PostgreSQL extension is loaded
echo "Testing PostgreSQL extension...\n";

// Check if PostgreSQL extension is available
if (extension_loaded('pdo_pgsql')) {
    echo "✓ PostgreSQL PDO extension is loaded!\n";
} else {
    echo "✗ PostgreSQL PDO extension is NOT loaded!\n";
    echo "Available PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
}

// Test basic connection without database
try {
    $pdo = new PDO('pgsql:host=dpg-d6t6h424d50c73c3vuqg-a.oregon-postgres.render.com;port=5432;dbname=financial_literacy_db', 'financial_literacy_db_zttd_user', 'giEKI5L0bq3P2qzX1hvYm56nU58MefRc');
    echo "✓ Basic PostgreSQL connection works!\n";
} catch (Exception $e) {
    echo "✗ PostgreSQL connection failed: " . $e->getMessage() . "\n";
}
?>
