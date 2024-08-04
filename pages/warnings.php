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
      WHERE (quantity <= $warning_quantity 
      OR (expiry_date IS NOT NULL AND expiry_date != '0000-00-00' AND expiry_date <= DATE_ADD(NOW(), INTERVAL $warning_expiry_days DAY)))
  ";
  $warningMedicinesResult = $conn->query($warningMedicinesQuery);
  ?>

  <div class="warnings-container">
    <h1 class="title">ئاگادارکردنەوەکان</h1>
    <form id="warnings-form">

      <h2>نوێکردنەوەی ئاگادارکردنەوەکان</h2>

      <div class="warnings-inner">
        <div>
          <label for="warning_quantity">کەمترین بڕ:</label>
          <input type="number" id="warning_quantity" name="warning_quantity" min="1" value="<?php echo htmlspecialchars($warning_quantity); ?>" required>
        </div>

        <div>
          <label for="warning_expiry_days">کەمترین ڕۆژ:</label>
          <input type="number" id="warning_expiry_days" name="warning_expiry_days" min="1" value="<?php echo htmlspecialchars($warning_expiry_days); ?>" required>
        </div>
      </div>

      <button type="submit" class="light-yellow-btn custom-font">نوێکردنەوە</button>
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
        <?php
        if ($warningMedicinesResult->num_rows === 0) {
          echo "<tr><td colspan='5'>هیچ زانیاریەک نییە</td></tr>";
        } else {

          while ($row = $warningMedicinesResult->fetch_assoc()) :
        ?>
            <tr>
              <?php $imageUrl = $row['image'] ? "../uploads/" . $row['image'] : "../assets/images/no-image.avif"; ?>
              <td><img src="<?= $imageUrl ?>" alt="Medicine Image"></td>
              <td><?php echo htmlspecialchars($row['name']); ?></td>
              <td><?php echo htmlspecialchars($row['category']); ?></td>
              <td <?php echo $row['quantity'] <= $warning_quantity ? "style='background-color: #FCDB58'" : "" ?>><?php echo htmlspecialchars($row['quantity']); ?></td>
              <?php
              $expiry_date = htmlspecialchars($row['expiry_date']);
              $current_date = date('Y-m-d');
              $warning_date = date('Y-m-d', strtotime("+$warning_expiry_days days"));

              if ($row['expiry_date'] <= $current_date) {
                echo "<td style='background-color: #FF6464'>$expiry_date</td>";
              } elseif (strtotime($row['expiry_date']) <= strtotime($warning_date)) {
                echo "<td style='background-color: #ff9966'>$expiry_date</td>";
              } else {
                echo "<td>$expiry_date</td>";
              }
              ?>
            </tr>
        <?php
          endwhile;
        } ?>
      </tbody>
    </table>
  </div>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script>
    $(function() {
      $('#warnings-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        let $warningQtyElement = $('#warning_quantity');
        let $warningExpiryDaysElement = $('#warning_expiry_days');
        let warningQty = $.trim($warningQtyElement.val());
        let warningExpiryDays = $.trim($warningExpiryDaysElement.val());

        let errorMessage = '';

        // Clear previous error messages
        $('.error-field').remove();

        // Validate input fields
        if (warningQty === '' || warningExpiryDays === '') {
          errorMessage = "<span class='error-field'>پێویستە هەموو خانەکان پڕ بکرێتەوە</span>";
        } else if (isNaN(warningQty) || isNaN(warningExpiryDays)) {
          errorMessage = "<span class='error-field'>پێویستە تەنها ژمارە بنووسیت.</span>";
        } else if (parseInt(warningQty) < 1 || parseInt(warningExpiryDays) < 1) {
          errorMessage = "<span class='error-field'>پێویستە ژمارەکە زیاتر بێت لە ١.</span>";
        }

        // Display error message if any
        if (errorMessage) {
          $(errorMessage).insertAfter('#warnings-form .warnings-inner').css("display", "block");
          return;
        }

        // Proceed with AJAX request if no errors
        $.ajax({
          url: '../modules/warnings/update_warning_settings.php',
          method: 'POST',
          data: {
            warning_quantity: warningQty,
            warning_expiry_days: warningExpiryDays
          },
          success: function(response) {
            location.reload();
          },
          error: function(xhr, status, error) {
            console.log(xhr.responseText);
          }
        });
      });
    });
  </script>
</body>

</html>