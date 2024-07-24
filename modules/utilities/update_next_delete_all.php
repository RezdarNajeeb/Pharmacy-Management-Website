<?php
require_once '../../includes/db.php';
session_start();

if (isset($_POST['nextRun'])) {
  // format the nextRun to accept by the database field which its type timestamp
  $nextRun = date('Y-m-d H:i:s', strtotime($_POST['nextRun']));
  $userId = $_SESSION['user_id']; // Assuming user ID is stored in the session

  $stmt = $conn->prepare("INSERT INTO next_delete_all (user_id, next_run) VALUES (?, ?) ON DUPLICATE KEY UPDATE next_run = ?");
  $stmt->bind_param("iss", $userId, $nextRun, $nextRun);

  if ($stmt->execute()) {
    echo "Success: $nextRun";
  } else {
    echo "Error: " . $stmt->error;
  }

  $stmt->close();
}

$conn->close();
