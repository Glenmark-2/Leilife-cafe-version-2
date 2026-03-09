<?php

require_once __DIR__ . '/../repositories/DashboardRepository.php';

class DashboardService
{
    private $dashboardRepo;

    public function __construct($db = null)
    {
        $this->dashboardRepo = new DashboardRepository($db);
    }

    public function getDashboardData($page = 1, $limit = 10, $filter = 'date_desc')
    {
        $offset = ($page - 1) * $limit;

        return [
            'orderStats' => $this->dashboardRepo->getOrderStats(),
            'staffStats' => $this->dashboardRepo->getStaffStats(),
            'totalRecentOrders' => $this->dashboardRepo->countRecentOrders($filter),
            'recentOrders' => $this->dashboardRepo->getRecentOrders($limit, $offset, $filter)
        ];
    }
}
