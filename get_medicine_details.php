<?php
require_once 'includes/db.php';

if (!isset($_GET['id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
  exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT id, name, price FROM medicines WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo json_encode(['status' => 'error', 'message' => 'Medicine not found']);
  exit();
}

$medicine = $result->fetch_assoc();
echo json_encode(['status' => 'success', 'id' => $medicine['id'], 'name' => $medicine['name'], 'price' => $medicine['price']]);

$stmt->close();
$conn->close();
