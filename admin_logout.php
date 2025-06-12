<?php
include '../connection/config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['auth_user']['userid']) || $_SESSION['auth_user']['userid'] == 0) {
    header("Location: index.php");
    exit();
}

// Update online_offlineStatus and log the action
$userid = $_SESSION['auth_user']['userid'];
date_default_timezone_set('Asia/Manila');
$date = date('F / d l / Y');
$time = date('g:i A');
$logs = 'You successfully logged out to your account.';
$online_offline_status = 'Offline';

try {
    // Insert logout log into system_notification
    $sql = $conn->prepare("INSERT INTO system_notification (userid, logs, logs_date, logs_time) VALUES (?, ?, ?, ?)");
    $sql->execute([$userid, $logs, $date, $time]);

    // Update online_offlineStatus in admin_account
    $sql2 = $conn->prepare("UPDATE admin_account SET online_offlineStatus = ? WHERE id = ?");
    $sql2->execute([$online_offline_status, $userid]);
} catch (PDOException $e) {
    // Log error to file or handle silently (avoid exposing to user)
    error_log("Logout error: " . $e->getMessage(), 3, 'errors.log');
}

// Clear session
session_unset();
session_destroy();

// Redirect to login page
header("Location: ../pending/login.php");
exit();
?>