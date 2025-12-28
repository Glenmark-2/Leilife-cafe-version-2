CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    -- Financials
    total_amount DECIMAL(10, 2) NOT NULL,
    delivery_fee DECIMAL(10, 2) DEFAULT 0.00,
    
    -- Statuses
    status ENUM('pending', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'delivered', 'cancelled', 'payment_failed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    
    -- Methods
    payment_method ENUM('cod', 'gcash') NOT NULL, -- 'gcash' represents e-wallet via PayMongo
    delivery_method ENUM('pickup', 'delivery') NOT NULL,
    
    -- Delivery Details (Snapshot)
    delivery_address TEXT NULL, -- Storing full address string/JSON
    delivery_notes TEXT NULL,
    
    -- PayMongo Integration
    paymongo_checkout_session_id VARCHAR(255) NULL,
    paymongo_payment_intent_id VARCHAR(255) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    
    -- Snapshots
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL, -- Price at time of purchase
    
    quantity INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL, -- (price * quantity)
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    
    -- Transaction Details
    transaction_reference VARCHAR(255) NOT NULL, -- PayMongo ID or UUID for COD
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'success', 'failed') NOT NULL,
    payment_method VARCHAR(50) NOT NULL, -- 'cod', 'gcash', 'paymongo'
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
-- done nobi