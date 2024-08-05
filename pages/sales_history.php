<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sale_id'])) {

  $saleId = $_POST['sale_id'];

  try {
    $stmt = $conn->prepare("DELETE FROM sales_history WHERE id = ?");
    $stmt->bind_param('i', $saleId);
    $stmt->execute();
    $stmt->close();

    $_SESSION['messages'][] = ['type' => 'success', 'message' => 'فرۆشتنەکە بەسەرکەوتوی سڕایەوە'];
  } catch (Exception $e) {
    $_SESSION['messages'][] = ['type' => 'error', 'message' => 'هەڵەیەک ڕوویدا: ' . $e->getMessage()];
  }

  header('Location: sales_history.php');
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
        $stmt = $conn->prepare("SELECT sales_history.id, sales_history.totalIQD, sales_history.discount, sales_history.discount_currency, sales_history.discounted_totalIQD, users.username, sales_history.created_at FROM sales_history JOIN users ON sales_history.user_id = users.id ORDER BY sales_history.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
          echo '<tr><td colspan="5">هیچ فرۆشتنێک نەدۆزرایەوە</td></tr>';
        }

        $counter = 1;
        while ($row = $result->fetch_assoc()) {
          $id = htmlspecialchars($row['id']);
          $number = htmlspecialchars($counter);

          $totalIQD = htmlspecialchars($row['totalIQD']);
          $totalUSD = floatval($totalIQD) / $exchange_rate;

          if ($row['discount_currency'] === 'USD') {
            $discountUSD = htmlspecialchars($row['discount']);
            $discountIQD = floatval($discountUSD) * $exchange_rate;
          } else {
            $discountIQD = htmlspecialchars($row['discount']);
            $discountUSD = floatval($discountIQD) / $exchange_rate;
          }

          $discountedTotalIQD = htmlspecialchars($row['discounted_totalIQD']);
          $discountedTotalUSD = floatval($discountedTotalIQD) / $exchange_rate;

          $username = htmlspecialchars($row['username']);
          $createdAt = htmlspecialchars($row['created_at']);

          $totalIQD = number_format($totalIQD, 0, false, false);
          $totalUSD = number_format($totalUSD, 2);
          $discountIQD = number_format($discountIQD, 0, false, false);
          $discountUSD = number_format($discountUSD, 2);
          $discountedTotalIQD = number_format($discountedTotalIQD, 0, false, false);
          $discountedTotalUSD = number_format($discountedTotalUSD, 2);

          echo <<<HTML
            <tr>
                <td>$number</td>
                <td>$totalUSD $<br><br>$totalIQD IQD</td>
                <td>$discountUSD $<br><br>$discountIQD IQD</td>
                <td>$discountedTotalUSD $<br><br>$discountedTotalIQD IQD</td>
                <td>$username</td>
                <td>$createdAt</td>
                <td>
                    <div class="actions">
                      <button
                          data-id="$id"
                          data-number="$number"
                          data-username="$username"
                          data-sale-date="$createdAt"
                          class="light-blue-btn view-sale-details">
                          <i class="fa-solid fa-circle-info"></i>
                      </button>
                      <form action="sales_history.php" method="POST" onsubmit="return confirm('دڵنیایت کە دەتەوێت ئەم مێژووی فرۆشتنە بسڕیتەوە؟')">
                          <input type="hidden" name="sale_id" value="$id">
                          <button type="submit" class="red-btn">
                              <i class="fa-solid fa-trash"></i>
                          </button>
                      </form>
                    </div>
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