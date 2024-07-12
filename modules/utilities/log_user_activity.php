<?php
function logUserActivity($activity)
{
  global $conn;

  $stmt = $conn->prepare("INSERT INTO user_activities (user_id, activity) VALUES (?, ?)");
  if ($stmt === false) {
    // Handle error in preparing statement
    error_log('MySQL prepare error: ' . $conn->error);
    return false;
  }

  $stmt->bind_param("is", $_SESSION['user_id'], $activity);
  if ($stmt->execute() === false) {
    // Handle error in executing statement
    error_log('MySQL execute error: ' . $stmt->error);
    return false;
  }

  $stmt->close();
  return true;
}
