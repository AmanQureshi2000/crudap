<?php
require_once 'Config.php';

class Model {
    private $host;
    private $db;
    private $user;
    private $password;
    private $port;
    private $connection_string;
    private $conn;
    
    public function __construct() {
        // Load environment variables
        Config::load(__DIR__ . '/.env');
        
        // Get database configuration from environment
        $this->host = Config::get('DB_HOST');
        $this->db = Config::get('DB_NAME');
        $this->user = Config::get('DB_USER');
        $this->password = Config::get('DB_PASSWORD');
        $this->port = Config::get('DB_PORT');
        
        $this->connection_string = "host={$this->host} dbname={$this->db} user={$this->user} password={$this->password} port={$this->port}";
        $this->conn = pg_connect($this->connection_string);
        if(!$this->conn) {
            die("Connection failed: " . pg_last_error());
        }
        $this->createTables();
        
        // Set session name from env
        $sessionName = Config::get('SESSION_NAME', 'todo_app_session');
        session_name($sessionName);
        session_start();
    }
    
    private function createTables() {
        // Create users table
        $query = "CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->runQuery($query);
        
        // Create todos table with user_id foreign key
        $query = "CREATE TABLE IF NOT EXISTS todos (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->runQuery($query);
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function runQuery($query, $params = null) {
        $conn = $this->getConnection();
        if($params === null || empty($params)) {
            $result = pg_query($conn, $query);
        } else {
            $result = pg_query_params($conn, $query, $params);
        }
        
        if(!$result) {
            return false;
        }
        
        // Check if it's a SELECT query
        if (strpos(strtoupper($query), 'SELECT') === 0) {
            $num_rows = pg_num_rows($result);
            if($num_rows > 0) {
                $data = pg_fetch_all($result);
                return $data;
            }
            return [];
        }
        
        // For INSERT, UPDATE, DELETE queries
        return true;
    }
    
    // User authentication methods
    public function registerUser($username, $email, $password) {
        // Check if user exists
        $checkQuery = "SELECT id FROM users WHERE username = $1 OR email = $2";
        $existing = $this->runQuery($checkQuery, array($username, $email));
        
        if($existing && count($existing) > 0) {
            return false; // User already exists
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $query = "INSERT INTO users (username, email, password) VALUES ($1, $2, $3)";
        return $this->runQuery($query, array($username, $email, $hashedPassword));
    }
    
    public function loginUser($username, $password) {
        $query = "SELECT * FROM users WHERE username = $1 OR email = $1";
        $result = $this->runQuery($query, array($username));
        
        if($result && count($result) > 0) {
            $user = $result[0];
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                return true;
            }
        }
        return false;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    // Todo specific methods with user_id
    public function getAllTodos() {
        $userId = $this->getCurrentUserId();
        if(!$userId) return [];
        
        $query = "SELECT * FROM todos WHERE user_id = $1 ORDER BY created_at DESC";
        $result = $this->runQuery($query, array($userId));
        return is_array($result) ? $result : [];
    }
    
    public function getTodoById($id) {
        $userId = $this->getCurrentUserId();
        if(!$userId) return null;
        
        $query = "SELECT * FROM todos WHERE id = $1 AND user_id = $2";
        $result = $this->runQuery($query, array($id, $userId));
        return ($result && is_array($result) && count($result) > 0) ? $result[0] : null;
    }
    
    public function createTodo($title, $description) {
        $userId = $this->getCurrentUserId();
        if(!$userId) return false;
        
        $query = "INSERT INTO todos (user_id, title, description, status) VALUES ($1, $2, $3, 'pending')";
        return $this->runQuery($query, array($userId, $title, $description));
    }
    
    public function updateTodo($id, $title, $description, $status) {
        $userId = $this->getCurrentUserId();
        if(!$userId) return false;
        
        $query = "UPDATE todos SET title = $1, description = $2, status = $3, updated_at = CURRENT_TIMESTAMP WHERE id = $4 AND user_id = $5";
        return $this->runQuery($query, array($title, $description, $status, $id, $userId));
    }
    
    public function deleteTodo($id) {
        $userId = $this->getCurrentUserId();
        if(!$userId) return false;
        
        $query = "DELETE FROM todos WHERE id = $1 AND user_id = $2";
        return $this->runQuery($query, array($id, $userId));
    }
    
    public function updateStatus($id, $status) {
        $userId = $this->getCurrentUserId();
        if(!$userId) return false;
        
        $query = "UPDATE todos SET status = $1, updated_at = CURRENT_TIMESTAMP WHERE id = $2 AND user_id = $3";
        return $this->runQuery($query, array($status, $id, $userId));
    }
}
?>