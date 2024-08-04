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
        <input type="hidden" name="existing-image" value="<?= "../uploads/" . $systemProfile['image']; ?>">
        <div class="sys-profile-image">
          <img src="<?= "../uploads/" . $systemProfile['image']; ?>" alt="System Profile Image" id="profileImage">
          <label for="profileImageInput" class="edit-icon"><i class="fa-regular fa-pen-to-square"></i></label>
          <input type="file" id="profileImageInput" name="edit-image" accept="image/jpg, image/jpeg, image/png" class="file-input">
        </div>

        <input type="text" id="systemName" class="sys-profile-name custom-font" name="name" value="<?php echo htmlspecialchars($systemProfile['name'], ENT_QUOTES, 'UTF-8'); ?>" required>

        <button type="submit" class="light-yellow-btn custom-font">نوێکردنەوە</button>
      </form>
    </div>
  </div>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script>
    $(function() {
      $('#sys-profile-form').submit(function(e) {
        e.preventDefault();

        const $systemNameElement = $('#systemName');
        const systemName = $systemNameElement.val();

        const nameRegex = /^[a-zA-Z0-9\u0600-\u06FF][a-zA-Z0-9\u0600-\u06FF\s]*$/;
        let errorMessage = '';

        // Clear previous error messages
        $('.error-field').remove();

        if (!systemName) {
          errorMessage = "<span class='error-field'>ناوی سیستەم پێویستە پڕبکرێتەوە.</span>";
        } else if (!nameRegex.test(systemName)) {
          errorMessage = "<span class='error-field'>ناوی سیستەم پێویستە بە پیت یان ژمارە دەست پێبکات و تەنها پیت و ژمارە و بۆشایی وەردەگرێت.</span>";
        } else if (systemName.length > 25) {
          errorMessage = "<span class='error-field'>ناوی سیستەم نابێت زیاتر لە ٢٥ پیت بێت.</span>";
        }

        if (errorMessage) {
          $systemNameElement.after(errorMessage);
          $('.error-field').css("display", "block");
          return;
        }

        $.ajax({
          url: '../modules/settings/update_system_profile.php',
          type: 'POST',
          data: new FormData(this),
          processData: false,
          contentType: false,
          success: function(response) {
            window.location.reload();
          },
          error: function(xhr, status, error) {
            console.error(error);
          }
        });
      });
    });
  </script>
</body>

</html>