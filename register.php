<?php
require_once 'includes/db.php';

if (isset($_POST['register'])) {
  $username = $conn->real_escape_string($_POST['username']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  if ($password !== $confirm_password) {
    echo "وشەی نهێنی و وشەی نهێنیی دڵنیایی وەک یەک نین.";
    exit();
  }

  // Check if username already exists
  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    echo "هەژمارێکی تر بە هەمان ناوەوە هەیە، تکایە ناوێکی جیاواز بەکاربێنە.";
    exit();
  }

  $password = password_hash($password, PASSWORD_BCRYPT);

  $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
  $stmt->bind_param("ss", $username, $password);

  if ($stmt->execute()) {
    echo "هەژمارێکی نوێ دروستکرا.";
  } else {
    echo "Error: " . $stmt->error;
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
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <div class="auth-forms-container">
    <form method="post" action="register.php" id="register-form">
      <h2>دروستکردنی هەژمار</h2>
      <label for="username">ناوی بەکارهێنەر: </label>
      <input type="text" id="username" name="username" required>
      <label for="password">وشەی نهێنی</label>
      <input type="password" id="password" name="password" required>
      <label for="confirm_password">وشەی نهێنیی دڵنیایی</label>
      <input type="password" id="confirm_password" name="confirm_password" required>
      <div class="show-pass-cont">
        <label for="show-password">پیشاندانی وشەی نهێنی</label>
        <input type="checkbox" id="show-password">
      </div>
      <button type="submit" name="register">دروستکردن</button>
      <a href="login.php">چوونەژوورەوە</a>
    </form>
  </div>

  <script src="js/lib/jquery-3.7.1.min.js"></script>
  <script src="js/validate.js"></script>
</body>

</html>