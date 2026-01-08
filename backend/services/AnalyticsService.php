<?php
require_once __DIR__ . '/../repositories/AnalyticsRepository.php';

class AnalyticsService {
    private $analyticsRepo;

    public function __construct($db = null) {
        $this->analyticsRepo = new AnalyticsRepository($db);
    }

    public function getAnalyticsData($fromDate, $toDate) {
        $trend = $this->analyticsRepo->getSalesTrend($fromDate, $toDate);
        $summary = $this->analyticsRepo->getSummaryStats($fromDate, $toDate);
        $categories = $this->analyticsRepo->getRevenueByCategory($fromDate, $toDate);

        return [
            'trend' => $trend,
            'summary' => $summary,
            'categories' => $categories
        ];
    }
}
