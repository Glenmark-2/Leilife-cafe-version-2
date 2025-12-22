CREATE TABLE IF NOT EXISTS user_addresses (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    street VARCHAR(255) NOT NULL,
    barangay VARCHAR(255) NOT NULL,
    city VARCHAR(100) DEFAULT 'Caloocan City',
    province VARCHAR(100) DEFAULT 'Metro Manila',
    region VARCHAR(50) DEFAULT 'NCR',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
--done glennmark
