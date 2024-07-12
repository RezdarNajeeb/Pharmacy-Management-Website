<?php
session_start();
require_once '../../includes/db.php';
require_once '../utilities/log_user_activity.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit();
}

$data = json_decode(file_get_contents('php://input'), true); // Get the JSON data sent to the server

if (empty($data)) {
  $_SESSION['messages'][] = ['type' => 'error', 'message' => 'هیچ زانیارییەک نەدۆزرایەوە.'];
  exit();
}

$sales = $data['sales'];
$discount = floatval($data['discount']);
$discounted_total = floatval($data['discountedTotalIQD']);

foreach ($sales as $sale) {
  $medicine_id = intval($sale['id']);
  $medicine_image = $sale['image'];
  $medicine_name = $sale['name'];
  $quantity = intval($sale['quantity']);
  $cost_price = floatval($sale['costPrice']);
  $selling_price = floatval($sale['sellingPrice']);
  $total = floatval($sale['totalIQD']);

  $stmt = $conn->prepare("INSERT INTO sales_history (medicine_id, quantity, cost_price, selling_price, total, discount, discounted_total, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param('iidddddi', $medicine_id, $quantity, $cost_price, $selling_price, $total, $discount, $discounted_total, $_SESSION['user_id']);

  if (!$stmt->execute()) {
    $_SESSION['messages'][] = ['type' => 'error', 'message' => 'کێشەیەک ڕوویدا: ' . $stmt->error];
    exit();
  }

  // Update the quantity in the medicines table
  $stmt = $conn->prepare("UPDATE medicines SET quantity = quantity - ? WHERE id = ?");
  $stmt->bind_param('ii', $quantity, $medicine_id);

  if (!$stmt->execute()) {
    $_SESSION['messages'][] = ['type' => 'error', 'message' => 'کێشەیەک ڕوویدا: ' . $stmt->error];
    exit();
  }
}

$_SESSION['messages'][] = ['type' => 'success', 'message' => 'فرۆشتنەکە بەسەرکەوتویی تۆمارکرا.'];

$stmt->close();
$conn->close();
