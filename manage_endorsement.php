<?php
include '../connection/config.php';
session_start();

if (!isset($_SESSION['auth_user']['userid']) || $_SESSION['auth_user']['userid'] == 0) {
    $_SESSION['status'] = "Unauthorized access.";
    $_SESSION['alert'] = "Error";
    $_SESSION['status-code'] = "error";
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'];
        if ($action === 'create') {
            $stmt = $conn->prepare("
                INSERT INTO endorsements (first_name, middle_name, last_name, section, date_submitted, created_by)
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
            $_SESSION['status'] = "Endorsement saved successfully.";
            $_SESSION['alert'] = "Success";
            $_SESSION['status-code'] = "success";
        } elseif ($action === 'update') {
            $stmt = $conn->prepare("
                UPDATE endorsements 
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
            $_SESSION['status'] = "Endorsement updated successfully.";
            $_SESSION['alert'] = "Success";
            $_SESSION['status-code'] = "success";
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM endorsements WHERE id = :id AND created_by = :created_by");
            $stmt->execute([
                'id' => $_POST['id'],
                'created_by' => $_SESSION['auth_user']['userid']
            ]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => 'Endorsement deleted successfully']);
            } else {
                echo json_encode(['error' => 'Endorsement not found or you don\'t have permission']);
            }
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        if ($action === 'delete') {
            echo json_encode(['error' => 'Failed to delete endorsement: ' . $e->getMessage()]);
            exit;
        } else {
            $_SESSION['status'] = "Failed to save endorsement: " . $e->getMessage();
            $_SESSION['alert'] = "Error";
            $_SESSION['status-code'] = "error";
        }
    }
}
header("Location: endorsement.php");
exit;
?>