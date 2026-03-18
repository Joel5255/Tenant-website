<?php
// Database configuration for Render PostgreSQL
class DatabaseConfig {
    public static function getConnection() {
        // Render PostgreSQL connection
        $host = "dpg-d6tht7fkijhs73f5j7gg-a.oregon-postgres.render.com";
        $port = "5432";
        $database = "financial_literacy_db_fyo7";
        $user = "financial_literacy_db_fyo7_user";
        $password = "55Qigb5CmHmDd7gmyXDGjyp3ypaFCDjz";
        
        try {
            // PostgreSQL connection with explicit parameters
            $conn = new PDO(
                "pgsql:host=$host;port=$port;dbname=$database",
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            return $conn;
        } catch(PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
}
?>
