<?php
session_start();
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id = trim($_POST['id']);

        // Validate input
        if (empty($id)) {
            header("Location: dashboard.php?status=error");
            exit();
        }

        // Prepare and execute the delete query
        $query = "DELETE FROM announcements WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['status'] = "Announcement deleted successfully.";
            $_SESSION['alert'] = "Success!";
            $_SESSION['status-code'] = "success";
            header("Location: dashboard.php?status=success");
        } else {
            header("Location: dashboard.php?status=error");
        }
    } else {
        header("Location: dashboard.php?status=error");
    }
} catch (PDOException $e) {
    error_log("Error deleting announcement: " . $e->getMessage());
    header("Location: dashboard.php?status=error");
}
?>