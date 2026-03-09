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
        // Count unarchived Admins based on join with admins table
        $queryAdmin = "SELECT COUNT(*) as count 
                      FROM staffs s 
                      JOIN admins a ON s.staff_id = a.staff_id 
                      WHERE s.is_archived = 0";
        $stmtAdmin = $this->conn->prepare($queryAdmin);
        $stmtAdmin->execute();
        $adminCount = $stmtAdmin->fetch(PDO::FETCH_ASSOC)['count'];

        // Count unarchived Drivers based on join with drivers table
        $queryDriver = "SELECT COUNT(*) as count 
                       FROM staffs s 
                       JOIN drivers d ON s.staff_id = d.staff_id 
                       WHERE s.is_archived = 0";
        $stmtDriver = $this->conn->prepare($queryDriver);
        $stmtDriver->execute();
        $driverCount = $stmtDriver->fetch(PDO::FETCH_ASSOC)['count'];

        // Count other unarchived Staff members (those without admin/driver accounts)
        $queryStaff = "SELECT COUNT(*) as count 
                      FROM staffs s 
                      LEFT JOIN admins a ON s.staff_id = a.staff_id 
                      LEFT JOIN drivers d ON s.staff_id = d.staff_id 
                      WHERE s.is_archived = 0 
                      AND a.staff_id IS NULL AND d.staff_id IS NULL";
        $stmtStaff = $this->conn->prepare($queryStaff);
        $stmtStaff->execute();
        $staffCount = $stmtStaff->fetch(PDO::FETCH_ASSOC)['count'];

        return [
            'admin' => (int)$adminCount,
            'driver' => (int)$driverCount,
            'staff' => (int)$staffCount
        ];
    }

    public function countRecentOrders($filter = 'date_desc')
    {
        $where = "o.status IN ('pending', 'preparing', 'ready_for_pickup', 'out_for_delivery')";

        switch ($filter) {
            case 'type_pickup':
                $where .= " AND o.delivery_method = 'pickup'";
                break;
            case 'type_delivery':
                $where .= " AND o.delivery_method = 'delivery'";
                break;
        }

        $query = "SELECT COUNT(*) as total FROM orders o WHERE $where";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function getRecentOrders($limit = 10, $offset = 0, $filter = 'date_desc')
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
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
