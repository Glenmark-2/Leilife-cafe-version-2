<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderItem.php';

class OrderRepository
{
    private $conn;
    private $table_orders = "orders";
    private $table_order_items = "order_items";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create(Order $order)
    {
        // Generate Custom Order Number
        if (empty($order->order_number)) {
            $order->order_number = '#ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        }

        $query = "INSERT INTO " . $this->table_orders . " 
                  (order_number, user_id, contact_number, total_amount, delivery_fee, status, payment_status, payment_method, delivery_method, delivery_address, delivery_notes, paymongo_checkout_session_id, paymongo_payment_intent_id) 
                  VALUES (:order_number, :user_id, :contact_number, :total_amount, :delivery_fee, :status, :payment_status, :payment_method, :delivery_method, :delivery_address, :delivery_notes, :paymongo_checkout_session_id, :paymongo_payment_intent_id)";

        $stmt = $this->conn->prepare($query);

        // Bind params
        $stmt->bindParam(':order_number', $order->order_number);
        $stmt->bindParam(':user_id', $order->user_id);
        $stmt->bindParam(':contact_number', $order->contact_number);
        $stmt->bindParam(':total_amount', $order->total_amount);
        $stmt->bindParam(':delivery_fee', $order->delivery_fee);
        $stmt->bindParam(':status', $order->status);
        $stmt->bindParam(':payment_status', $order->payment_status);
        $stmt->bindParam(':payment_method', $order->payment_method);
        $stmt->bindParam(':delivery_method', $order->delivery_method);
        $stmt->bindParam(':delivery_address', $order->delivery_address);
        $stmt->bindParam(':delivery_notes', $order->delivery_notes);
        $stmt->bindParam(':paymongo_checkout_session_id', $order->paymongo_checkout_session_id);
        $stmt->bindParam(':paymongo_payment_intent_id', $order->paymongo_payment_intent_id);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function addOrderItem(OrderItem $item)
    {
        $query = "INSERT INTO " . $this->table_order_items . " 
                  (order_id, product_id, product_name, price, quantity, subtotal) 
                  VALUES (:order_id, :product_id, :product_name, :price, :quantity, :subtotal)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':order_id', $item->order_id);
        $stmt->bindParam(':product_id', $item->product_id);
        $stmt->bindParam(':product_name', $item->product_name);
        $stmt->bindParam(':price', $item->price);
        $stmt->bindParam(':quantity', $item->quantity);
        $stmt->bindParam(':subtotal', $item->subtotal);

        return $stmt->execute();
    }

    public function findById($id)
    {
        $query = "SELECT * FROM " . $this->table_orders . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $order = new Order($row);
            $order->items = $this->getOrderItems($id);
            return $order;
        }
        return null;
    }

    public function getOrderItems($orderId)
    {
        $query = "SELECT * FROM " . $this->table_order_items . " WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = new OrderItem($row);
        }
        return $items;
    }

    public function findByUserId($userId)
    {
        $query = "SELECT * FROM " . $this->table_orders . " WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        $orders = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Note: We are not fetching items for all orders here to avoid performance issues if not needed.
            // If items are needed, we can call getOrderItems for each order or a separate join.
            // For the list view, usually main order details are enough.
            $order = new Order($row);
            $orders[] = $order;
        }
        return $orders;
    }
    public function updateStatus($id, $status)
    {
        $query = "UPDATE " . $this->table_orders . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }
    public function updateItemsStatusByOrderId($orderId, $status)
    {
        $query = "UPDATE " . $this->table_order_items . " SET status = :status WHERE order_id = :order_id AND status != 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $orderId);

        return $stmt->execute();
    }

    public function updateOrderItemStatus($itemId, $status)
    {
        $query = "UPDATE " . $this->table_order_items . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $itemId);

        return $stmt->execute();
    }

    public function getOrderIdByItemId($itemId)
    {
        $query = "SELECT order_id FROM " . $this->table_order_items . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $itemId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['order_id'];
        }
        return null;
    }

    public function getOrderItemCountByOrderId($orderId)
    {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_order_items . " WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    public function getSalesOrders($filters, $limit, $offset)
    {
        // Base filter: Only show finished orders (picked_up, delivered, cancelled)
        $where = "o.status IN ('picked_up', 'delivered', 'cancelled')";
        $params = [];

        // Status Filter
        if (!empty($filters['status']) && $filters['status'] !== 'All') {
            $where .= " AND o.status = :status";
            $params[':status'] = strtolower($filters['status']);
        }

        // Payment Filter
        if (!empty($filters['payment']) && $filters['payment'] !== 'All') {
            if ($filters['payment'] === 'Gcash') {
                // Assuming 'gcash' is the value in DB, or payment_method enum
                $where .= " AND o.payment_method = :payment";
                $params[':payment'] = 'gcash';
            } elseif ($filters['payment'] === 'Cash') {
                $where .= " AND o.payment_method = :payment";
                $params[':payment'] = 'cod'; // Assuming 'cod' is Cash on Delivery
            }
        }

        // Date Range Filter
        if (!empty($filters['fromDate'])) {
            $where .= " AND o.created_at >= :fromDate";
            $params[':fromDate'] = $filters['fromDate'] . " 00:00:00";
        }
        if (!empty($filters['toDate'])) {
            $where .= " AND o.created_at <= :toDate";
            $params[':toDate'] = $filters['toDate'] . " 23:59:59";
        }

        $query = "SELECT o.*, u.first_name, u.last_name 
                  FROM " . $this->table_orders . " o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  WHERE $where 
                  ORDER BY o.created_at DESC 
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countSalesOrders($filters)
    {
        // Base filter: Only show finished orders (picked_up, delivered, cancelled)
        $where = "o.status IN ('picked_up', 'delivered', 'cancelled')";
        $params = [];

        if (!empty($filters['status']) && $filters['status'] !== 'All') {
            $where .= " AND o.status = :status";
            $params[':status'] = strtolower($filters['status']);
        }

        if (!empty($filters['payment']) && $filters['payment'] !== 'All') {
            if ($filters['payment'] === 'Gcash') {
                $where .= " AND o.payment_method = :payment";
                $params[':payment'] = 'gcash';
            } elseif ($filters['payment'] === 'Cash') {
                $where .= " AND o.payment_method = :payment";
                $params[':payment'] = 'cod';
            }
        }

        if (!empty($filters['fromDate'])) {
            $where .= " AND o.created_at >= :fromDate";
            $params[':fromDate'] = $filters['fromDate'] . " 00:00:00";
        }
        if (!empty($filters['toDate'])) {
            $where .= " AND o.created_at <= :toDate";
            $params[':toDate'] = $filters['toDate'] . " 23:59:59";
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table_orders . " o WHERE $where";
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
