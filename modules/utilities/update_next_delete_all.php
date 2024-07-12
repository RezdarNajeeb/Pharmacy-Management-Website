<?php
session_start();
require_once '../../includes/db.php';
require_once 'log_user_activity.php';

if (isset($_POST['nextRun']) && isset($_POST['tableName'])) {
  // Format the nextRun to accept by the database field which its type is timestamp
  $nextRun = date('Y-m-d H:i:s', strtotime($_POST['nextRun']));
  $userId = $_SESSION['user_id']; // Assuming user ID is stored in the session
  $tableName = $_POST['tableName']; // Table name parameter

  // Prepare statement to insert or update next_run based on user_id and table_name
  $stmt = $conn->prepare("INSERT INTO next_delete_all (user_id, table_name, next_run) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE next_run = ?");
  $stmt->bind_param("isss", $userId, $tableName, $nextRun, $nextRun);

  if ($stmt->execute()) {
    // Log the activity
    logUserActivity("کاتی سڕینەوەی داهاتووی کرد بە $nextRun بۆ خشتەی $tableName.");

    $_SESSION['messages'][] = ['type' => 'success', 'message' => 'کاتی سڕینەوەی داهاتوو بەسەرکەوتوویی نوێکرایەوە'];
  } else {
    $_SESSION['messages'][] = ['type' => 'error', 'message' => 'هەڵەیەک ڕوویدا.'];
  }

  $stmt->close();
}

$conn->close();
