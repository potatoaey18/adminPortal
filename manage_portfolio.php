<?php
include '../connection/config.php';
session_start();

if (!isset($_SESSION['auth_user']['userid']) || $_SESSION['auth_user']['userid'] == 0) {
    $_SESSION['status'] = "Unauthorized access.";
    $_SESSION['alert'] = "Error";
    $_SESSION['status_code'] = "error";
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        if ($action === 'create') {
            $stmt = $conn->prepare("
                INSERT INTO portfolios (first_name, middle_name, last_name, section, date_submitted, created_by)
                VALUES (:first_name, :middle_name, :last_name, :section, :date_submitted, :created_by)
            ");
            $stmt->execute([
                'first_name' => $_POST['first_name'],
                'middle_name' => $_POST['middle_name'] ?: null,
                'last_name' => $_POST['last_name'],
                'section' => $_POST['section'],
                'date_submitted' => $_POST['date_submitted'],
                'created_by' => $_SESSION['auth_user']['userid']
            ]);
            $_SESSION['status'] = "Portfolio created successfully.";
            $_SESSION['alert'] = "Success";
            $_SESSION['status_code'] = "success";
        } elseif ($action === 'update') {
            $stmt = $conn->prepare("
                UPDATE portfolios 
                SET first_name = :first_name, 
                    middle_name = :middle_name, 
                    last_name = :last_name, 
                    section = :section, 
                    date_submitted = :date_submitted
                WHERE id = :id AND created_by = :created_by
            ");
            $stmt->execute([
                'id' => $_POST['id'],
                'first_name' => $_POST['first_name'],
                'middle_name' => $_POST['middle_name'] ?: null,
                'last_name' => $_POST['last_name'],
                'section' => $_POST['section'],
                'date_submitted' => $_POST['date_submitted'],
                'created_by' => $_SESSION['auth_user']['userid']
            ]);
            $_SESSION['status'] = "Portfolio updated successfully.";
            $_SESSION['alert'] = "Success";
            $_SESSION['status_code'] = "success";
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM portfolios WHERE id = :id AND created_by = :created_by");
            $stmt->execute([
                'id' => $_POST['id'],
                'created_by' => $_SESSION['auth_user']['userid']
            ]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => 'Portfolio deleted successfully']);
            } else {
                echo json_encode(['error' => 'Portfolio not found or you don\'t have permission']);
            }
            exit;
        } else {
            throw new Exception("Invalid action.");
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        if ($action === 'delete') {
            echo json_encode(['error' => 'Failed to delete portfolio: ' . $e->getMessage()]);
            exit;
        }
        $_SESSION['status'] = "Failed to process portfolio: " . $e->getMessage();
        $_SESSION['alert'] = "Error";
        $_SESSION['status_code'] = "error";
    }
}
header("Location: portfolio.php");
exit;
?>