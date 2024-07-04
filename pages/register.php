<?php
require_once '../includes/db.php';

$messages = [];

if (isset($_POST['register'])) {
  $username = $conn->real_escape_string($_POST['username']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  if ($password === $confirm_password) {

    // Check if username already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result->num_rows > 0) {

      $password = password_hash($password, PASSWORD_BCRYPT);

      $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
      $stmt->bind_param("ss", $username, $password);

      if ($stmt->execute()) {
        $messages[] = ['type' => 'success', 'message' => "هەژمارەکەت بەسەرکەوتویی دروستکرا."];
        header("refresh:2;url=login.php");
      } else {
        $messages[] = ['type' => 'error', 'message' => "کێشەیەک ڕوویدا، هەژمارەکەت دروست نەبوو."];
      }
    } else {
      $messages[] = ['type' => 'error', 'message' => "ئەم ناوە پێشتر بەکارهێنراوە."];
    }
  } else {
    $messages[] = ['type' => 'error', 'message' => "وشەی نهێنی و وشەی نهێنیی دڵنیایی وەک یەک نین."];
  }

  $stmt->close();
  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ckb" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>دروستکردنی هەژمار</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
  <?php require_once '../includes/messages.php'; ?>

  <div class="auth-forms-container">
    <form method="post" action="register.php" id="register-form">
      <h2>دروستکردنی هەژمار</h2>

      <div class="input-control">
        <label for="username">ناوی بەکارهێنەر: </label>
        <div class="input">
          <input type="text" id="username" name="username" required>
          <i class="fa fa-user"></i>
        </div>
        <span id="username-error"></span>
      </div>

      <div class="input-control">
        <label for="password">وشەی نهێنی</label>
        <div class="input">
          <input type="password" id="password" name="password" required>
          <i class="fa fa-lock"></i>
        </div>
        <span id="password-error"></span>
      </div>

      <div class="input-control">
        <label for="confirm_password">وشەی نهێنیی دڵنیایی</label>
        <div class="input">
          <input type="password" id="confirm_password" name="confirm_password" required>
          <i class="fa fa-lock"></i>
        </div>
        <span id="confirm-password-error"></span>
      </div>

      <div class="show-pass-cont">
        <label for="show-password">پیشاندانی وشەی نهێنی</label>
        <input type="checkbox" id="show-password">
      </div>

      <button type="submit" name="register" class="light-green-btn">دروستکردن</button>

      <p>هەژمارت هەیە؟ <a href="login.php">بچۆ ژوورەوە</a></p>
    </form>
  </div>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/validate.js"></script>
</body>

</html>