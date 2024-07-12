<?php
session_start();
require_once '../../includes/db.php';
require_once 'log_user_activity.php';

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
    logUserActivity("بەهای دیناری بۆ 1 دۆلار کرد بە $new_rate دینار.");

    $_SESSION['messages'][] = ['type' => 'success', 'message' => 'بەهای دینار بۆ 1 دۆلار بەسەرکەوتووی نوێکرایەوە.'];

    header('Location: ' . $_SERVER['HTTP_REFERER']); // Redirect back to the previous page
  } else {
    $_SESSION['messages'][] = ['type' => 'error', 'message' => 'هەڵەیەک ڕوویدا.'];
  }

  $stmt->close();
  $conn->close();
}
