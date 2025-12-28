<?php
// backend/api/admin/export_sales_csv.php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';
require_once __DIR__ . '/../../repositories/SettingsRepository.php';

// Set Timezone
date_default_timezone_set('Asia/Manila');

// Instantiate DB & Repository
$database = new Database();
$db = $database->getConnection();
$orderRepo = new OrderRepository($db);
$settingsRepo = new SettingsRepository();
$settings = $settingsRepo->getSettings();

// Get filters from GET request
$filters = [
    'status' => $_GET['status'] ?? 'All',
    'payment' => $_GET['payment'] ?? 'All',
    'fromDate' => $_GET['fromDate'] ?? null,
    'toDate' => $_GET['toDate'] ?? null
];

// Force exclude cancelled orders
if (strtolower($filters['status']) === 'cancelled') {
    die("Validation Error: Cannot export cancelled orders as a sales report.");
}

// Fetch Advanced Data (Same as PDF)
$stats = $orderRepo->getSalesStats($filters);
$topProducts = $orderRepo->getTopSellingProducts($filters, 5);
$detailedData = $orderRepo->getDetailedSalesData($filters);

if (empty($detailedData)) {
    echo "<script>alert('No sales data found for the selected filters.'); window.close();</script>";
    exit;
}

// Set Headers for CSV Download
$filename = "Sales_Analysis_Report_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// --- SECTION 1: REPORT HEADER ---
fputcsv($output, [strtoupper($settings['store_name'] ?? 'LEILIFE CAFE & RESTO')]);
fputcsv($output, ['Sales Analysis Report']);
fputcsv($output, ['Generated on:', date('F d, Y h:i A')]);
fputcsv($output, []); // Empty line

// --- SECTION 2: SALES OVERVIEW ---
fputcsv($output, ['--- SALES OVERVIEW ---']);
fputcsv($output, ['Status Filter', str_replace('_', ' ', $filters['status'])]);
fputcsv($output, ['Date Range', ($filters['fromDate'] ?: 'Ever') . ' to ' . ($filters['toDate'] ?: 'Present')]);
fputcsv($output, ['Total Orders', $stats['total_orders'] ?? 0]);
fputcsv($output, ['Total Revenue', number_format($stats['total_revenue'] ?? 0, 2)]);
fputcsv($output, []);

// --- SECTION 3: TOP 5 SELLING PRODUCTS ---
if (!empty($topProducts)) {
    fputcsv($output, ['--- TOP 5 SELLING PRODUCTS ---']);
    fputcsv($output, ['Product Name', 'Qty Sold', 'Revenue']);
    foreach ($topProducts as $tp) {
        fputcsv($output, [$tp['product_name'], $tp['qty_sold'], number_format($tp['total_revenue'], 2)]);
    }
    fputcsv($output, []);
}

// --- SECTION 4: MAIN CATEGORY PERFORMANCE ---
fputcsv($output, ['--- MAIN CATEGORY PERFORMANCE ---']);
fputcsv($output, ['Main Category', 'Orders', 'Total Revenue']);

$mainCategories = [];
foreach ($detailedData as $row) {
    $mCat = $row['parent_category_name'] ?: 'General';
    if (!isset($mainCategories[$mCat])) {
        $mainCategories[$mCat] = ['orders' => 0, 'revenue' => 0, 'subs' => []];
    }
    $mainCategories[$mCat]['revenue'] += $row['total_revenue'];
    $mainCategories[$mCat]['orders'] += $row['orders_count'];

    $sCat = $row['category_name'];
    if (!isset($mainCategories[$mCat]['subs'][$sCat])) {
        $mainCategories[$mCat]['subs'][$sCat] = [];
    }
    $mainCategories[$mCat]['subs'][$sCat][] = $row;
}

foreach ($mainCategories as $name => $mStats) {
    fputcsv($output, [$name, $mStats['orders'], number_format($mStats['revenue'], 2)]);
}
fputcsv($output, []);

// --- SECTION 5: DETAILED PRODUCT SALES PER SUB-CATEGORY ---
fputcsv($output, ['--- DETAILED PRODUCT SALES (PER SUB-CATEGORY) ---']);

foreach ($mainCategories as $mName => $mStats) {
    fputcsv($output, ['MAIN CATEGORY: ' . strtoupper($mName)]);

    foreach ($mStats['subs'] as $sName => $products) {
        fputcsv($output, ['Sub-Category: ' . $sName]);
        fputcsv($output, ['Product', 'Price Sold', 'Qty Sold', 'Orders', 'Revenue']);

        $subRevenue = 0;
        foreach ($products as $p) {
            fputcsv($output, [
                $p['product_name'],
                number_format($p['price'], 2),
                $p['qty_sold'],
                $p['orders_count'],
                number_format($p['total_revenue'], 2)
            ]);
            $subRevenue += $p['total_revenue'];
        }
        fputcsv($output, ['', '', '', 'SUB-TOTAL:', number_format($subRevenue, 2)]);
        fputcsv($output, []); // Small gap between sub-categories
    }
}

// --- SECTION 6: FINAL GRAND SUMMARY ---
fputcsv($output, ['--- FINAL GRAND SUMMARY ---']);
fputcsv($output, ['TOTAL PRODUCT REVENUE', number_format($stats['total_revenue'] - ($stats['total_delivery_fees'] ?? 0), 2)]);
fputcsv($output, ['TOTAL DELIVERY FEES', number_format($stats['total_delivery_fees'] ?? 0, 2)]);
fputcsv($output, ['GRAND TOTAL SALES', number_format($stats['total_revenue'], 2)]);

fclose($output);
exit;
