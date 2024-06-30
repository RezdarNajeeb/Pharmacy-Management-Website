<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ckb" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>زانیارییەکان</title>
    <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stat {
            flex: 1;
            background: var(--primary-color);
            color: white;
            padding: 20px;
            margin: 0 10px;
            text-align: center;
            border-radius: 10px;
        }

        .stat h2 {
            margin-bottom: 10px;
        }

        .quick-links {
            text-align: center;
        }

        .quick-links .btn {
            display: inline-block;
            margin: 10px;
            padding: 15px 30px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .quick-links .btn:hover {
            background: var(--secondary-color);
        }
    </style>
</head>

<body>
    <?php
    // Fetch data from the database
    $total_medicines = $conn->query("SELECT COUNT(*) AS count FROM medicines")->fetch_assoc()['count'];
    $total_sales = $conn->query("SELECT COUNT(*) AS count FROM sales_history")->fetch_assoc()['count'];
    $low_stock_medicines = $conn->query("SELECT COUNT(*) AS count FROM medicines WHERE quantity <= (SELECT warning_quantity FROM settings WHERE id = 1)")->fetch_assoc()['count'];
    $upcoming_expiries = $conn->query("SELECT COUNT(*) AS count FROM medicines WHERE expiry_date <= DATE_ADD(NOW(), INTERVAL (SELECT warning_expiry_days FROM settings WHERE id = 1) DAY)")->fetch_assoc()['count'];

    include '../includes/header.php';
    ?>

    <div class="dashboard">
        <h1>داشبۆرد</h1>
        <div class="stats">
            <div class="stat">
                <h2>هەموو دەرمانەکان</h2>
                <p><?php echo $total_medicines; ?></p>
            </div>
            <div class="stat">
                <h2>هەموو فرۆشتنەکان</h2>
                <p><?php echo $total_sales; ?></p>
            </div>
            <div class="stat">
                <h2>کەمترین ستۆک</h2>
                <p><?php echo $low_stock_medicines; ?></p>
            </div>
            <div class="stat">
                <h2>نزیك بە بەسەرچوون</h2>
                <p><?php echo $upcoming_expiries; ?></p>
            </div>
        </div>
        <div class="quick-links">
            <a href="medicines.php" class="btn">بەڕێوەبردنی دەرمانەکان</a>
            <a href="sales.php" class="btn">بەڕێوەبردنی فرۆشتنەکان</a>
            <a href="user_tracking.php" class="btn">بەدواداچوونی بەکارهێنەر</a>
            <a href="warnings.php" class="btn">ئاگادارییەکان</a>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <script src="../js/lib/jquery-3.7.1.min.js"></script>
    <script src="../js/scripts.js"></script>
</body>

</html>