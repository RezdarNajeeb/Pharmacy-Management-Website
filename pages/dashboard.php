<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Combined database query to fetch all required counts in one go
$query = "
    SELECT 
        (SELECT COUNT(*) FROM medicines) AS total_medicines,
        (SELECT COUNT(*) FROM sales_history) AS total_sales,
        (SELECT COUNT(*) FROM medicines WHERE quantity <= (SELECT warning_quantity FROM settings WHERE id = 1)) AS low_stock_medicines,
        (SELECT COUNT(*) FROM medicines WHERE expiry_date <= DATE_ADD(NOW(), INTERVAL (SELECT warning_expiry_days FROM settings WHERE id = 1) DAY)) AS upcoming_expiries
    FROM dual
";
$result = $conn->query($query);
$stats = $result->fetch_assoc();

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>زانیارییەکان</title>
    <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/dashboard.css"> <!-- Externalized CSS -->
</head>
<body>
    <div class="dashboard">
        <h1>داشبۆرد</h1>
        <div class="stats">
            <div class="stat">
                <h2>هەموو دەرمانەکان</h2>
                <p><?php echo $stats['total_medicines']; ?></p>
            </div>
            <div class="stat">
                <h2>هەموو فرۆشتنەکان</h2>
                <p><?php echo $stats['total_sales']; ?></p>
            </div>
            <div class="stat">
                <h2>کەمترین ستۆک</h2>
                <p><?php echo $stats['low_stock_medicines']; ?></p>
            </div>
            <div class="stat">
                <h2>نزیك بە بەسەرچوون</h2>
                <p><?php echo $stats['upcoming_expiries']; ?></p>
            </div>
        </div>
        <div class="quick-links">
            <!-- Quick links remain unchanged -->
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <script src="../js/lib/jquery-3.7.1.min.js"></script>
    <script src="../js/scripts.js"></script>
</body>
</html>