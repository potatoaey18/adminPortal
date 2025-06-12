<?php
include '../connection/config.php';
session_start();

//display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (isset($_SESSION['auth_user']['userid'])) {
    $userid = $_SESSION['auth_user']['userid'];

    $read = 'Read';
    // Adjust your SQL query to update notifications as read based on your database schema
    $stmt = $conn->prepare("UPDATE admin_system_notification SET status = ? WHERE userid = ?");
    $stmt->execute([$read, $userid]);

    // Respond to the AJAX request with a JSON response
    $response = array("success" => true);
    echo json_encode($response);
    exit;
}
?>
