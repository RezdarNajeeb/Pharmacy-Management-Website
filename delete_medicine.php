<?php
require_once 'includes/db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  echo "Unauthorized";
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = $_POST['id'];

  $stmt = $conn->prepare("DELETE FROM medicines WHERE id=?");
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
    echo "دەرمانەکە بە سەرکەوتویی سڕایەوە.";
    exit();
  } else {
    echo "Error: " . $stmt->error;
  }

  $stmt->close();
} else {
  echo "Invalid request method.";
}

$conn->close();
