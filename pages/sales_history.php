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
  <title>مێژووی فرۆشتنەکان</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
  <?php
  require_once '../includes/header.php';
  require_once '../includes/messages.php';
  ?>

  <div class="sales-history-container">
    <h1 class="title">مێژووی فرۆشتنەکان</h1>

    <table id="sales-history-table" class="normal-table">
      <thead>
        <tr>
          <th>#</th>
          <th>کۆی گشتی</th>
          <th>داشکاندن</th>
          <th>دوای داشکاندن</th>
          <th>فرۆشراوە لەلایەن</th>
          <th>بەروار</th>
          <th>کردار</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $conn->prepare("SELECT sales_history.id, sales_history.total, sales_history.discount, sales_history.discounted_total, users.username, sales_history.created_at FROM sales_history JOIN users ON sales_history.user_id = users.id ORDER BY sales_history.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
          echo '<tr><td colspan="5">هیچ فرۆشتنێک نەدۆزرایەوە</td></tr>';
        }

        $counter = 1;
        while ($row = $result->fetch_assoc()) {
          $id = htmlspecialchars($row['id']);
          $number = htmlspecialchars($counter);
          $total = htmlspecialchars(number_format($row['total'], 2));
          $discount = htmlspecialchars(number_format($row['discount'], 2));
          $discountedTotal = htmlspecialchars(number_format($row['discounted_total'], 2));
          $username = htmlspecialchars($row['username']);
          $createdAt = htmlspecialchars($row['created_at']);

          echo <<<HTML
            <tr>
                <td>$number</td>
                <td>$total</td>
                <td>$discount</td>
                <td>$discountedTotal</td>
                <td>$username</td>
                <td>$createdAt</td>
                <td>
                    <button 
                        data-id="$id" 
                        data-number="$number" 
                        data-username="$username" 
                        data-sale-date="$createdAt" 
                        class="light-blue-btn view-sale-details">
                        وردەکاری
                    </button>
                </td>
            </tr>
            HTML;

          $counter++;
        }
        $stmt->close();
        ?>
      </tbody>
    </table>

    <div id="sale-details-modal" class="modal">
      <div class="modal-content">
        <i class="fas fa-times close"></i>
        <h2 class="title"></h2>
        <div id="sale-details"></div>
      </div>
    </div>
  </div>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script src="../js/sales.js"></script>
</body>

</html>