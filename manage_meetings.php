<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
error_log("Reached manage_meetings.php");

if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
    error_log("Session auth_user[admin_id] not set, redirecting to index.php");
    $_SESSION['status'] = "Unauthorized access.";
    $_SESSION['alert'] = "Error";
    $_SESSION['status-code'] = "error";
    header("Location: index.php");
    exit;
}

$admin_id = (int)$_SESSION['auth_user']['admin_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$transactionActive = false;

try {
    if ($action === 'create_meeting') {
        $conn->beginTransaction();
        $transactionActive = true;

        $meeting_type = filter_input(INPUT_POST, 'meeting_type', FILTER_SANITIZE_STRING) ?: 'Zoom Meeting';
        $link = filter_input(INPUT_POST, 'link', FILTER_SANITIZE_URL);
        $passcode = filter_input(INPUT_POST, 'passcode', FILTER_SANITIZE_STRING);
        $meeting_date = filter_input(INPUT_POST, 'meeting_date', FILTER_SANITIZE_STRING);
        $meeting_time = filter_input(INPUT_POST, 'meeting_time', FILTER_SANITIZE_STRING);
        $agenda = filter_input(INPUT_POST, 'agenda', FILTER_SANITIZE_STRING);
        $portal = filter_input(INPUT_POST, 'portal', FILTER_SANITIZE_STRING);

        // Validate inputs
        if (!$link || strlen($link) > 255) {
            throw new Exception("Invalid or too long meeting link.");
        }
        if ($passcode && strlen($passcode) > 50) {
            throw new Exception("Passcode too long.");
        }
        if (!$meeting_date || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $meeting_date)) {
            throw new Exception("Invalid meeting date format.");
        }
        // Check if meeting date is not before today
        if (strtotime($meeting_date) < strtotime(date('Y-m-d'))) {
            throw new Exception("Meeting date cannot be before today.");
        }
        if (!$meeting_time || strlen($meeting_time) > 20) {
            throw new Exception("Invalid or too long meeting time.");
        }
        if (!$agenda) {
            throw new Exception("Agenda is required.");
        }
        if (!in_array($portal, ['student', 'faculty', 'hte', 'all'])) {
            throw new Exception("Invalid portal selected.");
        }

        // Insert into meetings table
        $stmt = $conn->prepare("INSERT INTO meetings (created_by, meeting_type, link, passcode, meeting_date, meeting_time, agenda, portal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$admin_id, $meeting_type, $link, $passcode ?: null, $meeting_date, $meeting_time, $agenda, $portal]);
        $conn->commit();
        $transactionActive = false;
        $_SESSION['status'] = "Meeting created successfully!";
        $_SESSION['alert'] = "Success";
        $_SESSION['status-code'] = "success";
    } elseif ($action === 'update_meeting') {
        $conn->beginTransaction();
        $transactionActive = true;

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $meeting_type = filter_input(INPUT_POST, 'meeting_type', FILTER_SANITIZE_STRING) ?: 'Zoom Meeting';
        $link = filter_input(INPUT_POST, 'link', FILTER_SANITIZE_URL);
        $passcode = filter_input(INPUT_POST, 'passcode', FILTER_SANITIZE_STRING);
        $meeting_date = filter_input(INPUT_POST, 'meeting_date', FILTER_SANITIZE_STRING);
        $meeting_time = filter_input(INPUT_POST, 'meeting_time', FILTER_SANITIZE_STRING);
        $agenda = filter_input(INPUT_POST, 'agenda', FILTER_SANITIZE_STRING);
        $portal = filter_input(INPUT_POST, 'portal', FILTER_SANITIZE_STRING);

        // Validate inputs
        if (!$id) {
            throw new Exception("Invalid meeting ID.");
        }
        if (!$link || strlen($link) > 255) {
            throw new Exception("Invalid or too long meeting link.");
        }
        if ($passcode && strlen($passcode) > 50) {
            throw new Exception("Passcode too long.");
        }
        if (!$meeting_date || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $meeting_date)) {
            throw new Exception("Invalid meeting date format.");
        }
        // Check if meeting date is not before today
        if (strtotime($meeting_date) < strtotime(date('Y-m-d'))) {
            throw new Exception("Meeting date cannot be before today.");
        }
        if (!$meeting_time || strlen($meeting_time) > 20) {
            throw new Exception("Invalid or too long meeting time.");
        }
        if (!$agenda) {
            throw new Exception("Agenda is required.");
        }
        if (!in_array($portal, ['student', 'faculty', 'hte', 'all'])) {
            throw new Exception("Invalid portal selected.");
        }

        // Update meetings table
        $stmt = $conn->prepare("UPDATE meetings SET meeting_type = ?, link = ?, passcode = ?, meeting_date = ?, meeting_time = ?, agenda = ?, portal = ? WHERE id = ? AND created_by = ?");
        $stmt->execute([$meeting_type, $link, $passcode ?: null, $meeting_date, $meeting_time, $agenda, $portal, $id, $admin_id]);
        $conn->commit();
        $transactionActive = false;
        $_SESSION['status'] = "Meeting updated successfully!";
        $_SESSION['alert'] = "Success";
        $_SESSION['status-code'] = "success";
    } elseif ($action === 'delete_meeting') {
        $conn->beginTransaction();
        $transactionActive = true;

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            throw new Exception("Invalid meeting ID.");
        }

        // Verify meeting exists and belongs to user
        $stmt = $conn->prepare("SELECT id FROM meetings WHERE id = ? AND created_by = ?");
        $stmt->execute([$id, $admin_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Meeting not found or you don't have permission to delete it.");
        }

        // Delete meeting
        $stmt = $conn->prepare("DELETE FROM meetings WHERE id = ? AND created_by = ?");
        $stmt->execute([$id, $admin_id]);
        $conn->commit();
        $transactionActive = false;
        $_SESSION['status'] = "Meeting deleted successfully!";
        $_SESSION['alert'] = "Success";
        $_SESSION['status-code'] = "success";
    } else {
        throw new Exception("Invalid action.");
    }
} catch (PDOException $e) {
    if ($transactionActive) {
        $conn->rollBack();
    }
    error_log("Database error in manage_meetings.php: " . $e->getMessage());
    $_SESSION['status'] = "Database error: " . htmlspecialchars($e->getMessage());
    $_SESSION['alert'] = "Error";
    $_SESSION['status-code'] = "error";
} catch (Exception $e) {
    if ($transactionActive) {
        $conn->rollBack();
    }
    error_log("Error in manage_meetings.php: " . $e->getMessage());
    $_SESSION['status'] = "Error: " . htmlspecialchars($e->getMessage());
    $_SESSION['alert'] = "Error";
    $_SESSION['status-code'] = "error";
}

header("Location: appointment_meetings.php");
exit;
?>