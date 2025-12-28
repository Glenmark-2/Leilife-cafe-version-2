-- Add picked_up status to orders table
ALTER TABLE orders 
MODIFY COLUMN status ENUM('pending', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'picked_up', 'delivered', 'cancelled', 'payment_failed') DEFAULT 'pending';
