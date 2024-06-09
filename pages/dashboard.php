<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<?php include('../includes/header.php'); ?>

<h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
<!-- Dashboard content here -->

<?php include('../includes/footer.php'); ?>
