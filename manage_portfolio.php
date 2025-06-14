<?php
include '../connection/config.php';
session_start();

if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
    $_SESSION['status'] = "Unauthorized access.";
    $_SESSION['alert'] = "Error";
    $_SESSION['status-code'] = "error";
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        $admin_id = $_SESSION['auth_user']['admin_id'];

        if ($action === 'create') {
            $student_id = filter_var($_POST['student_id'] ?? '', FILTER_VALIDATE_INT);
            $date_submitted = $_POST['date_submitted'] ?? '';

            error_log("Create portfolio: student_id=$student_id, date_submitted=$date_submitted");

            if (!$student_id || empty($date_submitted)) {
                $_SESSION['status'] = "Invalid student ID or missing required fields.";
                $_SESSION['alert'] = "Error";
                $_SESSION['status-code'] = "error";
                header("Location: portfolio_form.php");
                exit;
            }

            $stmt = $conn->prepare("
                SELECT first_name, middle_name, last_name, stud_section 
                FROM students_data 
                WHERE id = :student_id
            ");
            $stmt->execute(['student_id' => $student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                error_log("Invalid student_id: $student_id");
                $_SESSION['status'] = "Selected student not found.";
                $_SESSION['alert'] = "Error";
                $_SESSION['status-code'] = "error";
                header("Location: portfolio_form.php");
                exit;
            }

            $stmt = $conn->prepare("
                INSERT INTO portfolios (student_id, first_name, middle_name, last_name, section, date_submitted, created_by)
                VALUES (:student_id, :first_name, :middle_name, :last_name, :section, :date_submitted, :created_by)
            ");
            $stmt->execute([
                'student_id' => $student_id,
                'first_name' => $student['first_name'],
                'middle_name' => $student['middle_name'] ?: null,
                'last_name' => $student['last_name'],
                'section' => $student['stud_section'],
                'date_submitted' => $date_submitted,
                'created_by' => $admin_id
            ]);

            $_SESSION['status'] = "Portfolio saved successfully.";
            $_SESSION['alert'] = "Success";
            $_SESSION['status-code'] = "success";
            header("Location: portfolio.php");
            exit;

        } elseif ($action === 'update') {
            $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
            $student_id = filter_var($_POST['student_id'] ?? '', FILTER_VALIDATE_INT);
            $date_submitted = $_POST['date_submitted'] ?? '';

            error_log("Update portfolio: id=$id, student_id=$student_id, date_submitted=$date_submitted");

            if (!$id || !$student_id || empty($date_submitted)) {
                $_SESSION['status'] = "Invalid portfolio ID, student ID, or missing required fields.";
                $_SESSION['alert'] = "Error";
                $_SESSION['status-code'] = "error";
                header("Location: portfolio_form.php?id=$id");
                exit;
            }

            $stmt = $conn->prepare("
                SELECT first_name, middle_name, last_name, stud_section 
                FROM students_data 
                WHERE id = :student_id
            ");
            $stmt->execute(['student_id' => $student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                error_log("Invalid student_id for update: $student_id");
                $_SESSION['status'] = "Selected student not found.";
                $_SESSION['alert'] = "Error";
                $_SESSION['status-code'] = "error";
                header("Location: portfolio_form.php?id=$id");
                exit;
            }

            $stmt = $conn->prepare("
                UPDATE portfolios 
                SET student_id = :student_id, 
                    first_name = :first_name, 
                    middle_name = :middle_name, 
                    last_name = :last_name, 
                    section = :section, 
                    date_submitted = :date_submitted
                WHERE id = :id AND created_by = :created_by
            ");
            $stmt->execute([
                'student_id' => $student_id,
                'first_name' => $student['first_name'],
                'middle_name' => $student['middle_name'] ?: null,
                'last_name' => $student['last_name'],
                'section' => $student['stud_section'],
                'date_submitted' => $date_submitted,
                'id' => $id,
                'created_by' => $admin_id
            ]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['status'] = "Portfolio updated successfully.";
                $_SESSION['alert'] = "Success";
                $_SESSION['status-code'] = "success";
            } else {
                $_SESSION['status'] = "No changes made or you don't have permission to update this portfolio.";
                $_SESSION['alert'] = "Error";
                $_SESSION['status-code'] = "error";
            }
            header("Location: portfolio.php");
            exit;

        } else {
            $_SESSION['status'] = "Invalid action.";
            $_SESSION['alert'] = "Error";
            $_SESSION['status-code'] = "error";
            header("Location: portfolio.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error in manage_portfolio.php: " . $e->getMessage());
        $_SESSION['status'] = "Failed to save portfolio: " . $e->getMessage();
        $_SESSION['alert'] = "Error";
        $_SESSION['status-code'] = "error";
        header("Location: portfolio_form.php" . ($action === 'update' ? "?id=" . ($_POST['id'] ?? '') : ""));
        exit;
    }
}

header("Location: portfolio.php");
exit;
?>