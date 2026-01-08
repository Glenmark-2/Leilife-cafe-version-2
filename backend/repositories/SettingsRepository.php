<?php
require_once __DIR__ . '/../config/Database.php';

class SettingsRepository
{
    private $conn;
    private $table = "site_settings";

    public function __construct($db = null)
    {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    private function ensureTableExists()
    {
        if (!$this->conn) return;
        $query = "SHOW TABLES LIKE '" . $this->table . "'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $sql = "CREATE TABLE IF NOT EXISTS site_settings (
                id INT PRIMARY KEY DEFAULT 1,
                store_name VARCHAR(255) DEFAULT 'Leilife Cafe & Resto',
                store_slogan VARCHAR(255) DEFAULT 'Premium Coffee & Fine Dining',
                store_logo VARCHAR(255) DEFAULT 'logo.png',
                contact_phone VARCHAR(50) DEFAULT '09123456789',
                contact_email VARCHAR(100) DEFAULT 'info@leilife.com',
                physical_address TEXT,
                facebook_link VARCHAR(255),
                instagram_link VARCHAR(255),
                is_store_open BOOLEAN DEFAULT TRUE,
                order_limit INT DEFAULT 20,
                opening_hours JSON,
                delivery_fee DECIMAL(10, 2) DEFAULT 50.00,
                free_delivery_threshold DECIMAL(10, 2) DEFAULT 1000.00,
                enable_cod BOOLEAN DEFAULT TRUE,
                enable_gcash BOOLEAN DEFAULT TRUE,
                paymongo_public_key VARCHAR(255),
                paymongo_secret_key VARCHAR(255),
                receipt_footer TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT single_row CHECK (id = 1)
            );
            INSERT IGNORE INTO site_settings (id, store_name, physical_address) 
            VALUES (1, 'Leilife Cafe & Resto', '123 Coffee Street, Caloocan City');";
            $this->conn->exec($sql);
        }
    }

    public function getSettings()
    {
        if (!$this->conn) return null;
        $query = "SELECT * FROM " . $this->table . " WHERE id = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSettings($data)
    {
        $fields = [];
        $params = [];

        // List of updatable fields
        $updatable = [
            'store_name',
            'store_slogan',
            'contact_phone',
            'contact_email',
            'physical_address',
            'facebook_link',
            'instagram_link',
            'is_store_open',
            'order_limit',
            'opening_hours',
            'delivery_fee',
            'free_delivery_threshold',
            'enable_cod',
            'enable_gcash',
            'paymongo_public_key',
            'paymongo_secret_key',
            'receipt_footer'
        ];

        foreach ($updatable as $key) {
            if (isset($data[$key])) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $data[$key];
            }
        }

        if (empty($fields)) return false;

        $query = "UPDATE " . $this->table . " SET " . implode(", ", $fields) . " WHERE id = 1";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }
}
