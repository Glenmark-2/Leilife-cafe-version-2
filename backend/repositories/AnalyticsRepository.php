<?php
require_once __DIR__ . '/../config/Database.php';

class AnalyticsRepository {
    private $conn;

    public function __construct($db = null) {
        if ($db === null) {
            $database = new Database();
            $this->conn = $database->getConnection();
        } else {
            $this->conn = $db;
        }
    }

    public function getSalesTrend($fromDate, $toDate) {
        // Adjust toDate to include the full day
        $toDateTime = $toDate . ' 23:59:59';
        $fromDateTime = $fromDate . ' 00:00:00';

        $isSameDay = ($fromDate === $toDate);

        if ($isSameDay) {
            // Hourly trend for a single day
            $query = "SELECT HOUR(created_at) as time_unit, SUM(total_amount) as total_sales 
                      FROM orders 
                      WHERE created_at BETWEEN :fromDate AND :toDate 
                      AND status != 'cancelled'
                      GROUP BY HOUR(created_at) 
                      ORDER BY time_unit ASC";
        } else {
            // Daily trend for a range
            $query = "SELECT DATE(created_at) as time_unit, SUM(total_amount) as total_sales 
                      FROM orders 
                      WHERE created_at BETWEEN :fromDate AND :toDate 
                      AND status != 'cancelled'
                      GROUP BY DATE(created_at) 
                      ORDER BY time_unit ASC";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fromDate', $fromDateTime);
        $stmt->bindParam(':toDate', $toDateTime);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRevenueByCategory($fromDate, $toDate) {
        $toDateTime = $toDate . ' 23:59:59';
        $fromDateTime = $fromDate . ' 00:00:00';

        // Join order_items with products and categories to get revenue per category
        // We look at parent categories for higher level overview
        $query = "SELECT parent.name as category_name, SUM(oi.price * oi.quantity) as revenue
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.product_id
                  JOIN categories c ON p.category_id = c.category_id
                  JOIN categories parent ON c.parent_id = parent.category_id
                  JOIN orders o ON oi.order_id = o.id
                  WHERE o.created_at BETWEEN :fromDate AND :toDate
                  AND o.status != 'cancelled'
                  GROUP BY parent.category_id
                  ORDER BY revenue DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fromDate', $fromDateTime);
        $stmt->bindParam(':toDate', $toDateTime);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummaryStats($fromDate, $toDate) {
        $toDateTime = $toDate . ' 23:59:59';
        $fromDateTime = $fromDate . ' 00:00:00';

        // Total Sales & Orders
        $query = "SELECT SUM(total_amount) as total_sales, COUNT(*) as total_orders 
                  FROM orders 
                  WHERE created_at BETWEEN :fromDate AND :toDate 
                  AND status != 'cancelled'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fromDate', $fromDateTime);
        $stmt->bindParam(':toDate', $toDateTime);
        $stmt->execute();
        $baseStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Top Product
        $queryProduct = "SELECT p.name, SUM(oi.quantity) as total_sold 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.product_id 
                        JOIN orders o ON oi.order_id = o.id 
                        WHERE o.created_at BETWEEN :fromDate AND :toDate 
                        AND o.status != 'cancelled'
                        GROUP BY p.product_id 
                        ORDER BY total_sold DESC LIMIT 1";
        $stmtProd = $this->conn->prepare($queryProduct);
        $stmtProd->bindParam(':fromDate', $fromDateTime);
        $stmtProd->bindParam(':toDate', $toDateTime);
        $stmtProd->execute();
        $topProduct = $stmtProd->fetch(PDO::FETCH_ASSOC);

        // Top Customer
        $queryCustomer = "SELECT CONCAT(u.first_name, ' ', u.last_name) as name, COUNT(o.id) as order_count
                         FROM orders o
                         JOIN users u ON o.user_id = u.id
                         WHERE o.created_at BETWEEN :fromDate AND :toDate
                         AND o.status != 'cancelled'
                         GROUP BY o.user_id
                         ORDER BY order_count DESC LIMIT 1";
        $stmtCust = $this->conn->prepare($queryCustomer);
        $stmtCust->bindParam(':fromDate', $fromDateTime);
        $stmtCust->bindParam(':toDate', $toDateTime);
        $stmtCust->execute();
        $topCustomer = $stmtCust->fetch(PDO::FETCH_ASSOC);

        $totalSales = $baseStats['total_sales'] ?? 0;
        $totalOrders = $baseStats['total_orders'] ?? 0;

        return [
            'totalSales' => (float)$totalSales,
            'totalOrders' => (int)$totalOrders,
            'avgOrderValue' => $totalOrders > 0 ? (float)($totalSales / $totalOrders) : 0,
            'topProduct' => $topProduct['name'] ?? 'N/A',
            'topCustomer' => $topCustomer['name'] ?? 'N/A',
            'revenueGrowth' => 0 // Placeholder or implement comparison with previous period
        ];
    }
}
