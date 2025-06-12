<?php
include '../connection/config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['auth_user']['userid']) || $_SESSION['auth_user']['userid'] == 0) {
    $_SESSION['status'] = "Unauthorized access.";
    $_SESSION['alert'] = "Error";
    $_SESSION['status-code'] = "error";
    header("Location: index.php");
    exit;
}

$userid = $_SESSION['auth_user']['userid'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'create_announcement') {
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
        $portal = filter_input(INPUT_POST, 'portal', FILTER_SANITIZE_STRING);
        if ($title && $content && in_array($portal, ['Student', 'Adviser', 'HTE', 'All'])) {
            $stmt = $conn->prepare("INSERT INTO announcements (title, content, portal, created_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $content, $portal, $userid]);
            $_SESSION['status'] = "Announcement created successfully!";
            $_SESSION['alert'] = "Success";
            $_SESSION['status-code'] = "success";
        } else {
            $_SESSION['status'] = "Please fill in all fields correctly.";
            $_SESSION['alert'] = "Error";
            $_SESSION['status-code'] = "error";
        }
    } elseif ($action === 'update_announcement') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
        $portal = filter_input(INPUT_POST, 'portal', FILTER_SANITIZE_STRING);
        if ($id && $title && $content && in_array($portal, ['Student', 'Adviser', 'HTE', 'All'])) {
            $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, portal = ? WHERE id = ? AND created_by = ?");
            $stmt->execute([$title, $content, $portal, $id, $userid]);
            $_SESSION['status'] = "Announcement updated successfully!";
            $_SESSION['alert'] = "Success";
            $_SESSION['status-code'] = "success";
        } else {
            $_SESSION['status'] = "Please fill in all fields correctly.";
            $_SESSION['alert'] = "Error";
            $_SESSION['status-code'] = "error";
        }
    } elseif ($action === 'delete_announcement') {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ? AND (portal = 'Student' OR portal = 'Adviser' OR portal = 'HTE' OR portal = 'All')");
            $stmt->execute([$id]);
            $_SESSION['status'] = "Announcement deleted successfully!";
            $_SESSION['alert'] = "Success";
            $_SESSION['status-code'] = "success";
        } else {
            $_SESSION['status'] = "Invalid announcement ID.";
            $_SESSION['alert'] = "Error";
            $_SESSION['status-code'] = "error";
        }
    } elseif ($action === 'create_faq') {
        $question = filter_input(INPUT_POST, 'question', FILTER_SANITIZE_STRING);
        $answer = filter_input(INPUT_POST, 'answer', FILTER_SANITIZE_STRING);
        $portal = filter_input(INPUT_POST, 'portal', FILTER_SANITIZE_STRING);
        if ($question && $answer && in_array($portal, ['Student', 'Adviser', 'HTE', 'All'])) {
            $stmt = $conn->prepare("INSERT INTO faqs (question, answer, portal, created_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$question, $answer, $portal, $userid]);
            $_SESSION['status'] = "FAQ created successfully!";
            $_SESSION['alert'] = "Success";
            $_SESSION['status-code'] = "success";
        } else {
            $_SESSION['status'] = "Please fill in all fields correctly.";
            $_SESSION['alert'] = "Error";
            $_SESSION['status-code'] = "error";
        }
    } elseif ($action === 'update_faq') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $question = filter_input(INPUT_POST, 'question', FILTER_SANITIZE_STRING);
        $answer = filter_input(INPUT_POST, 'answer', FILTER_SANITIZE_STRING);
        $portal = filter_input(INPUT_POST, 'portal', FILTER_SANITIZE_STRING);
        if ($id && $question && $answer && in_array($portal, ['Student', 'Adviser', 'HTE', 'All'])) {
            $stmt = $conn->prepare("UPDATE faqs SET question = ?, answer = ?, portal = ? WHERE id = ? AND created_by = ?");
            $stmt->execute([$question, $answer, $portal, $id, $userid]);
            $_SESSION['status'] = "FAQ updated successfully!";
            $_SESSION['alert'] = "Success";
            $_SESSION['status-code'] = "success";
        } else {
            $_SESSION['status'] = "Please fill in all fields correctly.";
            $_SESSION['alert'] = "Error";
            $_SESSION['status-code'] = "error";
        }
    } elseif ($action === 'delete_faq') {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ? AND (portal = 'Student' OR portal = 'Adviser' OR portal = 'HTE' OR portal = 'All')");
            $stmt->execute([$id]);
            $_SESSION['status'] = "FAQ deleted successfully!";
            $_SESSION['alert'] = "Success";
            $_SESSION['status-code'] = "success";
        } else {
            $_SESSION['status'] = "Invalid FAQ ID.";
            $_SESSION['alert'] = "Error";
            $_SESSION['status-code'] = "error";
        }
    } else {
        $_SESSION['status'] = "Invalid action.";
        $_SESSION['alert'] = "Error";
        $_SESSION['status-code'] = "error";
    }
} catch (PDOException $e) {
    $_SESSION['status'] = "Database error: " . $e->getMessage();
    $_SESSION['alert'] = "Error";
    $_SESSION['status-code'] = "error";
}

header("Location: dashboard.php");
exit;
?>