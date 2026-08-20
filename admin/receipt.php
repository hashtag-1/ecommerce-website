<?php
// Seed2Greens - Serve an order's payment receipt as a separate response.
// The Order Details page only references this URL (receipt.php?id=ORDER),
// keeping the main Order Details HTML response lightweight so it never
// exceeds Vercel Serverless Function payload limits.
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/guard.php';

if (!isset($_GET['id'])) {
    http_response_code(404);
    exit;
}

$order_id = (int)$_GET['id'];
$receipt = getOrderReceipt($order_id);

if (!$receipt || empty($receipt['receipt_data'])) {
    http_response_code(404);
    exit;
}

$data = base64_decode($receipt['receipt_data'], true);
if ($data === false || $data === '') {
    http_response_code(404);
    exit;
}

$mime = !empty($receipt['receipt_mime']) ? $receipt['receipt_mime'] : 'application/octet-stream';
$size = strlen($data);

header('Content-Type: ' . $mime);
header('Content-Length: ' . $size);
header('Content-Disposition: inline; filename="receipt-' . $order_id . '"');
header('Cache-Control: private, max-age=3600');
echo $data;
exit;

