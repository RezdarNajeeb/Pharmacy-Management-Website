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
  <title>Sales History</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
  <?php require_once '../includes/header.php' ?>

  <div class="sales-history-container">
    <h2>Sales History</h2>
    <table id="sales-history-table">
      <thead>
        <tr>
          <th>Medicine</th>
          <th>Quantity</th>
          <th>Cost Price</th>
          <th>Selling Price</th>
          <th>Total</th>
          <th>Discount</th>
          <th>Discounted Total</th>
          <th>Sold By</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $conn->prepare("SELECT medicines.name, sales_history.quantity, sales_history.cost_price, sales_history.selling_price, sales_history.total, sales_history.discount, sales_history.discounted_total, users.username, sales_history.created_at FROM sales_history JOIN medicines ON sales_history.medicine_id = medicines.id JOIN users ON sales_history.user_id = users.id ORDER BY sales_history.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
          echo '<tr>';
          echo '<td>' . htmlspecialchars($row['name']) . '</td>';
          echo '<td>' . htmlspecialchars($row['quantity']) . '</td>';
          echo '<td>' . htmlspecialchars(number_format($row['cost_price'], 2)) . '</td>';
          echo '<td>' . htmlspecialchars(number_format($row['selling_price'], 2)) . '</td>';
          echo '<td>' . htmlspecialchars(number_format($row['total'], 2)) . '</td>';
          echo '<td>' . htmlspecialchars(number_format($row['discount'], 2)) . '</td>';
          echo '<td>' . htmlspecialchars(number_format($row['discounted_total'], 2)) . '</td>';
          echo '<td>' . htmlspecialchars($row['username']) . '</td>';
          echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
          echo '</tr>';
        }
        $stmt->close();
        ?>
      </tbody>
    </table>
  </div>

  <?php
  require_once '../includes/footer.php';
  ?>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>
</body>
</html>