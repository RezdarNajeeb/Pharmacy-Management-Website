<?php
require_once 'includes/db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  echo json_encode(["status" => "error", "message" => "Unauthorized"]);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = $_POST['id'];
  $name = $_POST['name'];
  $category = $_POST['category'];
  $price = $_POST['price'];
  $quantity = $_POST['quantity'];
  $expiry_date = $_POST['expiry_date'];
  $existing_image = $_POST['existing_image'];

  // Handle image upload
  if (!empty($_FILES['image']['name'])) {
    $image = $_FILES['image']['name'];
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($image);
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      echo json_encode(["status" => "error", "message" => "Error uploading image."]);
      exit();
    }
  } else {
    $image = $existing_image;
  }

  $stmt = $conn->prepare("UPDATE medicines SET name=?, category=?, price=?, quantity=?, expiry_date=?, image=? WHERE id=?");
  $stmt->bind_param("ssdiisi", $name, $category, $price, $quantity, $expiry_date, $image, $id);

  if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Medicine updated successfully."]);
  } else {
    echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
  }

  $stmt->close();
} else {
  echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

$conn->close();
