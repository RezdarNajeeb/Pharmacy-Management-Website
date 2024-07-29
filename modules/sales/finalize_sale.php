<?php
session_start();
require_once '../../includes/db.php';
require_once '../utilities/log_user_activity.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
  $_SESSION['messages'][] = ['type' => 'error', 'message' => 'هیچ زانیارییەک نەدۆزرایەوە.'];
  exit();
}

$sales = $data['sales'];
$discount = floatval($data['discount']);
$discounted_total = floatval($data['discountedTotalIQD']);

// Prepare statements once
$insert_stmt = $conn->prepare("INSERT INTO sales_history (medicine_id, quantity, cost_price, selling_price, total, discount, discounted_total, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$insert_stmt->bind_param('iidddddi', $medicine_id, $quantity, $cost_price, $selling_price, $total, $discount, $discounted_total, $_SESSION['user_id']);

$select_medicine_stmt = $conn->prepare("SELECT quantity, expiry_date FROM medicines WHERE id = ?");
$select_medicine_stmt->bind_param('i', $medicine_id);

$select_warning_stmt = $conn->prepare("SELECT warning_quantity, warning_expiry_days FROM warning_settings WHERE id = 1");

$update_medicine_stmt = $conn->prepare("UPDATE medicines SET quantity = quantity - ? WHERE id = ?");
$update_medicine_stmt->bind_param('ii', $quantity, $medicine_id);

$conn->begin_transaction();

try {
  foreach ($sales as $sale) {
    $medicine_id = intval($sale['id']);
    $quantity = intval($sale['quantity']);
    $cost_price = floatval($sale['costPrice']);
    $selling_price = floatval($sale['sellingPrice']);
    $total = floatval($sale['totalIQD']);

    if (!$insert_stmt->execute()) {
      throw new Exception('کێشەیەک ڕوویدا: ' . $insert_stmt->error);
    }

    $select_medicine_stmt->execute();
    $medicine_result = $select_medicine_stmt->get_result()->fetch_assoc();
    $medicine_DB_qty = $medicine_result['quantity'];
    $medicine_DB_expiry_date = $medicine_result['expiry_date'];

    $select_warning_stmt->execute();
    $warning_result = $select_warning_stmt->get_result()->fetch_assoc();
    $warning_qty = $warning_result['warning_quantity'];
    $warning_expiry_days = $warning_result['warning_expiry_days'];
    $warning_expiry_date = date('Y-m-d', strtotime('+' . $warning_expiry_days . ' days')); // Add warning expiry days to current date

    if (strtotime($medicine_DB_expiry_date) <= strtotime($warning_expiry_date)) {
      throw new Exception('فرۆشتنەکە سەرکەوتوو نەبوو چونکە بەسەرچوونی ئەم دەرمانە [ ' . $sale['name'] . ' ] گەشتووەتە بڕی ئاگادارکردنەوە.');
    }

    if ($quantity > $medicine_DB_qty) {
      throw new Exception('فرۆشتنەکە سەرکەوتوو نەبوو چونکە بڕی پێویست لەم دەرمانە [ ' . $sale['name'] . ' ] بەردەست نییە.');
    }

    if ($medicine_DB_qty <= $warning_qty) {
      $_SESSION['messages'][] = ['type' => 'info', 'message' => 'ئاگاداربە بڕی ئەم دەرمانە ' . $sale['name'] . 'گەشتووەتە بڕی ئاگادارکردنەوە.'];
    }

    if (!$update_medicine_stmt->execute()) {
      throw new Exception('کێشەیەک ڕوویدا: ' . $update_medicine_stmt->error);
    }
  }

  $conn->commit();
  $_SESSION['messages'][] = ['type' => 'success', 'message' => 'فرۆشتنەکە بەسەرکەوتویی تۆمارکرا.'];
  logUserActivity('فرۆشتنێکی ئەنجامدا');
} catch (Exception $e) {
  $conn->rollback();
  $_SESSION['messages'][] = ['type' => 'error', 'message' => $e->getMessage()];
}

$insert_stmt->close();
$select_medicine_stmt->close();
$select_warning_stmt->close();
$update_medicine_stmt->close();
$conn->close();
