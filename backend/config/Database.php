<?php

require_once __DIR__ . '/../helpers/EnvLoader.php';

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct()
    {
        // Load .env file
        EnvLoader::load(__DIR__ . '/../../.env');

        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        if ($this->host === 'localhost') {
            $this->host = '127.0.0.1';
        }
        $this->db_name = getenv('DB_NAME') ?: 'leilife_v2';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
    }

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            error_log("Database Connection Error: " . $exception->getMessage());
        }

        return $this->conn;
    }

    public function getMySQLiConnection()
    {
        $mysqli = new mysqli($this->host, $this->username, $this->password, $this->db_name);

        if ($mysqli->connect_error) {
            error_log("MySQLi Connection Error: " . $mysqli->connect_error);
            return null;
        }

        // THE BEST PART:
        $mysqli->set_charset("utf8mb4");

        return $mysqli;
    }
}
