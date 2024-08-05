<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_GET['sale_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
  exit();
}

$sale_id = intval($_GET['sale_id']);

$stmt = $conn->prepare("SELECT * FROM sales_history WHERE id = ?");
$stmt->bind_param('i', $sale_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo json_encode(['status' => 'error', 'message' => 'هیچ فرۆشتنێک نەدۆزرایەوە.']);
  exit();
}

$data = $result->fetch_assoc();
echo json_encode(['status' => 'success', 'data' => $data]);

$stmt->close();
$conn->close();
