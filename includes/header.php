<?php $currentUsername = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Fetch warning count
$warningQuery = "
    SELECT COUNT(*) AS warning_count FROM medicines 
    WHERE quantity <= (SELECT warning_quantity FROM settings WHERE id = 1) 
    OR expiry_date <= DATE_ADD(NOW(), INTERVAL (SELECT warning_expiry_days FROM settings WHERE id = 1) DAY)
";
$warningResult = $conn->query($warningQuery);
$warningCount = $warningResult->fetch_assoc()['warning_count'];
?>

<header>
  <div class="icons">
    <div class="icon" id="user-icon">
      <i class="fas fa-user"></i>
    </div>

    <a href="../pages/warnings.php" class="icon" id="notifications-icon">
      <i class="fas fa-exclamation-circle"></i>
      <?php if ($warningCount > 0) : ?>
        <span class="notification-count"><?php echo $warningCount; ?></span>
      <?php endif; ?>
    </a>

    <a href="../pages/settings.php" class="icon" id="settings-icon">
      <i class="fas fa-cog"></i>
    </a>

  </div>

  <div class="currency-selector">
    <label for="currency-select">دراو</label>
    <select id="currency-select">
      <option value="USD">$</option>
      <option value="IQD" selected>د.ع</option>
    </select>
  </div>

  <?php
  // Fetch the current exchange rate
  $query = "SELECT rate FROM exchange_rates WHERE currency = 'USD'";
  $result = $conn->query($query);

  if ($result->num_rows > 0) {
    $exchange_rate = $result->fetch_assoc()['rate'];
  } else {
    $exchange_rate = "نەزانراوە"; // Handle case where rate is not set
  }
  ?>

  <div class="exchange-rate">
    <span id="exchange-rate" data-exchange-rate="<?php echo htmlspecialchars($exchange_rate); ?>">1 دۆلار = <?php echo htmlspecialchars($exchange_rate); ?> دینار</span>
    <!-- Add a form to update the exchange rate -->
    <form action="../modules/utilities/update_exchange_rate.php" method="post">
      <input type="number" name="new_rate" step="any" placeholder="بەهای ئەمڕۆ">
      <button type="submit">گۆڕین</button>
    </form>
  </div>


  <nav class="navbar">
    <ul>
      <li><a href="dashboard.php">زانیارییەکان</a></li>
      <li><a href="medicines.php">دەرمانەکان</a></li>
      <li><a href="sales.php">فرۆشتن</a></li>
      <li><a href="sales_history.php">مێژووی فرۆشتن</a></li>
      <li><a href="user_activities.php">چالاکی بەکارهێنەرەکان</a></li>
    </ul>
  </nav>

  <div id="user-box">
    <span><?php echo $currentUsername; ?></span>
    <button id="update-user">گۆڕانکاری</button>
    <a href="../modules/users/logout.php">چوونەدەرەوە</a>
  </div>

  <div class="logo">
    <a href="dashboard.php">
      <img src="../assets/images/logo.png" alt="Logo">
    </a>
  </div>
</header>

<div id="account-modal" class="modal">
  <div class="modal-content">
    <i class="fas fa-times close"></i>
    <h2>گۆڕانکاری</h2>
    <form id="account-form" method="POST" action="../modules/users/update_user.php">
      <label for="new-username">ناوی نوێ:</label>
      <input type="text" id="new-username" name="new_username" value="<?php echo $currentUsername; ?>" required>
      <label for="current-password">وشەی نهێنی ئێستا</label>
      <input type="password" id="current-password" name="current_password" required>
      <label for="new-password">وشەی نهێنی نوێ:</label>
      <input type="password" id="new-password" name="new_password" required>
      <button type="submit" class="light-blue-btn">نوێکردنەوە</button>
    </form>
  </div>
</div>