ALTER TABLE order_items ADD COLUMN status ENUM('pending', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'delivered', 'cancelled', 'payment_failed') DEFAULT 'pending';
-- done nobi
