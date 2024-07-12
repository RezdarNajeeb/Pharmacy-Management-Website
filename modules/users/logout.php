<?php
session_start();
require_once '../../includes/db.php';
require_once '../utilities/log_user_activity.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../../../pages/login.php");
  exit();
}

// Before logout, delete the remember me token
if (isset($_COOKIE['remember_me'])) {
  $id = $_SESSION['user_id'];
  $token = $_COOKIE['remember_me'];

  // Prepare to clear the remember_token for the logged-in user
  $stmt = $conn->prepare("UPDATE users SET remember_token = '' WHERE id = ?;");
  $stmt->bind_param("i", $id);
  $stmt->execute();

  $stmt->close();
  $conn->close();

  // Securely remove the remember_me cookie
  setcookie('remember_me', '', time() - 3600, "/", "", true, true); // Expire the cookie
}

// Clear all session data
session_unset();
session_destroy();

//log the activity
logUserActivity("چووە دەرەوە لە سیستەمەکە.");

// Redirect to the login page
header("Location: ../../../../pages/login.php");
exit();
