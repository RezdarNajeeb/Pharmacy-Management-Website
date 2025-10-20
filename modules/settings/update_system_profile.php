<?php
session_start();
require_once '../../includes/db.php';
require_once '../utilities/log_user_activity.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// update system profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $system_name = $_POST['name'];
    $existing_image = $_POST['existing-image'];
    $edit_image = isset($_FILES['edit-image']) ? $_FILES['edit-image'] : null;

    // Check if a new image is selected and valid
    if ($edit_image && $edit_image['name']) {
        $image_name = $edit_image['name'];

        // Delete the old image file if it exists
        if (file_exists("../../uploads/system_profile/".$existing_image)) {
            unlink("../../uploads/system_profile/".$existing_image);
        }

        // Move the new image to the uploads directory
        move_uploaded_file($edit_image['tmp_name'], "../../uploads/system_profile/".$image_name);
    } else {
        $image_name = $existing_image;
    }

    if ($conn->query('SELECT * FROM system_profile WHERE id=1')->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO system_profile (id, name, image) VALUES (1, ?, ?)");
    } else {
        $stmt = $conn->prepare("UPDATE system_profile SET name=?, image=? WHERE id=1");
    }

    $stmt->bind_param("ss", $system_name, $image_name);

    if ($stmt->execute()) {
        // log the activity
        logUserActivity("ناوی پڕۆفایلی سیستەمەکەی کرد بە $system_name.");

        $_SESSION['messages'][] = ["type" => "success", "message" => "پڕۆفایلی سیستەمەکە بە سەرکەوتوویی نوێکرایەوە"];
    } else {
        $_SESSION['messages'][] = ["type" => "error", "message" => "هەڵەیەک ڕوویدا"];
    }

    $stmt->close();
} else {
    $_SESSION['messages'][] = ["type" => "error", "message" => "تکایە دووبارە هەوڵ بدەوە"];
}

$conn->close();
