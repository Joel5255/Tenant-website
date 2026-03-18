<?php
// Database configuration for Render.com
class DatabaseConfig {
    public static function getConnection() {
        // Render.com PostgreSQL connection
        $host = getenv("POSTGRES_HOST") ?: "localhost";
        $port = getenv("POSTGRES_PORT") ?: "5432";
        $database = getenv("POSTGRES_DB") ?: "financial_literacy";
        $user = getenv("POSTGRES_USER") ?: "financial_user";
        $password = getenv("POSTGRES_PASSWORD") ?: "";
        
        try {
            // PostgreSQL connection
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            $conn = new PDO($dsn, $user, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
