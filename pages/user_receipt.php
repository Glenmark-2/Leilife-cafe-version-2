<?php
// NOTE: FPDF library path must be correct for this to work
// Assuming FPDF is installed and accessible at this relative path
require __DIR__ . '../../backend/fpdf186/fpdf.php'; 
// --- HARD CODED SAMPLE DATA ---
// Data is structured exactly as if it were retrieved from the database
$order_number = 'LLCR-12345';

$order_info = [
    'order' => [
        'order_number' => $order_number,
        'delivery_method' => 'home', // Using 'home' to test delivery fee and address
        'payment_method' => 'cash on delivery',
        'order_date' => '2025-12-13 14:30:00',
        'customer_name' => 'Juan Dela Cruz',
        'delivery_address' => '456 Acacia St., Brgy. 1, Sampaloc, Manila',
    ],
    'items' => [
        [
            'product_name' => 'iced latte',
            'flavors' => ['vanilla'],
            'size' => 'large',
            'quantity' => 2,
            'price' => 150.00,
        ],
        [
            'product_name' => 'chicken sandwich',
            'flavors' => [],
            'size' => '',
            'quantity' => 1,
            'price' => 180.00,
        ],
        [
            'product_name' => 'choco brownie',
            'flavors' => [],
            'size' => '',
            'quantity' => 3,
            'price' => 50.00,
        ],
    ],
    // Calculated totals: (2*150) + (1*180) + (3*50) = 300 + 180 + 150 = 630.00
    'subtotal' => 630.00,
    'delivery_fee' => 50.00,
];

// --- FPDF GENERATION ---

// Create a standard A4 PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// ---- HEADER ----
// NOTE: Ensure your logo path is correct relative to where this script runs
// $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/Leilife/public/assests/leilife.png';
// $pdf->Image($imagePath, 30, 5, 20);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'LEILIFE CAFE & RESTO', 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(77, 81, 86);
$pdf->Cell(0, 5, '123 Coffee Street, Manila', 0, 1, 'C');
$pdf->Cell(0, 5, 'Tel: 0912-345-6789', 0, 1, 'C');
$pdf->Cell(0, 5, 'TIN: 123-456-789', 0, 1, 'C');
$pdf->Ln(10);

// ---- ORDER INFO SETUP ----
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);

$order = $order_info['order'];
$on = $order['order_number'];

$order_type = ($order['delivery_method'] ?? '') === 'home' ? 'Delivery' : 'Pick up';

$formatted_date = !empty($order['order_date'])
    ? date('F d, Y h:i A', strtotime($order['order_date']))
    : '';

$orderDetails = [
    ['date' => $formatted_date],
    ['order_number' => $on ?? ''],
    ['order_type' => $order_type],
    ['payment_method' => !empty($order['payment_method']) ? ucwords($order['payment_method']) : ''],
    ['customer' => !empty($order['customer_name']) ? ucwords(strtolower($order['customer_name'])) : 'Unknown Customer'],
];

// Add delivery address only if order type is Delivery
if ($order_type === 'Delivery') {
    $orderDetails[] = ['delivery_address' => !empty($order['delivery_address']) ? ucwords(($order['delivery_address'])) : ''];
}

// ---- PRINT ORDER INFO ----
foreach ($orderDetails as $detail) {
    foreach ($detail as $label => $value) {
        $labelFormatted = ucwords(str_replace('_', ' ', $label));
        $value = $value ?? '';
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 6, $labelFormatted . ':', 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 6, $value, 0, 'L');
    }
}
$pdf->Ln(8);

// ---- ORDER SUMMARY HEADER ----
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'ORDER SUMMARY', 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 8, 'QTY', 'B', 0, 'C');
$pdf->Cell(110, 8, 'ITEM', 'B', 0, 'L');
$pdf->Cell(40, 8, 'PRICE', 'B', 1, 'R');

// ---- PRINT ORDER ITEMS ----
$pdf->SetFont('Arial', '', 10);
foreach ($order_info['items'] as $item) {
    $productName = ucwords(strtolower($item['product_name']));

    $flavors = !empty($item['flavors']) 
        ? ' (' . implode(', ', array_map(function($f){ return ucwords(strtolower($f)); }, $item['flavors'])) . ')' 
        : '';

    $size = !empty($item['size']) ? ' - ' . ucwords(strtolower($item['size'])) : '';
    $itemPrice = number_format($item['price'] * $item['quantity'], 2); // Calculate total price per line item

    $pdf->Cell(20, 6, $item['quantity'], 0, 0, 'C');
    $pdf->Cell(110, 6, $productName . $flavors . $size, 0, 0, 'L');
    $pdf->Cell(40, 6, $itemPrice, 0, 1, 'R');
}

$pdf->Ln(5);

// ---- TOTALS ----
$subtotal = number_format($order_info['subtotal'] ?? 0, 2, '.', '');
$deliveryFee = number_format($order_info['delivery_fee'] ?? 0, 2, '.', '');
$total = number_format(($order_info['subtotal'] + $order_info['delivery_fee']) ?? 0, 2, '.', '');

// Add a separating line
$pdf->SetLineWidth(0.5);
$pdf->Line(150, $pdf->GetY(), 200, $pdf->GetY()); 
$pdf->Ln(1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(130, 6, "Subtotal", 0, 0, 'R');
$pdf->Cell(40, 6, $subtotal, 0, 1, 'R');

if ($order_type === "Delivery") {
    $pdf->Cell(130, 6, "Delivery Fee", 0, 0, 'R');
    $pdf->Cell(40, 6, $deliveryFee, 0, 1, 'R');
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(130, 8, "TOTAL AMOUNT DUE", 0, 0, 'R');
$pdf->Cell(40, 8, 'Php ' . $total, 0, 1, 'R');

$pdf->Ln(10);

// ---- FOOTER ----
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(77, 81, 86);
$pdf->Cell(0, 6, "Thank you for your purchase!", 0, 1, 'C');

$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 6, "This is a system-generated receipt.", 0, 1, 'C');


// Output: 'I' for display in browser, 'D' for forcing a download
$pdf->Output('I', 'receipt_' . $on . '.pdf');

exit;