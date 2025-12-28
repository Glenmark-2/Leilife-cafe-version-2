<?php
// backend/api/admin/export_sales_pdf.php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';
require_once __DIR__ . '/../../repositories/SettingsRepository.php';
require_once __DIR__ . '/../../fpdf186/fpdf.php';

// Set Timezone to Local Time
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

// Force exclude cancelled orders if they somehow pass through JS validation
if (strtolower($filters['status']) === 'cancelled') {
    die("Validation Error: Cannot export cancelled orders as a sales report.");
}

// Fetch Data for Advanced Report
$stats = $orderRepo->getSalesStats($filters);
$topProducts = $orderRepo->getTopSellingProducts($filters, 5);
$detailedData = $orderRepo->getDetailedSalesData($filters);

if (empty($detailedData)) {
    echo "<script>alert('No sales data found for the selected filters.'); window.close();</script>";
    exit;
}

// Generate PDF
class SalesPDF extends FPDF
{
    protected $settings;

    public function setStoreSettings($settings)
    {
        $this->settings = $settings;
    }

    function Header()
    {
        $logo = __DIR__ . '/../../../public/assets/leilife.png';
        if (file_exists($logo)) {
            $this->Image($logo, 10, 6, 20);
        }

        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(36, 53, 58); // #24353A
        $this->Cell(0, 10, strtoupper($this->settings['store_name'] ?? 'LEILIFE CAFE & RESTO'), 0, 1, 'C');

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, $this->settings['physical_address'] ?? 'Caloocan City, Philippines', 0, 1, 'C');
        $this->Cell(0, 5, 'Contact: ' . ($this->settings['contact_phone'] ?? '09123456789'), 0, 1, 'C');

        $this->Ln(8);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'L');
        $this->Cell(0, 10, 'Generated on ' . date('F d, Y h:i A'), 0, 0, 'R');
    }

    function SectionTitle($title)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(245, 245, 245);
        $this->SetTextColor(36, 53, 58);
        $this->Cell(0, 10, '  ' . strtoupper($title), 0, 1, 'L', true);
        $this->Ln(3);
    }
}

$pdf = new SalesPDF();
$pdf->setStoreSettings($settings);
$pdf->AliasNbPages();
$pdf->AddPage();

// --- SALES OVERVIEW SECTION ---
$pdf->SectionTitle('Sales Overview');
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);

$pdf->Cell(45, 10, 'Status Filter:', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 10, str_replace('_', ' ', $filters['status']), 0, 0);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 10, 'Total Orders:', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 10, number_format($stats['total_orders'] ?? 0), 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 10, 'Date Range:', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$dateRange = ($filters['fromDate'] ?: 'Ever') . ' to ' . ($filters['toDate'] ?: 'Present');
$pdf->Cell(50, 10, $dateRange, 0, 0);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(45, 10, 'Total Revenue:', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(34, 139, 34); // ForestGreen
$pdf->Cell(50, 10, 'P ' . number_format($stats['total_revenue'] ?? 0, 2), 0, 1);
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(5);

// --- TOP 5 PRODUCTS SECTION ---
if (!empty($topProducts)) {
    $pdf->SectionTitle('Top 5 Selling Products');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(36, 53, 58);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(100, 8, 'Product Name', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Qty Sold', 1, 0, 'C', true);
    $pdf->Cell(50, 8, 'Revenue', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    foreach ($topProducts as $tp) {
        $pdf->Cell(100, 8, ' ' . $tp['product_name'], 1, 0, 'L');
        $pdf->Cell(40, 8, $tp['qty_sold'], 1, 0, 'C');
        $pdf->Cell(50, 8, 'P ' . number_format($tp['total_revenue'], 2), 1, 1, 'R');
    }
    $pdf->Ln(10);
}

// --- CATEGORY BREAKDOWN SECTION ---
$pdf->SectionTitle('Category Sales Performance');

// Group data by Main Category (Parent)
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

// Display Main Categories Summary
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(90, 8, 'Main Category', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Orders', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Total Revenue', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
foreach ($mainCategories as $name => $mStats) {
    $pdf->Cell(90, 8, ' ' . $name, 1, 0, 'L');
    $pdf->Cell(50, 8, $mStats['orders'], 1, 0, 'C');
    $pdf->Cell(50, 8, 'P ' . number_format($mStats['revenue'], 2), 1, 1, 'R');
}
$pdf->Ln(10);

// --- DETAILED PRODUCT LIST PER SUB-CATEGORY ---
$pdf->SectionTitle('Detailed Product Sales (Per Sub-Category)');

foreach ($mainCategories as $mName => $mStats) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor(36, 53, 58);
    $pdf->Cell(0, 10, 'MAIN CATEGORY: ' . strtoupper($mName), 0, 1, 'L');

    foreach ($mStats['subs'] as $sName => $products) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 8, 'Sub-Category: ' . $sName, 0, 1, 'L');

        // Product Table Header
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(100, 110, 115);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(65, 8, 'Product', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Price', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Qty Sold', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Orders', 1, 0, 'C', true);
        $pdf->Cell(50, 8, 'Revenue', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $subRevenue = 0;
        foreach ($products as $p) {
            $pdf->Cell(65, 8, ' ' . $p['product_name'], 1, 0, 'L');
            $pdf->Cell(25, 8, 'P' . number_format($p['price'], 2), 1, 0, 'R');
            $pdf->Cell(25, 8, $p['qty_sold'], 1, 0, 'C');
            $pdf->Cell(25, 8, $p['orders_count'], 1, 0, 'C');
            $pdf->Cell(50, 8, 'P ' . number_format($p['total_revenue'], 2), 1, 1, 'R');
            $subRevenue += $p['total_revenue'];
        }

        // Sub-category Total
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(140, 8, 'SUB-TOTAL ' . strtoupper($sName) . ':', 1, 0, 'R');
        $pdf->Cell(50, 8, 'P ' . number_format($subRevenue, 2), 1, 1, 'R');
        $pdf->Ln(5);
    }
    $pdf->Ln(5);
}

// --- FINAL GRAND SUMMARY ---
if ($pdf->GetY() > 230) {
    $pdf->AddPage();
} // New page if near bottom
$pdf->Ln(10);
$pdf->SectionTitle('Final Grand Summary');

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(140, 10, 'TOTAL PRODUCT REVENUE:', 0, 0, 'R');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(50, 10, 'P ' . number_format($stats['total_revenue'] - ($stats['total_delivery_fees'] ?? 0), 2), 0, 1, 'R');

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(140, 10, 'TOTAL DELIVERY FEES:', 0, 0, 'R');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(50, 10, 'P ' . number_format($stats['total_delivery_fees'] ?? 0, 2), 0, 1, 'R');

$pdf->SetDrawColor(36, 53, 58);
$pdf->SetLineWidth(0.5);
$pdf->Line(130, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(36, 53, 58);
$pdf->Cell(140, 12, 'GRAND TOTAL SALES:', 0, 0, 'R');
$pdf->SetFillColor(255, 255, 200);
$pdf->Cell(50, 12, 'P ' . number_format($stats['total_revenue'], 2), 0, 1, 'R', true);

// Output
$pdf->Output('I', 'Sales_Analysis_Report_' . date('Ymd') . '.pdf');
