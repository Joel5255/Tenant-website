<?php
// Database configuration for Supabase - Website version
class DatabaseConfig {
    public static function getConnection() {
        // Supabase PostgreSQL connection for website
        // Replace these with your actual Supabase credentials
        $host = "db.vcgmqckgxbpjwfdxgwbt.supabase.co";
        $port = "5432";
        $database = "postgres";
        $user = "postgres";
        $password = "BvwOmOLWSGRzbYMQ";
        
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
