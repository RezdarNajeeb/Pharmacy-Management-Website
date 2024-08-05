<?php
session_start();
require_once '../../includes/db.php';
require_once '../utilities/log_user_activity.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../pages/login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $new_username = $conn->real_escape_string($_POST['new_username']);
  $current_password = $conn->real_escape_string($_POST['current_password']);
  $new_password = password_hash($conn->real_escape_string($_POST['new_password']), PASSWORD_BCRYPT);
  $user_id = $_SESSION['user_id'];

  $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $stmt->close();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (!password_verify($current_password, $row['password'])) {
      $_SESSION['messages'][] = ['type' => 'error', 'message' => 'وشەی نهێنی ئێستا هەڵەیە.'];
      exit();
    }
  } else {
    $_SESSION['messages'][] = ['type' => 'error', 'message' => 'هەڵەیەک ڕوویدا.'];
    exit();
  }


  $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
  $stmt->bind_param("ssi", $new_username, $new_password, $user_id);

  if ($stmt->execute()) {
    $_SESSION['username'] = $new_username;

    // Log user activity
    logUserActivity("زانیارییەکانی خۆی نوێکردەوە.");

    $_SESSION['messages'][] = ['type' => 'success', 'message' => 'زانیارییەکانت بەسەرکەوتوویی نوێکرانەوە.'];
    header('Location: ' . $_SERVER['HTTP_REFERER']); // Redirect back to the previous page
    exit();
  } else {
    $_SESSION['messages'][] = ['type' => 'error', 'message' => 'هەڵەیەک ڕوویدا.'];
  }

  $stmt->close();
  $conn->close();
}
