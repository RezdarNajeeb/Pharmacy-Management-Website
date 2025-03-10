<?php
session_start();
require_once '../includes/db.php';
require_once '../modules/utilities/log_user_activity.php';

$_SESSION['messages'] = [];

// Check for remember me cookie
if (isset($_COOKIE['remember_me'])) {
  $token = $_COOKIE['remember_me'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ?");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Direct comparison since the token is hashed in the database
    if ($token === $user['remember_token']) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];

      // Log the user activity
      logUserActivity("چووە ناو سیستەمەکە لەڕێی منت لەبیر بێت.");

      header("Location: dashboard.php");
      exit();
    }
  }
}

if (isset($_POST['login'])) {
  $username = $conn->real_escape_string($_POST['username']);
  $password = $conn->real_escape_string($_POST['password']);
  $remember = isset($_POST['remember']);

  $stmt = $conn->prepare("SELECT * FROM users WHERE username= ?");
  $stmt->bind_param("s", $username);

  $stmt->execute();

  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];

      if ($remember) {
        $token = bin2hex(random_bytes(32)); // Generate a secure token
        $hashedToken = password_hash($token, PASSWORD_DEFAULT); // Hash the token for storage
        $expires = time() + (86400 * 30); // 30 days
        setcookie('remember_me', $token, $expires, "/", "", true, true); // Secure and HttpOnly flags

        $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedToken, $user['id']);
        $stmt->execute();
      }

      // Log the user activity
      logUserActivity("چووە ناو سیستەمەکە.");

      header("Location: dashboard.php");
      exit();
    } else {
      $_SESSION['messages'][] = ['type' => 'error', 'message' => "وشەی نهێنی هەڵەیە."];
    }
  } else {
    $_SESSION['messages'][] = ['type' => 'info', 'message' => "هیچ هەژمارێک بەم ناوەوە نییە."];
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
  <title>چوونەژوورەوە</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
  <?php require_once '../includes/messages.php'; ?>

  <div class="auth-forms-container">
    <form method="post" action="login.php" id="login-form">
      <h2>چوونەژوورەوە</h2>

      <div class="input-control">
        <label for="username">ناوی بەکارهێنەر:</label>
        <div class="input">
          <input type="text" id="username" name="username" required>
          <i class="fa fa-user"></i>
        </div>
        <span id="username-error"></span>
      </div>

      <div class="input-control">
        <label for="password">وشەی نهێنی:</label>
        <div class="input">
          <input type="password" id="password" name="password" required>
          <i class="fa fa-lock"></i>
        </div>
        <span id="password-error"></span>
      </div>

      <div class="show-pass-cont">
        <input type="checkbox" id="show-password">
        <label for="show-password">پیشاندانی وشەی نهێنی</label>
      </div>

      <div class="remember-me-cont">
        <input type="checkbox" id="remember" name="remember">
        <label for="remember">منت لەبیر بێت</label>
      </div>

      <button type="submit" class="light-blue-btn custom-font" name="login">بچۆ ژوورەوە</button>

      <p>هەژمارت نییە؟ <a href="register.php">هەژمارێک دروست بکە</a></p>
    </form>
  </div>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/validate.js"></script>

</body>

</html>