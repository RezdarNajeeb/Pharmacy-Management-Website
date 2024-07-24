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

    <?php require_once '../includes/delete_all_container.php' ?>

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
  <script>
    $(document).ready(function() {
      let intervalId;

      function deleteTableData(days, isImmediate = false) {
        $.ajax({
          url: '../modules/utilities/delete_all.php',
          type: 'POST',
          data: {
            table: 'user_activities',
            days: days,
            isImmediate: isImmediate
          },
          success: function(response) {
            if (isImmediate) {
              window.location.reload();
            }
          },
          error: function(xhr, status, error) {
            console.error("Error: " + status + " " + error);
          }
        });
      }

      function setDeleteInterval(days) {
        const milliseconds = days * 24 * 60 * 60 * 1000;

        if (intervalId) {
          clearInterval(intervalId);
        }

        intervalId = setInterval(function() {
          deleteTableData(days);
        }, milliseconds);

        const nextRunDate = new Date(Date.now() + milliseconds);
        localStorage.setItem('nextRun', nextRunDate.toISOString());

        $.post('../modules/utilities/update_next_delete_all.php', {
          nextRun: nextRunDate.toISOString()
        });
      }

      $('#deleteDataButton').click(function() {
        if (confirm('Are you sure you want to delete the data now?')) {
          const days = $('#intervalSelect').val();
          deleteTableData(days, true);
        }
      });

      const savedValue = localStorage.getItem('selectedOption');
      if (savedValue) {
        $('#intervalSelect').val(savedValue);
      }

      $('#intervalSelect').change(function() {
        const selectedValue = $(this).val();
        localStorage.setItem('selectedOption', selectedValue);
        setDeleteInterval(selectedValue);
      });

      const nextRun = localStorage.getItem('nextRun');
      if (nextRun) {
        const nextRunDate = new Date(nextRun);
        const currentTime = new Date();
        const remainingTimeMs = nextRunDate.getTime() - currentTime.getTime();

        if (remainingTimeMs > 0) {
          setTimeout(function() {
            const days = $('#intervalSelect').val();
            deleteTableData(days);
            setDeleteInterval(days);
          }, remainingTimeMs);
        } else {
          const days = $('#intervalSelect').val();
          deleteTableData(days);
          setDeleteInterval(days);
        }
      } else {
        setDeleteInterval($('#intervalSelect').val());
      }
    });
  </script>


</body>

</html>