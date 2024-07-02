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

$sales = $data['sales'];
$discount = floatval($data['discount']);
$discounted_total = floatval($data['discountedTotalIQD']);

foreach ($sales as $sale) {
  $medicine_id = intval($sale['id']);
  $medicine_name = $sale['name'];
  $quantity = intval($sale['quantity']);
  $cost_price = floatval($sale['costPrice']);
  $selling_price = floatval($sale['sellingPrice']);
  $total = floatval($sale['totalIQD']);

  $stmt = $conn->prepare("INSERT INTO sales_history (medicine_id, quantity, cost_price, selling_price, total, discount, discounted_total, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param('iidddddi', $medicine_id, $quantity, $cost_price, $selling_price, $total, $discount, $discounted_total, $_SESSION['user_id']);

  if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
    exit();
  }

  // Log the activity
  $activity = "Added sale for medicine with name $medicine_name and quantity $quantity";
  $stmt_log = $conn->prepare("INSERT INTO user_activities (user_id, activity) VALUES (?, ?)");
  $stmt_log->bind_param("is", $_SESSION['user_id'], $activity);
  $stmt_log->execute();
  $stmt_log->close();

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
