<?php
require_once '../../includes/db.php';
session_start();

if (isset($_POST['table']) && isset($_POST['days'])) {
  $table = $_POST['table'];
  $days = intval($_POST['days']);
  $userId = $_SESSION['user_id']; // Assuming user ID is stored in the session

  // Calculate the new threshold date based on the days parameter
  $threshold_date = date('Y-m-d H:i:s', strtotime("-$days days"));

  // Validate table name to prevent SQL injection
  $allowed_tables = ['user_activities', 'sales_history'];
  if (in_array($table, $allowed_tables)) {
    if ($_POST['isImmediate'] == 'true') {
      $sql = "DELETE FROM $table WHERE created_at < '$threshold_date'";
      if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
          $_SESSION['messages'][] = ["type" => 'success', "message" => 'Records deleteddd successfully.'];
        } else {
          $_SESSION['messages'][] = ["type" => 'info', "message" => 'No records to delete.'];
        }
      } else {
        $_SESSION['messages'][] = ["type" => 'error', "message" => 'An error occurred.'];
      }
    } else {
      // Get the next run timestamp from the database
      $result = $conn->query("SELECT next_run FROM next_delete_all WHERE user_id = $userId");

      if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nextRun = strtotime($row['next_run']);

        if (time() >= $nextRun) {
          $sql = "DELETE FROM $table WHERE created_at < '$threshold_date'";

          if ($conn->query($sql) === TRUE) {
            $_SESSION['messages'][] = ["type" => 'success', "message" => 'Records deleted successfully.'];

            // Update the next run timestamp
            $newNextRun = date('Y-m-d H:i:s', strtotime("+$days days"));
            $stmt = $conn->prepare("UPDATE next_delete_all SET next_run = ? WHERE user_id = ?");
            $stmt->bind_param("si", $newNextRun, $userId);
            $stmt->execute();
            $stmt->close();
          } else {
            $_SESSION['messages'][] = ["type" => 'error', "message" => 'An error occurred.'];
          }
        } else {
          $_SESSION['messages'][] = ["type" => 'info', "message" => 'Not yet time to delete records.'];
        }
      } else {
        $_SESSION['messages'][] = ["type" => 'error', "message" => 'Missing next run timestamp.'];
      }
    }
  } else {
    $_SESSION['messages'][] = ["type" => 'error', "message" => 'Invalid table name.'];
  }
} else {
  $_SESSION['messages'][] = ["type" => 'error', "message" => 'Missing table or days parameter.'];
}

$conn->close();
