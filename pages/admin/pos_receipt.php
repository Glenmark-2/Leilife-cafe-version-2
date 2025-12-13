<?php
// NOTE: Adjust path to FPDF as needed relative to this file
// pages/admin/pos_receipt.php -> root/backend/fpdf186/fpdf.php
require __DIR__ . '/../../backend/fpdf186/fpdf.php';

// --- HARD CODED SAMPLE DATA (Same as user_receipt.php) ---
$order_number = 'LLCR-12345';

$order_info = [
    'order' => [
        'order_number' => $order_number,
        'delivery_method' => 'home', 
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
    'subtotal' => 630.00,
    'delivery_fee' => 50.00,
];

// --- POS RECEIPT GENERATION ---

// Standard POS paper width is 80mm. 
// Height is arbitrary for continuous roll, but we must set a page size.
// We'll estimate height or use a long page.
$pdf = new FPDF('P', 'mm', array(80, 250)); 
$pdf->AddPage();
$pdf->SetMargins(3, 3, 3);
$pdf->SetAutoPageBreak(true, 5);

// ---- HEADER ----
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetXY(3, 5); // Start near top
$pdf->Cell(0, 5, 'LEILIFE CAFE & RESTO', 0, 1, 'C');

$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 4, '123 Coffee Street, Manila', 0, 1, 'C');
$pdf->Cell(0, 4, 'Tel: 0912-345-6789', 0, 1, 'C');
$pdf->Cell(0, 4, 'TIN: 123-456-789', 0, 1, 'C');
$pdf->Ln(3);

// ---- ORDER INFO ----
$pdf->SetFont('Arial', '', 8);
$order = $order_info['order'];

$pdf->Cell(0, 4, 'Date: ' . date('m/d/Y h:i A', strtotime($order['order_date'])), 0, 1, 'L');
$pdf->Cell(0, 4, 'Order #: ' . $order['order_number'], 0, 1, 'L');
$pdf->Cell(0, 4, 'Customer: ' . ucwords(strtolower($order['customer_name'])), 0, 1, 'L');

$order_type = ($order['delivery_method'] ?? '') === 'home' ? 'Delivery' : 'Pick up';
$pdf->Cell(0, 4, 'Type: ' . $order_type, 0, 1, 'L');

if ($order_type === 'Delivery' && !empty($order['delivery_address'])) {
    $pdf->MultiCell(0, 4, 'Addr: ' . ucwords($order['delivery_address']), 0, 'L');
}
$pdf->Ln(2);

// ---- SEPARATOR ----
$pdf->Cell(0, 0, str_repeat('-', 42), 0, 1, 'C'); 
$pdf->Ln(2);

// ---- ITEMS HEADER ----
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(8, 4, 'Qty', 0, 0, 'L');
$pdf->Cell(42, 4, 'Item', 0, 0, 'L'); // Adjusted width for 80mm (approx 74mm printable)
$pdf->Cell(24, 4, 'Price', 0, 1, 'R');
$pdf->Ln(1);

// ---- ITEMS BODY ----
$pdf->SetFont('Arial', '', 8);

foreach ($order_info['items'] as $item) {
    $pdf->Cell(8, 4, $item['quantity'], 0, 0, 'L');
    
    // Construct item name with details
    $productName = ucwords(strtolower($item['product_name']));
    $details = [];
    if (!empty($item['flavors'])) {
        $details[] = implode(', ', $item['flavors']);
    }
    if (!empty($item['size'])) {
        $details[] = $item['size'];
    }
    
    $fullName = $productName;
    if (!empty($details)) {
        $fullName .= ' (' . implode(', ', $details) . ')';
    }
    
    // Save current Y to align price with the first line of the multicell
    $yPos = $pdf->GetY();
    $xPos = $pdf->GetX();
    
    // Print Product description wrapped
    $pdf->MultiCell(42, 4, $fullName, 0, 'L');
    
    // Get the new Y after multicell
    $newY = $pdf->GetY();
    
    // Move back to print price at the top-right of this row
    $pdf->SetXY($xPos + 42, $yPos);
    
    $itemTotal = number_format($item['price'] * $item['quantity'], 2);
    $pdf->Cell(24, 4, $itemTotal, 0, 1, 'R');
    
    // Set Y to the new position if MultiCell pushed it down
    if ($newY > $pdf->GetY()) {
        $pdf->SetY($newY);
    }
}

$pdf->Ln(2);
$pdf->Cell(0, 0, str_repeat('-', 42), 0, 1, 'C');
$pdf->Ln(2);

// ---- TOTALS ----
$pdf->SetFont('Arial', '', 9);

$subtotal = number_format($order_info['subtotal'], 2);
$deliveryFee = number_format($order_info['delivery_fee'], 2);
$total = number_format($order_info['subtotal'] + $order_info['delivery_fee'], 2);

$pdf->Cell(45, 5, 'Subtotal:', 0, 0, 'R');
$pdf->Cell(29, 5, $subtotal, 0, 1, 'R');

if ($order_type === 'Delivery') {
    $pdf->Cell(45, 5, 'Delivery Fee:', 0, 0, 'R');
    $pdf->Cell(29, 5, $deliveryFee, 0, 1, 'R');
}

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(45, 6, 'TOTAL:', 0, 0, 'R');
$pdf->Cell(29, 6, $total, 0, 1, 'R');

$pdf->Ln(5);

// ---- FOOTER ----
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 4, 'Thank you for your purchase!', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 7);
$pdf->Cell(0, 4, 'System-generated receipt', 0, 1, 'C');

$pdf->Output('I', 'pos_receipt_' . $order['order_number'] . '.pdf');
