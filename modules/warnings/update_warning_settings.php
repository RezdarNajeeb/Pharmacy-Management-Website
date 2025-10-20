<?php
session_start();
require_once '../../includes/db.php';
require_once '../utilities/log_user_activity.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $warning_quantity = $_POST['warning_quantity'];
    $warning_expiry_days = $_POST['warning_expiry_days'];

    if ($conn->query('SELECT * FROM warning_settings WHERE id=1')->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO warning_settings (id, warning_quantity, warning_expiry_days) VALUES (1, ?, ?)");
    } else {
        $stmt = $conn->prepare("UPDATE warning_settings SET warning_quantity=?, warning_expiry_days=? WHERE id=1");
    }
    $stmt->bind_param("ii", $warning_quantity, $warning_expiry_days);

    if ($stmt->execute()) {
        logUserActivity("ئاگادارکردنەوەکانی نوێکردەوە.");

        $_SESSION['messages'][] = ["type" => "success", "message" => " ئاگادارکردنەوەکان بە سەرکەوتوویی نوێکرایەوە"];
    } else {
        $_SESSION['messages'][] = ["type" => "error", "message" => "هەڵەیەک ڕوویدا"];
    }

    $stmt->close();
} else {
    $_SESSION['messages'][] = ["type" => "error", "message" => "تکایە دووبارە هەوڵ بدەوە"];
}

$conn->close();
