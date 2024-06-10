<?php
require_once 'includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// before logout user delete remember me token
if (isset($_COOKIE['remember_me'])) {
  $id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
  $token = $_COOKIE['remember_me'];
  $stmt = $conn->prepare("UPDATE users SET remember_token = '' WHERE remember_token = ? AND id = ?;");
  $stmt->bind_param("si", $token, $id);
  $stmt->execute();

  $stmt->close();
  $conn->close();

  setcookie('remember_me', '', time() - (86400 * 30), "/", "", true, true);
}

session_unset();
session_destroy();
header("Location: login.php");
exit();
