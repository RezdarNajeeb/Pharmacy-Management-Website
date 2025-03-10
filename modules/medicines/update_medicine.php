<?php
session_start();
require_once '../../includes/db.php';
require_once '../utilities/log_user_activity.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../../pages/login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  try {
    $currency = $_POST['currency'];
    $exchange_rate = $_POST['exchange_rate'];
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $quantity = $_POST['quantity'];
    $expiry_date = $_POST['expiry_date'];
    $barcode = $_POST['barcode'];
    $existing_image = $_POST['existing_image'];

    if ($barcode == '') {
      $barcode = null;
    }

    if ($expiry_date == '' || $expiry_date == '0000-00-00') {
      $expiry_date = null;
    }

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
      $image = $_FILES['image']['name'];
      $target_dir = "../../uploads/";
      $target_file = $target_dir . basename($image);
      if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        // Delete the previous image file
        if (!empty($existing_image) && file_exists($target_dir . $existing_image)) {
          unlink($target_dir . $existing_image);
        }
      } else {
        $_SESSION['messages'][] = ["type" => 'error', "message" => 'هەڵەیەک ڕوویدا لەکاتی بارکردنی وێنە.'];
        $image = $existing_image; // Keep the existing image if upload fails
      }
    } else {
      $image = $existing_image;
    }

    $stmt = $conn->prepare("UPDATE medicines SET name=?, category=?, cost_price=?, selling_price=?, currency=?, quantity=?, expiry_date=?, barcode=?, image=? WHERE id=?");
    $stmt->bind_param("ssddsisssi", $name, $category, $cost_price, $selling_price, $currency, $quantity, $expiry_date, $barcode, $image, $id);

    if ($stmt->execute()) {
      // Log the activity
      logUserActivity("بەرهەمێکی نوێکردەوە بە ناوی $name.");

      $_SESSION['messages'][] = ["type" => 'success', "message" => 'بەرهەمەکە بەسەرکەوتوویی نوێ کرایەوە.'];
    } else {
      $_SESSION['messages'][] = ["type" => 'error', "message" => 'هەڵەیەک ڕویدا لە نوێکردنەوەی بەرهەم.'];
    }

    $stmt->close();
  } catch (mysqli_sql_exception $e) {
    $_SESSION['messages'][] = [
      'type' => 'error',
      'message' => "هەڵەیەک ڕویدا لە نوێکردنەوەی بەرهەم: " . $e->getMessage()
    ];
  }
} else {
  $_SESSION['messages'][] = ["type" => 'error', "message" => 'تکایە دوبارە هەوڵ بدەوە.'];
}

$conn->close();
