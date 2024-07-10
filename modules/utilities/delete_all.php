<?php
session_start();
require_once '../../includes/db.php';

if (isset($_POST['table']) && isset($_POST['days'])) {
  $table = $_POST['table'];
  $days = intval($_POST['days']);
  $userId = $_SESSION['user_id'];

  $threshold_date = date('Y-m-d H:i:s', strtotime("-$days days"));

  $allowed_tables = ['user_activities', 'sales_history'];
  if (in_array($table, $allowed_tables)) {
    if ($_POST['isImmediate'] == 'true') {
      $sql = "DELETE FROM $table WHERE created_at <= ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("s", $threshold_date);
      if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
          $_SESSION['messages'][] = ["type" => 'success', "message" => 'زانیارییەکان بەسەرکەوتووی سڕانەوە'];
        } else {
          $_SESSION['messages'][] = ["type" => 'info', "message" => "هیچ زانیارییەک ماوەکەی لە $days ڕۆژ زیاتر نییە تا بتوانیت بیسڕیتەوە."];
        }
      } else {
        $_SESSION['messages'][] = ["type" => 'error', "message" => 'کێشەیەک ڕویدا.'];
      }
      $stmt->close();
    } else {
      $result = $conn->query("SELECT next_run FROM next_delete_all WHERE user_id = $userId");

      if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nextRun = strtotime($row['next_run']);

        if (time() >= $nextRun) {
          $sql = "DELETE FROM $table WHERE created_at <= ?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("s", $threshold_date);
          if ($stmt->execute()) {
            $_SESSION['messages'][] = ["type" => 'success', "message" => 'زانیارییەکان بەسەرکەوتووی سڕانەوە'];

            $newNextRun = date('Y-m-d H:i:s', strtotime("+$days days"));
            $stmt = $conn->prepare("UPDATE next_delete_all SET next_run = ? WHERE user_id = ?");
            $stmt->bind_param("si", $newNextRun, $userId);
            $stmt->execute();
            $stmt->close();
          } else {
            $_SESSION['messages'][] = ["type" => 'error', "message" => 'کێشەیەک ڕویدا.'];
          }
        } else {
          $_SESSION['messages'][] = ["type" => 'info', "message" => 'هێشتا کاتی سڕینەوەی زانیارییەکان نەهاتووە.'];
        }
      } else {
        $_SESSION['messages'][] = ["type" => 'error', "message" => 'کێشەیەک ڕویدا: نازانرێت کاتی سڕینەوەی زانیارییەکان کەیە.'];
      }
    }
  } else {
    $_SESSION['messages'][] = ["type" => 'error', "message" => 'ناتوانیت زانیارییەکانی ئەم خشتە بسڕیتەوە.'];
  }
} else {
  $_SESSION['messages'][] = ["type" => 'error', "message" => 'ناتوانیت زانیارییەکانی ئەم خشتە بسڕیتەوە.'];
}

$conn->close();
