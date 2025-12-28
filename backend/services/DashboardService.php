<?php

require_once __DIR__ . '/../repositories/DashboardRepository.php';

class DashboardService
{
    private $dashboardRepo;

    public function __construct($db = null)
    {
        $this->dashboardRepo = new DashboardRepository($db);
    }

    public function getDashboardData($filter = 'date_desc')
    {
        return [
            'orderStats' => $this->dashboardRepo->getOrderStats(),
            'staffStats' => $this->dashboardRepo->getStaffStats(),
            'recentOrders' => $this->dashboardRepo->getRecentOrders(10, $filter)
        ];
    }
}
