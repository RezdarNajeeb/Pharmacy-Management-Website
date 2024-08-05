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
        (SELECT COUNT(*) FROM medicines WHERE quantity <= (SELECT warning_quantity FROM warning_settings WHERE id = 1)) AS low_stock_medicines,
        (SELECT COUNT(*) FROM medicines WHERE expiry_date <= DATE_ADD(NOW(), INTERVAL (SELECT warning_expiry_days FROM warning_settings WHERE id = 1) DAY)) AS upcoming_expiries
    FROM dual
";
$result = $conn->query($query);
$stats = $result->fetch_assoc();

$sql = "SELECT * FROM system_profile WHERE id = 1";
$result = $conn->query($sql);
$system_profile = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ckb" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>زانیارییەکان</title>
    <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <?php
    require_once '../includes/header.php';
    require_once '../includes/messages.php';
    ?>

    <div class="dashboard">
        <h2 class="title">زانیارییەکان</h2>
        <div class="stats">
            <a href="medicines.php">
                <div class="stat">
                    <h2>هەموو بەرهەمەکان</h2>
                    <p><?php echo $stats['total_medicines']; ?></p>
                </div>
            </a>
            <?php if ($isAdmin) { ?>
                <a href="sales_history.php">
                    <div class="stat">
                        <h2>هەموو فرۆشتنەکان</h2>
                        <p><?php echo $stats['total_sales']; ?></p>
                    </div>
                </a>
            <?php } ?>
            <a href="warnings.php">
                <div class="stat">
                    <h2>بەرهەمە کەمبووەکان</h2>
                    <p><?php echo $stats['low_stock_medicines']; ?></p>
                </div>
            </a>
            <a href="warnings.php">
                <div class="stat">
                    <h2>نزیك لە بەسەرچوون</h2>
                    <p><?php echo $stats['upcoming_expiries']; ?></p>
                </div>
            </a>
        </div>

        <div class="sys-profile">
            <div class="sys-img">
                <img src="<?= !empty($system_profile['image']) ? "../uploads/system_profile/" . $system_profile['image'] : "../assets/images/no-image.png" ?>" alt="System Profile Image">
            </div>
            <h1 class="sys-name"><?= $system_profile['name'] ?></h1>
            <h3 class="sys-user"><?= $isAdmin ? "بەڕێوەبەر: " : "بەکارهێنەر: " ?> <span><?php echo $_SESSION['username'] ?></span></h3>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script src="../js/lib/jquery-3.7.1.min.js"></script>
    <script src="../js/scripts.js"></script>
</body>

</html>