<?php
require_once '../includes/db.php';
session_start();

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
  <title>warnings</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">

  <style>
    .notifications-icon {
      position: relative;
      color: #ff0000;
      text-decoration: none;
      font-size: 1.5rem;
    }

    .notification-count {
      position: absolute;
      top: -5px;
      right: -5px;
      background-color: red;
      color: white;
      border-radius: 50%;
      padding: 2px 5px;
      font-size: 0.75rem;
    }
  </style>
</head>

<body>
  <?php
  include '../includes/header.php';

  // Fetch warning settings
  $settingsQuery = "SELECT * FROM settings WHERE id = 1";
  $settingsResult = $conn->query($settingsQuery);
  $settings = $settingsResult->fetch_assoc();

  $warning_quantity = $settings['warning_quantity'];
  $warning_expiry_days = $settings['warning_expiry_days'];

  // Fetch medicines with low quantity or near expiry date
  $warningMedicinesQuery = "
    SELECT * FROM medicines 
    WHERE quantity <= $warning_quantity 
    OR expiry_date <= DATE_ADD(NOW(), INTERVAL $warning_expiry_days DAY)
";
  $warningMedicinesResult = $conn->query($warningMedicinesQuery);
  ?>

  <div class="warnings-container">
    <h2>Warnings</h2>
    <form id="settings-form">
      <div>
        <label for="warning_quantity">Warning Quantity:</label>
        <input type="number" id="warning_quantity" name="warning_quantity" value="<?php echo htmlspecialchars($warning_quantity); ?>">
      </div>
      <div>
        <label for="warning_expiry_days">Warning Expiry Days:</label>
        <input type="number" id="warning_expiry_days" name="warning_expiry_days" value="<?php echo htmlspecialchars($warning_expiry_days); ?>">
      </div>
      <button type="submit">Save Settings</button>
    </form>
    <h3>Medicines with Warnings</h3>
    <table id="warnings-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Expiry Date</th>
          <th>Image</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $warningMedicinesResult->fetch_assoc()) : ?>
          <tr>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['category']); ?></td>
            <td><?php echo htmlspecialchars($row['price']); ?></td>
            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
            <td><?php echo htmlspecialchars($row['expiry_date']); ?></td>
            <td><img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Medicine Image"></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <?php
  require_once '../includes/footer.php';
  ?>


  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script>
    $(document).ready(function() {
      $('#settings-form').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
          url: '../modules/utilities/update_settings.php',
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