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
  require_once '../includes/messages.php'
  ?>

  <div class="sales-history-container">
    <h1 class="title">مێژووی فرۆشتنەکان</h1>

    <?php require_once '../includes/delete_all_container.php' ?>

    <table id="sales-history-table" class="normal-table">
      <thead>
        <tr>
          <th>وێنە</th>
          <th>ناو</th>
          <th>بڕ</th>
          <th>نرخی کڕین</th>
          <th>نرخی فرۆشتن</th>
          <th>کۆی گشتی</th>
          <th>داشکاندن</th>
          <th>دوای داشکاندن</th>
          <th>فرۆشراوە لەلایەن</th>
          <th>بەروار</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $conn->prepare("SELECT medicines.image, medicines.name, sales_history.quantity, sales_history.cost_price, sales_history.selling_price, sales_history.total, sales_history.discount, sales_history.discounted_total, users.username, sales_history.created_at FROM sales_history JOIN medicines ON sales_history.medicine_id = medicines.id JOIN users ON sales_history.user_id = users.id ORDER BY sales_history.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
          echo '<tr>';
          echo '<td><img src="../uploads/' . htmlspecialchars($row['image']) . '"</td>';
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



  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script src="../js/delete_all.js"></script>
</body>

</html>