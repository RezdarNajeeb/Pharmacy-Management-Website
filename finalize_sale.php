<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit();
}

$data = json_decode(file_get_contents('php://input'), true); // Get the JSON data sent to the server

if (empty($data)) {
  echo json_encode(['status' => 'error', 'message' => 'No data provided']);
  exit();
}

foreach ($data as $sale) {
  $medicine_id = intval($sale['id']);
  $quantity = intval($sale['quantity']);
  $price = floatval($sale['price']);
  $total = floatval($sale['total']);

  $stmt = $conn->prepare("INSERT INTO sales_history (medicine_id, quantity, price, total, user_id) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param('iiddi', $medicine_id, $quantity, $price, $total, $_SESSION['user_id']);

  if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    exit();
  }

  // Update the quantity in the medicines table
  $stmt = $conn->prepare("UPDATE medicines SET quantity = quantity - ? WHERE id = ?");
  $stmt->bind_param('ii', $quantity, $medicine_id);

  if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    exit();
  }
}

echo json_encode(['status' => 'success', 'message' => 'Sale finalized successfully']);

$stmt->close();
$conn->close();
