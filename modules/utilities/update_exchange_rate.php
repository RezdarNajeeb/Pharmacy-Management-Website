<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../../../pages/login.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_rate = floatval($_POST['new_rate']);

  // Update the exchange rate in the database
  $query = "UPDATE exchange_rates SET rate = ?, updated_at = NOW() WHERE currency = 'USD'";
  $stmt = $conn->prepare($query);
  $stmt->bind_param('d', $new_rate);

  if ($stmt->execute()) {
    // Log the activity
    $activity = "Updated USD to IQD exchange rate to $new_rate";
    $stmt_log = $conn->prepare("INSERT INTO user_activities (user_id, activity) VALUES (?, ?)");
    $stmt_log->bind_param("is", $_SESSION['user_id'], $activity);
    $stmt_log->execute();
    $stmt_log->close();

    echo json_encode(['status' => 'success', 'message' => 'Exchange rate updated successfully']);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update exchange rate']);
  }

  $stmt->close();
  $conn->close();
}
