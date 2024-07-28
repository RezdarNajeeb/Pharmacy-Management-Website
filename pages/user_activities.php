<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="ckb" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>چالاکی بەکارهێنەرەکان</title>
  <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
  <?php
  require_once '../includes/header.php';
  require_once '../includes/messages.php';
  ?>

  <div class="user-tracking-container">
    <h1 class="title">چالاکی بەکارهێنەرەکان</h1>

    <table id="user-activity-table" class="normal-table">
      <thead>
        <tr>
          <th>بەکارهێنەر</th>
          <th>چالاکی</th>
          <th>بەروار</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $conn->prepare("SELECT users.username, user_activities.activity, user_activities.created_at FROM user_activities JOIN users ON user_activities.user_id = users.id ORDER BY user_activities.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
          echo '<tr><td colspan="3">هیچ چالاکییەک نەدۆزرایەوە</td></tr>';
        }
        while ($row = $result->fetch_assoc()) {
          echo '<tr>';
          echo '<td>' . htmlspecialchars($row['username']) . '</td>';
          echo '<td>' . htmlspecialchars($row['activity']) . '</td>';
          echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
          echo '</tr>';
        }
        $stmt->close();
        ?>
      </tbody>
    </table>
  </div>

  <script src="../js/lib/jquery-3.7.1.min.js"></script>
  <script src="../js/scripts.js"></script>
  <script src="../js/delete_all.js"></script>
</body>

</html>