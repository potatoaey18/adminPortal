<?php
session_start();
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id = trim($_POST['id']);
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);
        $category = trim($_POST['category']);

        // Validate input
        if (empty($id) || empty($question) || empty($answer) || empty($category)) {
            header("Location: dashboard.php?status=error");
            exit();
        }

        // Validate category
        $valid_categories = ['student', 'admin', 'supervisor'];
        if (!in_array($category, $valid_categories)) {
            header("Location: dashboard.php?status=error");
            exit();
        }

        // Prepare and execute the update query
        $query = "UPDATE faqs SET question = :question, answer = :answer, category = :category WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':question', $question, PDO::PARAM_STR);
        $stmt->bindParam(':answer', $answer, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['status'] = "FAQ updated successfully.";
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
    error_log("Error updating FAQ: " . $e->getMessage());
    header("Location: dashboard.php?status=error");
}
?>