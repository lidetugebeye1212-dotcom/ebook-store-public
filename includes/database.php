<?php
require_once 'config.php';

class Database {
    private $connection;
    private static $instance = null;
    
    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prepare statement to prevent SQL injection
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    // Execute query with parameters
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    // Get last insert ID
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    // Escape string
    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }
}
?>