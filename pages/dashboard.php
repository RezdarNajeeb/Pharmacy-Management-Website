<?php
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
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../assets/fontawesome-free-6.5.2-web/css/all.min.css">
</head>

<body>
    <?php include('../includes/header.php'); ?>

    <?php include('../includes/footer.php'); ?>
</body>

</html>