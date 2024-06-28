<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = $_POST['id'];

  // First, fetch the image filename from the database
  $stmt = $conn->prepare("SELECT image FROM medicines WHERE id=?");
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
    if ($imageFilename && file_exists("uploads/" . $imageFilename)) {
      unlink("uploads/" . $imageFilename);
    }
    echo "دەرمان بەسەرکەوتوی سڕایەوە.";
    header("Location: pages/medicines.php");
  } else {
    echo "هەڵەیەک ڕوویدا: " . $stmt->error;
    header("Location: pages/medicines.php");
  }

  $stmt->close();
} else {
  echo "داواکارییەکەت هەڵەیە.";
  header("Location: pages/medicines.php");
}

$conn->close();
