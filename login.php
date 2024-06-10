<?php
require_once 'includes/db.php';
session_start();

// Check for remember me cookie
if (isset($_COOKIE['remember_me'])) {
  $token = $_COOKIE['remember_me'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ?");
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($token, $user['remember_token'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      header("Location: pages/dashboard.php");
      exit();
    }
  }
}

if (isset($_POST['login'])) {
  $username = $conn->real_escape_string($_POST['username']);
  $password = $_POST['password'];
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

      if ($remember) {
        $token = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT); // Generate a secure token
        $expires = time() + (86400 * 30); // 30 days
        setcookie('remember_me', $token, $expires, "/", "", true, true); // Secure and HttpOnly flags

        $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmt->bind_param("si", $token, $user['id']);
        $stmt->execute();
      }

      header("Location: pages/dashboard.php");
      exit();
    } else {
      echo "وشەی نهێنی هەڵەیە.";
    }
  } else {
    echo "هیچ هەژمارێک بەم ناوەوە نییە.";
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
  <link rel="stylesheet" href="css/styles.css">
</head>

<body>
  <h2>چوونەژوورەوە</h2>
  <form method="POST" action="login.php" id="login-form">
    <label for="username">ناوی بەکارهێنەر:</label>
    <input type="text" id="username" name="username" required><br>
    <label for="password">وشەی نهێنی:</label>
    <input type="password" id="password" name="password" required><br>
    <label for="show-password">پیشاندانی وشەی نهێنی</label>
    <input type="checkbox" id="show-password"><br>
    <label for="remember">منت لەبیر بێت</label>
    <input type="checkbox" id="remember" name="remember"><br>
    <button type="submit" name="login">بچۆ ژوورەوە</button>
    <a href="register.php">دروستکردنی هەژمار</a>
  </form>

  <script src="js/lib/jquery-3.7.1.min.js"></script>
  <script src="js/validate.js"></script>

</body>

</html>