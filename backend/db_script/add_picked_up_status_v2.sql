-- Update orders table status enum to include picked_up
ALTER TABLE orders 
MODIFY COLUMN status ENUM('pending', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'picked_up', 'delivered', 'cancelled', 'payment_failed') DEFAULT 'pending';

-- Update order_items table status enum to include picked_up
ALTER TABLE order_items 
MODIFY COLUMN status ENUM('pending', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'picked_up', 'delivered', 'cancelled', 'payment_failed') DEFAULT 'pending';
