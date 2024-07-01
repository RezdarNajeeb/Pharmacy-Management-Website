<?php
require_once 'includes/db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = $_POST['id'];
  $name = $_POST['name'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $quantity = $_POST['quantity'];
  $expiry_date = $_POST['expiry_date'];
  $barcode = $_POST['barcode'];
  $existing_image = $_POST['existing_image'];

  // Handle image upload
  if (!empty($_FILES['image']['name'])) {
    $image = $_FILES['image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      echo "Error uploading image.";
      exit();
    }
  } else {
    $image = $existing_image;
  }

  $stmt = $conn->prepare("UPDATE medicines SET name=?, category=?, price=?, quantity=?, expiry_date=?, barcode=?, image=? WHERE id=?");
  $stmt->bind_param("ssdisssi", $name, $category, $price, $quantity, $expiry_date, $barcode, $image, $id);

  if ($stmt->execute()) {
    // Log the activity
    $activity = "Updated medicine with name $name";
    $stmt_log = $conn->prepare("INSERT INTO user_activities (user_id, activity) VALUES (?, ?)");
    $stmt_log->bind_param("is", $_SESSION['user_id'], $activity);
    $stmt_log->execute();
    $stmt_log->close();

    header("Location: pages/medicines.php");
  } else {
    echo "Error: " . $stmt->error;
  }

  $stmt->close();
} else {
  echo "داواکارییەکەت هەڵەیە.";
  header("Location: pages/medicines.php");
}

$conn->close();
