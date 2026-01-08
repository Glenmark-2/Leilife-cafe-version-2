<?php
require_once __DIR__ . '/../backend/helpers/SessionManager.php';
require_once __DIR__ . '/../backend/config/Database.php';
require_once __DIR__ . '/../backend/repositories/OrderRepository.php';
require_once __DIR__ . '/../backend/repositories/UserRepository.php';
require_once __DIR__ . '/../backend/repositories/SettingsRepository.php';

// FPDF library path
require_once __DIR__ . '/../backend/fpdf186/fpdf.php';

SessionManager::startSession();

if (!SessionManager::isLoggedIn()) {
    die("Access denied. Please login.");
}

$userId = SessionManager::get('user_id');
$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
    die("Order ID is required.");
}

// 1. Fetch Data
$db = (new Database())->getConnection();
$orderRepo = new OrderRepository($db);
$userRepo = new UserRepository($db);
$settingsRepo = new SettingsRepository($db);

$order = $orderRepo->findById($orderId);
if (!$order || $order->user_id != $userId) {
    die("Order not found or access denied.");
}

if (!in_array($order->status, ['delivered', 'picked_up', 'completed'])) {
    die("Receipt is only available for delivered or picked up orders.");
}

$orderItems = $orderRepo->getOrderItems($orderId);
$settings = $settingsRepo->getSettings();
$customer = $userRepo->findById($userId);

// Extract store info
$store_name = $settings['store_name'] ?? 'LEILIFE CAFE & RESTO';
$store_address = $settings['store_address'] ?? '123 Coffee Street, Manila';
$store_phone = $settings['store_phone'] ?? '0912-345-6789';

// --- FPDF GENERATION ---

// Create a standard A4 PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// ---- HEADER ----
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, strtoupper($store_name), 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(77, 81, 86);
$pdf->Cell(0, 5, $store_address, 0, 1, 'C');
$pdf->Cell(0, 5, 'Tel: ' . $store_phone, 0, 1, 'C');
$pdf->Ln(10);

// ---- ORDER INFO SETUP ----
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);

$order_type = ($order->delivery_method ?? '') === 'delivery' ? 'Delivery' : 'Pick up';
$formatted_date = date('F d, Y h:i A', strtotime($order->created_at));

$customer_full_name = $customer ? ($customer->first_name . ' ' . $customer->last_name) : 'Valued Customer';

$orderDetails = [
    ['Date' => $formatted_date],
    ['Order Number' => $order->order_number],
    ['Type' => $order_type],
    ['Payment Method' => ($order->payment_method === 'cod' ? 'Cash' : 'GCash / Online')],
    ['Customer' => ucwords(strtolower($customer_full_name))],
];

if (!empty($order->contact_number)) {
    $orderDetails[] = ['Phone' => $order->contact_number];
}

if ($order_type === 'Delivery' && !empty($order->delivery_address)) {
    $orderDetails[] = ['Address' => ucwords($order->delivery_address)];
}

// ---- PRINT ORDER INFO ----
foreach ($orderDetails as $detail) {
    foreach ($detail as $label => $value) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 6, $label . ':', 0, 0);
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
$pdf->Cell(40, 8, 'TOTAL', 'B', 1, 'R');

// ---- PRINT ORDER ITEMS ----
$pdf->SetFont('Arial', '', 10);
foreach ($orderItems as $item) {
    if ($item->status === 'cancelled') continue;

    $productName = ucwords(strtolower($item->product_name));
    $itemTotal = number_format($item->price * $item->quantity, 2);

    $pdf->Cell(20, 6, $item->quantity, 0, 0, 'C');
    $pdf->Cell(110, 6, $productName, 0, 0, 'L');
    $pdf->Cell(40, 6, $itemTotal, 0, 1, 'R');
}

$pdf->Ln(5);

// ---- TOTALS ----
$subtotal = number_format($order->total_amount, 2, '.', '');
$deliveryFee = number_format($order->delivery_fee ?? 0, 2, '.', '');
$total = number_format(($order->total_amount + ($order->delivery_fee ?? 0)), 2, '.', '');

$pdf->SetLineWidth(0.5);
$pdf->Line(130, $pdf->GetY(), 200, $pdf->GetY());
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

$pdf->Output('I', 'receipt_' . $order->order_number . '.pdf');
exit;
