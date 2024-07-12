<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

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
  <title>ڕێکخستنەکان</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
  <?php require_once '../includes/header.php'; ?>
  <?php require_once '../includes/messages.php'; ?>

  <div class="settings-container">
    <h2 class="title">ڕێکخستنەکان</h2>

    <div class="sys-settings-section">
      <h3>پڕۆفایلی سیستەمەکە</h3>
      <form id="sys-profile-form" enctype="multipart/form-data">
        <div class="sys-profile-image">
          <img src="<?= "../uploads/" . $systemProfile['image']; ?>" alt="System Profile Image" id="profileImage">
          <label for="profileImageInput" class="edit-icon"><i class="fa-regular fa-pen-to-square"></i></label>
          <input type="file" id="profileImageInput" name="image" accept="image/*" style="display: none;">
        </div>

        <input type="text" id="systemName" class="sys-profile-name" name="name" value="<?php echo htmlspecialchars($systemProfile['name'], ENT_QUOTES, 'UTF-8'); ?>">

        <button type="submit" class="light-blue-btn">نوێکردنەوە</button>
      </form>
    </div>
  </div>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script>
    $(function() {
      $('#sys-profile-form').submit(function(e) {
        e.preventDefault();

        $.ajax({
          url: '../modules/utilities/update_system_profile.php',
          type: 'POST',
          data: new FormData(this),
          processData: false,
          contentType: false,
          success: function(response) {
            window.location.reload();
          }
        });
      });
    });
  </script>
</body>

</html>