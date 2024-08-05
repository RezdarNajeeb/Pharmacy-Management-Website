<?php
session_start();
require_once '../../includes/db.php';
require_once '../utilities/log_user_activity.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../pages/login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = $_POST['id'];

  // First, fetch the image filename from the database
  $stmt = $conn->prepare("SELECT name, image FROM medicines WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $medicine = $result->fetch_assoc();
  $imageFilename = $medicine['image'];
  $stmt->close();

  // Then, delete the medicine record
  $stmt = $conn->prepare("DELETE FROM medicines WHERE id=?");
  $stmt->bind_param("i", $id);
  if ($stmt->execute()) {
    // If the medicine is successfully deleted, delete the image file
    if ($imageFilename && file_exists("../../uploads" . $imageFilename)) {
      unlink("../../uploads/" . $imageFilename);
    }

    // Log the activity
    logUserActivity("بەرهەمێکی سڕییەوە بە ناوی " . $medicine['name'] . ".");

    $_SESSION['messages'][] = ["type" => 'success', "message" => 'بەرهەمەکە بەسەرکەوتوویی سڕایەوە.'];
  } else {
    $_SESSION['messages'][] = ["type" => 'error', "message" => 'کێشەیەک ڕوویدا.'];
  }

  $stmt->close();
} else {
  $_SESSION['messages'][] = ["type" => 'error', "message" => 'تکایە دووبارە هەوڵ بدەوە.'];
}

$conn->close();
