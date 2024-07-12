<?php
session_start();
require_once '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
  $medicineId = $_POST['id'];

  $stmt = $conn->prepare("SELECT * FROM medicines WHERE id = ?");
  $stmt->bind_param("i", $medicineId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $medicine = $result->fetch_assoc();
    echo json_encode(['success' => true, 'medicine' => $medicine]);
  } else {
    $_SESSION['messages'][] = ['error', 'دەرمانەکە نەدۆزرایەوە.'];
  }
  $stmt->close();
} else {
  $_SESSION['messages'][] = ['error', 'دەرمانەکە نەدۆزرایەوە.'];
}

$conn->close();
