-- Update transactions table to support more detailed logging and refunds
ALTER TABLE transactions 
ADD COLUMN transaction_type ENUM('payment', 'refund', 'chargeback', 'reversal') DEFAULT 'payment' AFTER order_id,
ADD COLUMN description TEXT NULL AFTER amount,
ADD COLUMN raw_response TEXT NULL AFTER payment_method,
MODIFY COLUMN status ENUM('pending', 'success', 'failed', 'expired', 'refunded') NOT NULL;
