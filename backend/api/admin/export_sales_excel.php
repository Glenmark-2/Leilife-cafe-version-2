<?php
// backend/api/admin/export_sales_excel.php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../repositories/OrderRepository.php';
require_once __DIR__ . '/../../repositories/SettingsRepository.php';

// Set Timezone
date_default_timezone_set('Asia/Manila');

// Instantiate DB & Repository
$database = new Database();
$db = $database->getConnection();
$orderRepo = new OrderRepository($db);
$settingsRepo = new SettingsRepository($db);
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

// Fetch Advanced Data (Same as PDF/CSV)
$stats = $orderRepo->getSalesStats($filters);
$topProducts = $orderRepo->getTopSellingProducts($filters, 5);
$detailedData = $orderRepo->getDetailedSalesData($filters);

if (empty($detailedData)) {
    echo "<script>alert('No sales data found for the selected filters.'); window.close();</script>";
    exit;
}

// Set Headers for Excel Download
$filename = "Sales_Analysis_Report_" . date('Ymd_His') . ".xls";
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=' . $filename);
header('Cache-Control: max-age=0');

// Generate HTML represented as Excel
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">

<head>
    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
    <style>
        .header {
            font-size: 18pt;
            font-weight: bold;
            text-align: center;
        }

        .sub-header {
            font-size: 12pt;
            color: #666;
            text-align: center;
        }

        .section-title {
            background-color: #f2f2f2;
            font-weight: bold;
            border: 1px solid #ccc;
            font-size: 14pt;
        }

        .table-head {
            background-color: #24353A;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #000;
        }

        .money {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .category-row {
            background-color: #e6e6e6;
            font-weight: bold;
        }

        .sub-total {
            background-color: #f9f9f9;
            font-weight: bold;
            font-style: italic;
            text-align: right;
        }

        .grand-total {
            background-color: #ffffcc;
            font-weight: bold;
            font-size: 12pt;
            border-top: 2px solid #000;
            text-align: right;
        }

        .green-text {
            color: #228B22;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td colspan="5" class="header"><?php echo strtoupper($settings['store_name'] ?? 'LEILIFE CAFE & RESTO'); ?></td>
        </tr>
        <tr>
            <td colspan="5" class="sub-header">Sales Analysis Report</td>
        </tr>
        <tr>
            <td colspan="5" class="sub-header">Generated on: <?php echo date('F d, Y h:i A'); ?></td>
        </tr>
        <tr>
            <td></td>
        </tr>

        <!-- SALES OVERVIEW -->
        <tr>
            <td colspan="5" class="section-title">SALES OVERVIEW</td>
        </tr>
        <tr>
            <td><b>Status Filter:</b></td>
            <td colspan="4"><?php echo str_replace('_', ' ', $filters['status']); ?></td>
        </tr>
        <tr>
            <td><b>Date Range:</b></td>
            <td colspan="4"><?php echo ($filters['fromDate'] ?: 'Ever') . ' to ' . ($filters['toDate'] ?: 'Present'); ?></td>
        </tr>
        <tr>
            <td><b>Total Orders:</b></td>
            <td colspan="4" style="text-align: left;"><?php echo $stats['total_orders'] ?? 0; ?></td>
        </tr>
        <tr>
            <td><b>Total Revenue:</b></td>
            <td colspan="4" class="green-text">P <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></td>
        </tr>
        <tr>
            <td></td>
        </tr>

        <!-- TOP 5 PRODUCTS -->
        <?php if (!empty($topProducts)): ?>
            <tr>
                <td colspan="5" class="section-title">TOP 5 SELLING PRODUCTS</td>
            </tr>
            <tr>
                <td colspan="3" class="table-head">Product Name</td>
                <td class="table-head">Qty Sold</td>
                <td class="table-head">Revenue</td>
            </tr>
            <?php foreach ($topProducts as $tp): ?>
                <tr>
                    <td colspan="3"><?php echo htmlspecialchars($tp['product_name']); ?></td>
                    <td class="center"><?php echo $tp['qty_sold']; ?></td>
                    <td class="money">P <?php echo number_format($tp['total_revenue'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td></td>
            </tr>
        <?php endif; ?>

        <!-- CATEGORY BREAKDOWN -->
        <tr>
            <td colspan="5" class="section-title">CATEGORY PERFORMANCE</td>
        </tr>
        <tr>
            <td colspan="3" class="table-head">Main Category</td>
            <td class="table-head">Orders</td>
            <td class="table-head">Total Revenue</td>
        </tr>
        <?php
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

        foreach ($mainCategories as $name => $mStats): ?>
            <tr>
                <td colspan="3" class="category-row"><?php echo htmlspecialchars($name); ?></td>
                <td class="center category-row"><?php echo $mStats['orders']; ?></td>
                <td class="money category-row">P <?php echo number_format($mStats['revenue'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td></td>
        </tr>

        <!-- DETAILED PRODUCT SALES -->
        <tr>
            <td colspan="5" class="section-title">DETAILED PRODUCT SALES (PER SUB-CATEGORY)</td>
        </tr>
        <?php foreach ($mainCategories as $mName => $mStats): ?>
            <tr>
                <td colspan="5" style="font-weight: bold; color: #24353A; font-size: 11pt; padding-top: 10px;">MAIN CATEGORY: <?php echo strtoupper($mName); ?></td>
            </tr>
            <?php foreach ($mStats['subs'] as $sName => $products): ?>
                <tr>
                    <td colspan="5" style="color: #666; font-weight: bold;">Sub-Category: <?php echo $sName; ?></td>
                </tr>
                <tr>
                    <td class="table-head">Product</td>
                    <td class="table-head">Price Sold</td>
                    <td class="table-head">Qty Sold</td>
                    <td class="table-head">Orders</td>
                    <td class="table-head">Revenue</td>
                </tr>
                <?php
                $subRevenue = 0;
                foreach ($products as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['product_name']); ?></td>
                        <td class="money">P <?php echo number_format($p['price'], 2); ?></td>
                        <td class="center"><?php echo $p['qty_sold']; ?></td>
                        <td class="center"><?php echo $p['orders_count']; ?></td>
                        <td class="money">P <?php echo number_format($p['total_revenue'], 2); ?></td>
                    </tr>
                <?php $subRevenue += $p['total_revenue'];
                endforeach; ?>
                <tr>
                    <td colspan="4" class="money sub-total">SUB-TOTAL <?php echo strtoupper($sName); ?>:</td>
                    <td class="money sub-total">P <?php echo number_format($subRevenue, 2); ?></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <!-- GRAND SUMMARY -->
        <tr>
            <td colspan="5" class="section-title">FINAL GRAND SUMMARY</td>
        </tr>
        <tr>
            <td colspan="4" class="money">TOTAL PRODUCT REVENUE:</td>
            <td class="money">P <?php echo number_format($stats['total_revenue'] - ($stats['total_delivery_fees'] ?? 0), 2); ?></td>
        </tr>
        <tr>
            <td colspan="4" class="money">TOTAL DELIVERY FEES:</td>
            <td class="money">P <?php echo number_format($stats['total_delivery_fees'] ?? 0, 2); ?></td>
        </tr>
        <tr>
            <td colspan="4" class="money grand-total">GRAND TOTAL SALES:</td>
            <td class="money grand-total">P <?php echo number_format($stats['total_revenue'], 2); ?></td>
        </tr>
    </table>
</body>

</html>
<?php exit; ?>