<?php
require_once '../includes/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// Fetch current user details
$user_id = $_SESSION['user_id'];
$userQuery = $conn->prepare("SELECT * FROM users WHERE id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

// Fetch system profile details
$systemQuery = $conn->prepare("SELECT * FROM system_profile WHERE id = 1");
$systemQuery->execute();
$systemResult = $systemQuery->get_result();
$systemProfile = $systemResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ckb" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>warnings</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
  <?php require_once '../includes/header.php'; ?>

  <div class="settings-container">
    <h2>Settings</h2>

    <div class="settings-section">
      <h3>System Profile</h3>
      <form action="../modules/utilities/update_system_profile.php" method="post" enctype="multipart/form-data">
        <label for="system-name">System Name:</label>
        <input type="text" id="system-name" name="system_name" value="<?php echo htmlspecialchars($systemProfile['name']); ?>" required>

        <label for="system-profile-picture">Profile Picture:</label>
        <input type="file" id="system-profile-picture" name="system_profile_picture">

        <button type="submit">Update System Profile</button>
      </form>
    </div>

    <div class="settings-section">
      <h3>User Profile</h3>
      <form action="../modules/users/update_user.php" method="post">
        <label for="new-username">New Username:</label>
        <input type="text" id="new-username" name="new_username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label for="current-password">Current Password:</label>
        <input type="password" id="current-password" name="current_password" required>

        <label for="new-password">New Password:</label>
        <input type="password" id="new-password" name="new_password" required>

        <button type="submit">Update User Profile</button>
      </form>
    </div>
  </div>

  <?php require_once '../includes/footer.php'; ?>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>

</body>

</html>