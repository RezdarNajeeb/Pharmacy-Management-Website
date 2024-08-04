<?php $currentUsername = isset($_SESSION['username']) ? $_SESSION['username'] : null;

$isAdmin = $_SESSION['role'] === 'admin';

// Fetch warning count
$warningQuery = "
    SELECT COUNT(*) AS warning_count FROM medicines 
    WHERE quantity <= (SELECT warning_quantity FROM warning_settings WHERE id = 1) 
    OR (expiry_date IS NOT NULL AND expiry_date != '0000-00-00' AND expiry_date <= DATE_ADD(NOW(), INTERVAL (SELECT warning_expiry_days FROM warning_settings WHERE id = 1) DAY))
";
$warningResult = $conn->query($warningQuery);
$warningCount = $warningResult->fetch_assoc()['warning_count'];

$warning_expiry_days = $conn->query("SELECT warning_expiry_days FROM warning_settings WHERE id = 1")->fetch_assoc()['warning_expiry_days'];
?>
<script defer>
  var warningExpiryDays = <?php echo json_encode($warning_expiry_days); ?>;
</script>

<header id="header">
  <div class="icons">
    <div class="icon" id="user-icon">
      <i class="fas fa-user"></i>
    </div>

    <a href="../pages/warnings.php" class="icon" id="notifications-icon">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <?php if ($warningCount > 0) : ?>
        <span class="notification-count"><?php echo $warningCount; ?></span>
      <?php endif; ?>
    </a>

    <?php if ($isAdmin) { ?>
      <a href="../pages/settings.php" class="icon" id="settings-icon">
        <i class="fas fa-cog"></i>
      </a>
    <?php } ?>

  </div>

  <div class="currency-selector">
    <label for="currency-select"><i class="fa-solid fa-money-bill-transfer"></i></label>
    <select id="currency-select">
      <option value="USD">$</option>
      <option value="IQD">IQD</option>
    </select>
  </div>

  <?php
  // Fetch the current exchange rate or insert if it doesn't exist
  $query = "SELECT rate FROM exchange_rates WHERE currency = 'USD'";
  $result = $conn->query($query);

  if ($result->num_rows == 0) {
    // If no rows are returned, insert the new currency and rate
    $insert_query = "INSERT INTO exchange_rates (currency, rate) VALUES ('USD', 1450)";
    $conn->query($insert_query);
    $exchange_rate = 1450;
  } else {
    $exchange_rate = $result->fetch_assoc()['rate'];
  }

  if (!$exchange_rate) {
    $exchange_rate = "نەزانراوە"; // Handle case where rate is not set
  }
  ?>

  <div class="exchange-rate">
    <span id="exchange-rate" data-exchange-rate="<?php echo htmlspecialchars($exchange_rate); ?>">1 دۆلار = <?php echo htmlspecialchars(number_format($exchange_rate, 2, '.', false)); ?> دینار</span>
    <!-- Add a form to update the exchange rate -->
    <form id="update-exc-rate-form" action="../modules/utilities/update_exchange_rate.php" method="post">
      <input type="number" id="exchange-rate-input" name="new_rate" min="1" step="any" placeholder="بەهای ئەمڕۆ" class="custom-font" required>
      <button type="submit" class="custom-font">گۆڕین</button>
    </form>
  </div>


  <nav class="navbar">
    <ul>
      <li><a href="dashboard.php">زانیارییەکان</a></li>
      <li><a href="medicines.php">دەرمانەکان</a></li>
      <li><a href="sales.php">فرۆشتن</a></li>
      <?php if ($isAdmin) { ?>
        <li><a href="sales_history.php">مێژووی فرۆشتن</a></li>
        <li><a href="user_activities.php">چالاکی بەکارهێنەرەکان</a></li>
      <?php } ?>
    </ul>
  </nav>

  <div id="user-box">
    <span><?php echo $currentUsername; ?></span>
    <button id="update-user"><i class="fa-solid fa-user-pen"></i></button>
    <a href="../modules/users/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
  </div>

  <?php
  $sql = "SELECT * FROM system_profile WHERE id = 1";
  $result = $conn->query($sql);
  $system_profile = $result->fetch_assoc();
  ?>

  <div class="logo">
    <a href="dashboard.php">
      <img src=<?= "../uploads/" . $system_profile['image'] ?> alt="profile">
    </a>
  </div>
</header>

<div id="account-modal" class="modal">
  <div class="modal-content">
    <i class="fas fa-times close"></i>
    <h2 class="title">گۆڕانکاری</h2>
    <form id="account-form" method="POST" action="../modules/users/update_user.php">

      <label for="new-username">ناوی نوێ:</label>
      <input type="text" id="new-username" name="new_username" value="<?php echo $currentUsername; ?>" required>
      <span id="new-username-error" class="error-field"></span>

      <label for="current-password">وشەی نهێنی ئێستا</label>
      <input type="password" id="current-password" name="current_password" required>
      <span id="current-password-error" class="error-field"></span>

      <label for="new-password">وشەی نهێنی نوێ:</label>
      <input type="password" id="new-password" name="new_password" required>
      <span id="new-password-error" class="error-field"></span>

      <div class="show-pass-cont">
        <input type="checkbox" id="show-password">
        <label for="show-password">پیشاندانی وشەی نهێنی</label>
      </div>

      <button type="submit" class="light-yellow-btn custom-font">نوێکردنەوە</button>
    </form>
  </div>
</div>