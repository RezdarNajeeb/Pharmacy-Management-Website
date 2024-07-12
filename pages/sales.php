<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="ckb" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>فرۆشتن</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
  <?php
  require_once '../includes/header.php';
  require_once '../includes/messages.php';
  ?>

  <div class="sales-container">
    <div class="right-cont">
      <h1 class="title">فرۆشتن</h1>
      <div class="top">
        <form id="add-sale-form">
          <div class="input-control">
            <label for="medicine-barcode">بارکۆد</label>
            <input type="text" id="medicine-barcode" name="barcode" placeholder="بارکۆد بنووسە یان سکان بکە" required>
          </div>

          <div class="input-control">
            <label for="quantity">بڕ</label>
            <input type="number" id="quantity" name="quantity" min="1" value="1" required>
          </div>

          <button type="submit" class="light-green-btn">زیادیکە بۆ خشتەی فرۆشتن</button>
        </form>
      </div>

      <div class="bottom">
        <div class="input-control">
          <label for="discount">داشکاندن </label>
          <input type="number" id="discount" name="discount" min="0" value="0" required>
        </div>

        <h3>کۆی گشتی: <span id="total-price-usd">0.00</span> $ | <span id="total-price-iqd">0</span> د.ع</h3>
        <h3>دوای داشکاندن: <span id="discounted-total-price-usd">0.00</span> $ | <span id="discounted-total-price-iqd">0</span> د.ع</h3>
        <button id="finalize-sale" class="light-blue-btn">بیفرۆشە</button>
      </div>
    </div>

    <div class="left-cont">
      <table id="sales-table" class="normal-table">
        <thead>
          <tr>
            <th>وێنە</th>
            <th>ناو</th>
            <th>بڕ</th>
            <th>نرخی کڕین</th>
            <th>نرخی فرۆشتن</th>
            <th>گشتی</th>
            <th>کردار</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sales items will be added here dynamically -->
           <tr>
            <td colspan="7"> هیچ دەرمانێک لە لیستی فرۆشتندا نییە. </td>
           </tr>
        </tbody>
      </table>
    </div>
  </div>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script src="../js/sales.js"></script>
</body>

</html>