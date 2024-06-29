<?php $currentUsername = isset($_SESSION['username']) ? $_SESSION['username'] : null; ?>

<header>
  <div class="icons">
    <div class="icon" id="user-icon">
      <i class="fas fa-user"></i>
    </div>

    <a href="search.php" class="icon" id="search-icon">
      <i class="fas fa-search"></i>
    </a>
  </div>

  <nav class="navbar">
    <ul>
      <li><a href="dashboard.php">زانیارییەکان</a></li>
      <li><a href="medicines.php">دەرمانەکان</a></li>
      <li><a href="sales.php">فرۆشتن</a></li>
      <li><a href="sales_history.php">مێژووی فرۆشتن</a></li>
      <li><a href="user_tracking.php">چالاکی بەکارهێنەرەکان</a></li>
    </ul>
  </nav>

  <div id="user-box">
    <span><?php echo $currentUsername; ?></span>
    <button id="update-user">گۆڕانکاری</button>
    <a href="../logout.php">چوونەدەرەوە</a>
  </div>

  <div class="logo">
    <a href="dashboard.php">
      <img src="../assets/images/logo.jpg" alt="Logo">
    </a>
  </div>
</header>

<div id="account-modal" class="modal">
  <div class="modal-content">
    <i class="fas fa-times close"></i>
    <h2>گۆڕانکاری</h2>
    <form id="account-form" method="POST" action="../update_user.php">
      <label for="new-username">ناوی نوێ:</label>
      <input type="text" id="new-username" name="new_username" value="<?php echo $currentUsername; ?>" required>
      <label for="current-password">وشەی نهێنی ئێستا</label>
      <input type="password" id="current-password" name="current_password" required>
      <label for="new-password">وشەی نهێنی نوێ:</label>
      <input type="password" id="new-password" name="new_password" required>
      <button type="submit">نوێکردنەوە</button>
    </form>
  </div>
</div>