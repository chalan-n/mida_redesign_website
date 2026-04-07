<?php
// admin/config/db.php

class Database
{
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct()
    {
        $this->host = getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost';
        $this->port = getenv('DB_PORT') ? getenv('DB_PORT') : '3307';
        $this->db_name = getenv('DB_NAME') ? getenv('DB_NAME') : 'db_mida_leasing';
        $this->username = getenv('DB_USER') ? getenv('DB_USER') : 'root';
        $this->password = getenv('DB_PASSWORD');
        if ($this->password === false) {
            $this->password = '';
        }
    }

    public function getConnection()
    {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);

            // Set Error Mode to Exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Set Default Fetch Mode to Assoc
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>
