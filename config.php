<?php
// Database configuration for Render.com
class DatabaseConfig {
    public static function getConnection() {
        // Render.com PostgreSQL connection (hardcoded for testing)
        $host = "dpg-d6t6h424d50c73c3vuqg-a.oregon-postgres.render.com";
        $port = "5432";
        $database = "financial_literacy_db";
        $user = "financial_literacy_db_zttd_user";
        $password = "giEKI5L0bq3P2qzX1hvYm56nU58MefRc";
        
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
            // Fallback to MySQL for local development
            return self::getMySQLConnection();
        }
    }
    
    private static function getMySQLConnection() {
        // Local MySQL connection (for development)
        $host = "localhost";
        $user = "root";
        $password = "";
        $database = "financial_literacy";
        
        $conn = new mysqli($host, $user, $password, $database);
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
}
?>
