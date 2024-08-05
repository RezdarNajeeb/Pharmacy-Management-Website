<?php
session_start();
require_once '../../includes/db.php';
require_once '../utilities/log_user_activity.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../pages/login.php");
  exit();
}

// Handle form submission for adding a medicine
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_medicine'])) {
  $currency = $_POST['currency'];
  $exchange_rate = floatval($_POST['exchange_rate']);
  $name = $_POST['name'];
  $category = $_POST['category'];
  $cost_price = $_POST['cost_price'];
  $selling_price = $_POST['selling_price'];
  $quantity = $_POST['quantity'];
  $expiry_date = $_POST['expiry_date'];
  $barcode = $_POST['barcode'];

  if ($barcode == '') {
    $barcode = null;
  }

  if ($expiry_date == '' || $expiry_date == '0000-00-00') {
    $expiry_date = null;
  }

  // Handle image upload
  $image = null;
  if (!empty($_FILES['image']['name'])) {
    $image = $_FILES['image']['name'];
    $target_dir = "../../uploads/";
    $target_file = $target_dir . basename($image);

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      $_SESSION['messages'][] = [
        'type' => 'error',
        'message' => 'هەڵەیەک ڕویدا لە بارکردنی وێنە.'
      ];

      // Redirect to the same page to show the error message
      header("location: ../../pages/medicines.php");
      exit();
    }
  }

  try {
    $stmt = $conn->prepare("INSERT INTO medicines (name, category, cost_price, selling_price, currency, quantity, expiry_date, barcode, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddsisss", $name, $category, $cost_price, $selling_price, $currency, $quantity, $expiry_date, $barcode, $image);

    if ($stmt->execute() === TRUE) {
      // Log the user activity
      logUserActivity("بەرهەمێکی زیادکرد بە ناوی $name.");

      $_SESSION['messages'][] = [
        'type' => 'success',
        'message' => 'بەرهەمێکی نوێ زیادکرا.'
      ];
    }
    $stmt->close();
  } catch (mysqli_sql_exception $e) {
    $_SESSION['messages'][] = [
      'type' => 'error',
      'message' => "هەڵەیەک ڕوویدا لە کاتی زیادکردنی بەرهەم: " . $e->getMessage()
    ];
  }

  header("Location: ../../pages/medicines.php");
}
