<?php
session_start();
require_once '../includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="ckb" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ئاگادارکردنەوەکان</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
  <?php
  require_once '../includes/header.php';
  require_once '../includes/messages.php';

  // Fetch warning settings
  $warningSettingsQuery = "SELECT * FROM warning_settings WHERE id = 1";
  $warningSettingsResult = $conn->query($warningSettingsQuery);
  $warningSettings = $warningSettingsResult->fetch_assoc();

  $warning_quantity = $warningSettings['warning_quantity'];
  $warning_expiry_days = $warningSettings['warning_expiry_days'];

  // Fetch medicines with low quantity or near expiry date
  $warningMedicinesQuery = "
    SELECT * FROM medicines 
    WHERE quantity <= $warning_quantity 
    OR expiry_date <= DATE_ADD(NOW(), INTERVAL $warning_expiry_days DAY)
";
  $warningMedicinesResult = $conn->query($warningMedicinesQuery);
  ?>

  <div class="warnings-container">
    <h1 class="title">ئاگادارکردنەوەکان</h1>
    <form id="warnings-form">

      <h2>نوێکردنەوەی ئاگادارکردنەوەکان</h2>

      <div>
        <label for="warning_quantity">کەمترین بڕ:</label>
        <input type="number" id="warning_quantity" name="warning_quantity" value="<?php echo htmlspecialchars($warning_quantity); ?>">
      </div>
      
      <div>
        <label for="warning_expiry_days">کەمترین ڕۆژ:</label>
        <input type="number" id="warning_expiry_days" name="warning_expiry_days" value="<?php echo htmlspecialchars($warning_expiry_days); ?>">
      </div>

      <button type="submit" class="light-blue-btn">نوێکردنەوە</button>
    </form>

    <table id="warnings-table" class="normal-table">
      <thead>
        <tr>
          <th>وێنە</th>
          <th>ناو</th>
          <th>جۆر</th>
          <th>بڕ</th>
          <th>بەرواری بەسەرچوون</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $warningMedicinesResult->fetch_assoc()) : ?>
          <tr>
            <td><img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Medicine Image"></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['category']); ?></td>
            <td <?php echo $row['quantity'] <= $warning_quantity ? "style='background-color: #FCDB58'" : "" ?>><?php echo htmlspecialchars($row['quantity']); ?></td>
            <td <?php echo strtotime($row['expiry_date']) <= strtotime(date('Y-m-d', strtotime("+$warning_expiry_days days"))) ? "style='background-color: #ff9966'" : "" ?>><?php echo htmlspecialchars($row['expiry_date']); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script>
    $(function() {
      $('#warnings-form').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
          url: '../modules/warnings/update_warning_settings.php',
          method: 'POST',
          data: {
            warning_quantity: $('#warning_quantity').val(),
            warning_expiry_days: $('#warning_expiry_days').val()
          },
          dataType: 'json',
          success: function(response) {
            alert(response.message);
            location.reload();
          },
          error: function(xhr, status, error) {
            console.log(xhr.responseText);
            alert('Error: ' + error);
          }
        });
      });
    });
  </script>
</body>

</html>