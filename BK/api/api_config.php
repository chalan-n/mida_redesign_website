<?php
// API Configuration File
// api/api_config.php

// API Key สำหรับการยืนยันตัวตน
define('API_KEY', 'MIDA_API_2026_SECRET_KEY');

// Database Configuration (reuse from admin)
class ApiDatabase
{
    private $host = 'localhost';
    private $port = '3306';
    private $db_name = 'zp12865_db_mida_leasing';
    private $username = 'zp12865_zp12865';
    private $password = 'sfcLF7SfUYeNgPRwWb7W';
    public $conn;

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
            return null;
        }

        return $this->conn;
    }
}
