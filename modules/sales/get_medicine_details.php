<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_GET['barcode'])) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
  exit();
}

$barcode = $_GET['barcode'];
$medicine_name = $_GET['medicine_name'];

$stmt = $conn->prepare("SELECT id, name, image, cost_price, selling_price FROM medicines WHERE barcode = ? OR name = ?");
$stmt->bind_param('ss', $barcode, $medicine_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  $_SESSION['messages'][] = ['type' => 'error', 'message' => 'هیچ دەرمانێک بەم بارکۆدە نەدۆزرایەوە.'];
  exit();
}

$medicine = $result->fetch_assoc();
echo json_encode(['status' => 'success', 'medicine' => $medicine]);

$stmt->close();
$conn->close();
