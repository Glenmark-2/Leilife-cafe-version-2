<?php

require_once __DIR__ . '/../config/Database.php';

class DashboardRepository
{
    private $conn;

    public function __construct($db = null)
    {
        if ($db === null) {
            $database = new Database();
            $this->conn = $database->getConnection();
        } else {
            $this->conn = $db;
        }
    }

    public function getOrderStats()
    {
        $stats = [
            'pending' => 0,
            'preparing' => 0,
            'ready_for_pickup' => 0,
            'out_for_delivery' => 0,
            'picked_up' => 0,
            'delivered' => 0,
            'delivered_today' => 0,
            'picked_up_today' => 0,
            'cancelled' => 0,
            'cancelled_today' => 0
        ];

        // Counts by status (Overall)
        $query = "SELECT status, COUNT(*) as count FROM orders WHERE NOT (payment_method IN ('gcash', 'grab_pay') AND payment_status = 'unpaid') GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status = strtolower($row['status']);
            if (isset($stats[$status])) {
                $stats[$status] = (int)$row['count'];
            }
        }

        // Delivered Today specifically
        $queryToday = "SELECT COUNT(*) as count FROM orders WHERE status = 'delivered' AND DATE(updated_at) = CURDATE()";
        $stmtToday = $this->conn->prepare($queryToday);
        $stmtToday->execute();
        $rowToday = $stmtToday->fetch(PDO::FETCH_ASSOC);
        $stats['delivered_today'] = (int)$rowToday['count'];

        // Picked Up Today specifically
        $queryPickedUpToday = "SELECT COUNT(*) as count FROM orders WHERE status = 'picked_up' AND DATE(updated_at) = CURDATE()";
        $stmtPickedUpToday = $this->conn->prepare($queryPickedUpToday);
        $stmtPickedUpToday->execute();
        $rowPickedUpToday = $stmtPickedUpToday->fetch(PDO::FETCH_ASSOC);
        $stats['picked_up_today'] = (int)$rowPickedUpToday['count'];

        // Cancelled Today specifically
        $queryCancelledToday = "SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled' AND DATE(updated_at) = CURDATE()";
        $stmtCancelledToday = $this->conn->prepare($queryCancelledToday);
        $stmtCancelledToday->execute();
        $rowCancelledToday = $stmtCancelledToday->fetch(PDO::FETCH_ASSOC);
        $stats['cancelled_today'] = (int)$rowCancelledToday['count'];
        
        // Update the main 'cancelled' stat to use the today count if that's what's expected for the dashboard UI
        // or just keep both and let the frontend decide. To be safe based on your request:
        $stats['cancelled'] = $stats['cancelled_today'];

        return $stats;
    }

    public function getStaffStats()
    {
        $query = "SELECT TRIM(LOWER(role)) as role, COUNT(*) as count 
                  FROM staffs 
                  WHERE TRIM(LOWER(status)) = 'active' 
                  AND is_archived = 0 
                  GROUP BY role";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $stats = [
            'admin' => 0,
            'driver' => 0,
            'staff' => 0
        ];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $role = strtolower($row['role']);
            if (isset($stats[$role])) {
                $stats[$role] = (int)$row['count'];
            }
        }

        error_log("DEBUG Staff Stats: " . json_encode($stats));

        return $stats;
    }

    public function getRecentOrders($limit = 10, $filter = 'date_desc')
    {
        $orderBy = "o.created_at DESC";
        // Filter for active orders only: pending, preparing, ready_for_pickup, out_for_delivery
        $where = "o.status IN ('pending', 'preparing', 'ready_for_pickup', 'out_for_delivery') AND NOT (o.payment_method IN ('gcash', 'grab_pay') AND o.payment_status = 'unpaid')";

        switch ($filter) {
            case 'date_asc':
                $orderBy = "o.created_at ASC";
                break;
            case 'total_desc':
                $orderBy = "o.total_amount DESC";
                break;
            case 'total_asc':
                $orderBy = "o.total_amount ASC";
                break;
            case 'status':
                $orderBy = "o.status ASC";
                break;
            case 'type_pickup':
                $where .= " AND o.delivery_method = 'pickup'";
                break;
            case 'type_delivery':
                $where .= " AND o.delivery_method = 'delivery'";
                break;
            case 'date_desc':
            default:
                $orderBy = "o.created_at DESC";
                break;
        }

        $query = "SELECT o.*, u.first_name, u.last_name 
                  FROM orders o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  WHERE $where
                  ORDER BY $orderBy 
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
