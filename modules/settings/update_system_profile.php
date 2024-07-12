<?php 
session_start();
require_once '../../includes/db.php'; 
require_once '../utilities/log_user_activity.php';

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["status" => "error", "message" => "Unauthorized"]);
  exit();
}

// update system profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $system_name = $_POST['name'];
  $system_profile_picture = $_FILES['image'];

  $stmt = $conn->prepare("UPDATE system_profile SET name=?, image=? WHERE id=1");
  $stmt->bind_param("ss", $system_name, $system_profile_picture['name']);

  if ($stmt->execute()) {
    move_uploaded_file($system_profile_picture['tmp_name'], "../../uploads/" . $system_profile_picture['name']);
    
    // log the activity
    logUserActivity("پڕۆفایلی سیستەمەکەی کرد بە $system_name.");

    $_SESSION['messages'][] = ["type" => "success", "message" => "پڕۆفایلی سیستەمەکە بە سەرکەوتوویی نوێکرایەوە"];
  } else {
    $_SESSION['messages'][] = ["type" => "error", "message" => "هەڵەیەک ڕوویدا"];
  }

  $stmt->close();
} else {
  $_SESSION['messages'][] = ["type" => "error", "message" => "تکایە دووبارە هەوڵ بدەوە"];
}

$conn->close();