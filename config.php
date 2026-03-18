<?php
// Database configuration for Supabase - Website version
class DatabaseConfig {
    public static function getConnection() {
        // Supabase PostgreSQL connection for website
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
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
}
?>
?>
