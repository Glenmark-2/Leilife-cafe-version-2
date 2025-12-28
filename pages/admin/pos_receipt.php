<?php
// pages/admin/pos_receipt.php
require_once __DIR__ . '/../../backend/fpdf186/fpdf.php';
require_once __DIR__ . '/../../backend/config/Database.php';
require_once __DIR__ . '/../../backend/repositories/OrderRepository.php';
require_once __DIR__ . '/../../backend/repositories/SettingsRepository.php';

// 1. Validate Input
$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    die("Error: Order ID is required.");
}

// 2. Fetch Data
try {
    $database = new Database();
    $db = $database->getConnection();

    $orderRepo = new OrderRepository($db);
    $settingsRepo = new SettingsRepository(); // It handles its own connection

    $order = $orderRepo->findById($order_id);
    if (!$order) {
        die("Error: Order not found.");
    }

    // Get store settings
    $settings = $settingsRepo->getSettings();
    $store_name = $settings['store_name'] ?? 'LEILIFE CAFE & RESTO';
    $store_address = $settings['physical_address'] ?? '123 Coffee Street, Manila';
    $store_phone = $settings['contact_phone'] ?? '0912-345-6789';

    // 3. Filter Items (Exclude Cancelled)
    $active_items = [];
    $calculated_subtotal = 0;

    foreach ($order->items as $item) {
        if ($item->status !== 'cancelled') {
            $active_items[] = $item;
            $calculated_subtotal += $item->subtotal;
        }
    }

    if (empty($active_items)) {
        die("Error: This order has no active items (all cancelled). Receipt cannot be generated.");
    }

    $delivery_fee = floatval($order->delivery_fee);
    $total_amount = $calculated_subtotal + $delivery_fee;

    // 4. POS Receipt Generation (FPDF)
    // Standard POS paper width is 80mm. 
    // We use a dynamic height or a safe long height.
    $pdf = new FPDF('P', 'mm', array(80, 250));
    $pdf->AddPage();
    $pdf->SetMargins(3, 3, 3);
    $pdf->SetAutoPageBreak(true, 5);

    // ---- HEADER ----
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(3, 5);
    $pdf->Cell(0, 5, strtoupper($store_name), 0, 1, 'C');

    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 4, $store_address, 0, 1, 'C');
    $pdf->Cell(0, 4, 'Tel: ' . $store_phone, 0, 1, 'C');
    $pdf->Ln(3);

    // ---- ORDER INFO ----
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 4, 'Date: ' . date('m/d/Y h:i A', strtotime($order->created_at)), 0, 1, 'L');
    $pdf->Cell(0, 4, 'Order #: ' . $order->order_number, 0, 1, 'L');

    // Get customer name from user id if available? 
    // OrderRepository's findById doesn't join with users. 
    // But we might have it if we update the repository. 
    // For now, let's just use "Customer" since we don't have the join in findById.
    // Wait, let me check if findById joins users. No, it doesn't.
    // I will try to get it if I can or just skip for now.

    $order_type = ($order->delivery_method ?? '') === 'delivery' ? 'Delivery' : 'Pick up';
    $pdf->Cell(0, 4, 'Type: ' . $order_type, 0, 1, 'L');
    if (!empty($order->contact_number)) {
        $pdf->Cell(0, 4, 'Phone: ' . $order->contact_number, 0, 1, 'L');
    }

    if ($order_type === 'Delivery' && !empty($order->delivery_address)) {
        $pdf->MultiCell(0, 4, 'Addr: ' . ucwords($order->delivery_address), 0, 'L');
    }
    $pdf->Ln(2);

    // ---- SEPARATOR ----
    $pdf->Cell(0, 0, str_repeat('-', 42), 0, 1, 'C');
    $pdf->Ln(2);

    // ---- ITEMS HEADER ----
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(8, 4, 'Qty', 0, 0, 'L');
    $pdf->Cell(42, 4, 'Item', 0, 0, 'L');
    $pdf->Cell(24, 4, 'Total', 0, 1, 'R');
    $pdf->Ln(1);

    // ---- ITEMS BODY ----
    $pdf->SetFont('Arial', '', 8);

    foreach ($active_items as $item) {
        $pdf->Cell(8, 4, $item->quantity, 0, 0, 'L');

        $productName = ucwords(strtolower($item->product_name));

        // Save current Y for price alignment
        $yPos = $pdf->GetY();
        $xPos = $pdf->GetX();

        // Print Product name wrapped
        $pdf->MultiCell(42, 4, $productName, 0, 'L');

        $newY = $pdf->GetY();
        $pdf->SetXY($xPos + 42, $yPos);

        $lineTotal = number_format($item->subtotal, 2);
        $pdf->Cell(24, 4, $lineTotal, 0, 1, 'R');

        if ($newY > $pdf->GetY()) {
            $pdf->SetY($newY);
        }
    }

    $pdf->Ln(2);
    $pdf->Cell(0, 0, str_repeat('-', 42), 0, 1, 'C');
    $pdf->Ln(2);

    // ---- TOTALS ----
    $pdf->SetFont('Arial', '', 9);

    $pdf->Cell(45, 5, 'Subtotal:', 0, 0, 'R');
    $pdf->Cell(29, 5, number_format($calculated_subtotal, 2), 0, 1, 'R');

    if ($order_type === 'Delivery') {
        $pdf->Cell(45, 5, 'Delivery Fee:', 0, 0, 'R');
        $pdf->Cell(29, 5, number_format($delivery_fee, 2), 0, 1, 'R');
    }

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(45, 6, 'TOTAL:', 0, 0, 'R');
    $pdf->Cell(29, 6, number_format($total_amount, 2), 0, 1, 'R');

    $is_pickup = ($order->delivery_method ?? '') !== 'delivery';
    $payment_label = ($order->payment_method === 'cod') ? ($is_pickup ? 'Cash' : 'Cash on Delivery') : 'GCash / Online';
    $payment_method = strtoupper($payment_label);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 5, 'Payment: ' . $payment_method, 0, 1, 'R');

    $pdf->Ln(5);

    // ---- FOOTER ----
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 4, $settings['receipt_footer'] ?? 'Thank you for your purchase!', 0, 1, 'C');
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->Cell(0, 4, 'System-generated receipt', 0, 1, 'C');

    $pdf->Output('I', 'pos_receipt_' . $order->order_number . '.pdf');
} catch (Exception $e) {
    die("Error generating receipt: " . $e->getMessage());
}
