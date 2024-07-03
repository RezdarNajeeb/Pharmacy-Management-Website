<?php
require_once '../../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["status" => "error", "message" => "Unauthorized"]);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $warning_quantity = $_POST['warning_quantity'];
  $warning_expiry_days = $_POST['warning_expiry_days'];

  $stmt = $conn->prepare("UPDATE settings SET warning_quantity=?, warning_expiry_days=? WHERE id=1");
  $stmt->bind_param("ii", $warning_quantity, $warning_expiry_days);

  if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Settings updated successfully."]);
  } else {
    echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
  }

  $stmt->close();
} else {
  echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

$conn->close();
