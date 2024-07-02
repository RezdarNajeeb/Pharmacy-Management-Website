<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../login.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
  <?php require_once '../includes/header.php'; ?>

  <div class="sales-container">
    <h2>Current Sales</h2>
    <form id="sales-form">
      <label for="medicine-barcode">Barcode</label>
      <input type="text" id="medicine-barcode" name="barcode" placeholder="Enter barcode" required>

      <label for="quantity">Quantity</label>
      <input type="number" id="quantity" name="quantity" min="1" value="1" required>

      <button type="submit">Add to Sale</button>
    </form>

    <table id="sales-table">
      <thead>
        <tr>
          <th>Medicine</th>
          <th>Quantity</th>
          <th>Cost Price</th>
          <th>Selling Price</th>
          <th>Total</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Sales items will be added here dynamically -->
      </tbody>
    </table>

    <label for="discount">Discount </label>
    <input type="number" id="discount" name="discount" min="0" value="0" required>

    <h3>Total: $<span id="total-price-usd">0.00</span> | IQD<span id="total-price-iqd">0</span></h3>
    <h3>Discounted Total: $<span id="discounted-total-price-usd">0.00</span> | IQD<span id="discounted-total-price-iqd">0</span></h3>

    <button id="finalize-sale">Finalize Sale</button>
  </div>

  <?php require_once '../includes/footer.php'; ?>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script src="../js/sales.js"></script>
</body>

</html>