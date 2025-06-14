<?php
include '../connection/config.php';
session_start();

$adminID = $_SESSION['auth_user']['admin_id'] ?? null;
if (!$adminID) {
    header('Location: index.php');
    exit;
}

// Handle course, section, and adviser addition/editing
if (isset($_POST['saveCourseSection'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $course = filter_input(INPUT_POST, 'course', FILTER_SANITIZE_STRING);
    $section = filter_input(INPUT_POST, 'section', FILTER_SANITIZE_STRING);
    $adviser_id = filter_input(INPUT_POST, 'adviser_id', FILTER_SANITIZE_NUMBER_INT);

    error_log("Save request: ID=$id, Course=$course, Section=$section, Adviser=$adviser_id");

    if (empty($course) || empty($section)) {
        error_log("Save error: Course or section missing.");
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "Course and section are required.";
    } else {
        try {
            $conn->beginTransaction();

            // Check for duplicate course-section combination
            $sql = $id ? 
                "SELECT COUNT(*) FROM courses_sections WHERE course = ? AND section = ? AND id != ?" : 
                "SELECT COUNT(*) FROM courses_sections WHERE course = ? AND section = ?";
            $stmt = $conn->prepare($sql);
            $params = $id ? [$course, $section, $id] : [$course, $section];
            $stmt->execute($params);
            
            if ($stmt->fetchColumn() > 0) {
                error_log("Save error: Duplicate course-section combination.");
                throw new Exception("Course and section already exist.");
            }

            if ($id) {
                // Update existing record
                $stmt = $conn->prepare("UPDATE courses_sections SET course = ?, section = ? WHERE id = ?");
                $stmt->execute([$course, $section, $id]);
                $logs = "Edited course $course with section $section.";
            } else {
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO courses_sections (course, section) VALUES (?, ?)");
                $stmt->execute([$course, $section]);
                $id = $conn->lastInsertId();
                $logs = "Added course $course with section $section.";
            }

            // Handle adviser assignment if provided
            if ($adviser_id) {
                // Check adviser's current assignments
                $stmt = $conn->prepare("
                    SELECT assigned_section, second_assigned_section 
                    FROM coordinators_account 
                    WHERE id = ?
                ");
                $stmt->execute([$adviser_id]);
                $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$coordinator) {
                    throw new Exception("Adviser not found.");
                }

                $assignedCount = 0;
                if (!empty($coordinator['assigned_section'])) $assignedCount++;
                if (!empty($coordinator['second_assigned_section'])) $assignedCount++;
                $isAssignedToThisSection = in_array($section, array_filter([
                    $coordinator['assigned_section'],
                    $coordinator['second_assigned_section']
                ]));

                if ($assignedCount >= 2 && !$isAssignedToThisSection) {
                    throw new Exception("Adviser is already assigned to two sections.");
                }

                // Update section_advisers
                $stmt = $conn->prepare("SELECT COUNT(*) FROM section_advisers WHERE section = ?");
                $stmt->execute([$section]);
                $exists = $stmt->fetchColumn();

                if ($exists) {
                    $stmt = $conn->prepare("UPDATE section_advisers SET adviser_id = ? WHERE section = ?");
                    $stmt->execute([$adviser_id, $section]);
                } else {
                    $stmt = $conn->prepare("INSERT INTO section_advisers (section, adviser_id) VALUES (?, ?)");
                    $stmt->execute([$section, $adviser_id]);
                }

                // Update coordinators_account
                if ($coordinator['assigned_section'] == $section) {
                    $stmt = $conn->prepare("UPDATE coordinators_account SET assigned_section = NULL WHERE id = ?");
                    $stmt->execute([$adviser_id]);
                } elseif ($coordinator['second_assigned_section'] == $section) {
                    $stmt = $conn->prepare("UPDATE coordinators_account SET second_assigned_section = NULL WHERE id = ?");
                    $stmt->execute([$adviser_id]);
                }

                if (empty($coordinator['assigned_section'])) {
                    $stmt = $conn->prepare("UPDATE coordinators_account SET assigned_section = ? WHERE id = ?");
                    $stmt->execute([$section, $adviser_id]);
                } elseif (empty($coordinator['second_assigned_section'])) {
                    $stmt = $conn->prepare("UPDATE coordinators_account SET second_assigned_section = ? WHERE id = ?");
                    $stmt->execute([$section, $adviser_id]);
                }

                $logs .= " Assigned adviser ID $adviser_id to section $section.";
            }

            // Log the action
            date_default_timezone_set('Asia/Manila');
            $date = date('F / d l / Y');
            $time = date('g:i A');
            $stmt = $conn->prepare("INSERT INTO admin_notification(admin_id, logs, logs_date, logs_time) VALUES (?, ?, ?, ?)");
            $stmt->execute([$adminID, $logs, $date, $time]);

            $conn->commit();
            $_SESSION['alert'] = "Success";
            $_SESSION['status'] = $id ? "Course, section, and adviser updated successfully." : "Course, section, and adviser added successfully.";
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Save error: " . $e->getMessage());
            $_SESSION['alert'] = "Error";
            $_SESSION['status'] = $e->getMessage();
        }
    }
    header('Location: assign_advisers.php');
    exit;
}

// Handle AJAX adviser assignment
if (isset($_POST['saveAdviserAssignment'])) {
    header('Content-Type: application/json');
    $section = filter_input(INPUT_POST, 'section', FILTER_SANITIZE_STRING);
    $adviser_id = filter_input(INPUT_POST, 'adviser_id', FILTER_SANITIZE_NUMBER_INT);

    if (empty($section) || empty($adviser_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Section and adviser are required.']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // Check adviser's current assignments
        $stmt = $conn->prepare("
            SELECT assigned_section, second_assigned_section 
            FROM coordinators_account 
            WHERE id = ?
        ");
        $stmt->execute([$adviser_id]);
        $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$coordinator) {
            throw new Exception("Adviser not found.");
        }

        $assignedCount = 0;
        if (!empty($coordinator['assigned_section'])) $assignedCount++;
        if (!empty($coordinator['second_assigned_section'])) $assignedCount++;
        $isAssignedToThisSection = in_array($section, array_filter([
            $coordinator['assigned_section'],
            $coordinator['second_assigned_section']
        ]));

        if ($assignedCount >= 2 && !$isAssignedToThisSection) {
            throw new Exception("Adviser is already assigned to two sections.");
        }

        // Update section_advisers
        $stmt = $conn->prepare("SELECT COUNT(*) FROM section_advisers WHERE section = ?");
        $stmt->execute([$section]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $stmt = $conn->prepare("UPDATE section_advisers SET adviser_id = ? WHERE section = ?");
            $stmt->execute([$adviser_id, $section]);
        } else {
            $stmt = $conn->prepare("INSERT INTO section_advisers (section, adviser_id) VALUES (?, ?)");
            $stmt->execute([$section, $adviser_id]);
        }

        // Update coordinators_account
        if ($coordinator['assigned_section'] == $section) {
            $stmt = $conn->prepare("UPDATE coordinators_account SET assigned_section = NULL WHERE id = ?");
            $stmt->execute([$adviser_id]);
        } elseif ($coordinator['second_assigned_section'] == $section) {
            $stmt = $conn->prepare("UPDATE coordinators_account SET second_assigned_section = NULL WHERE id = ?");
            $stmt->execute([$adviser_id]);
        }

        if (empty($coordinator['assigned_section'])) {
            $stmt = $conn->prepare("UPDATE coordinators_account SET assigned_section = ? WHERE id = ?");
            $stmt->execute([$section, $adviser_id]);
        } elseif (empty($coordinator['second_assigned_section'])) {
            $stmt = $conn->prepare("UPDATE coordinators_account SET second_assigned_section = ? WHERE id = ?");
            $stmt->execute([$section, $adviser_id]);
        }

        // Log the action
        date_default_timezone_set('Asia/Manila');
        $date = date('F / d l / Y');
        $time = date('g:i A');
        $logs = "Assigned adviser ID $adviser_id to section $section.";
        $stmt = $conn->prepare("INSERT INTO admin_notification(admin_id, logs, logs_date, logs_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$adminID, $logs, $date, $time]);

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Adviser assigned successfully.']);
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("AJAX Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle deletion
if (isset($_POST['deleteCourseSection'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

    if (empty($id)) {
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "Section ID is required.";
    } else {
        try {
            $conn->beginTransaction();

            // Get course and section for logging
            $stmt = $conn->prepare("SELECT course, section FROM courses_sections WHERE id = ?");
            $stmt->execute([$id]);
            $sectionData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$sectionData) {
                throw new Exception("Section not found.");
            }
            $course = $sectionData['course'];
            $section = $sectionData['section'];

            // Check if section has students
            $stmt = $conn->prepare("SELECT COUNT(*) FROM students_data WHERE stud_course = ? AND stud_section = ?");
            $stmt->execute([$course, $section]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Cannot delete section with enrolled students.");
            }

            // Remove from section_advisers
            $stmt = $conn->prepare("DELETE FROM section_advisers WHERE section = ?");
            $stmt->execute([$section]);

            // Update coordinators_account
            $stmt = $conn->prepare("UPDATE coordinators_account SET assigned_section = NULL WHERE assigned_section = ?");
            $stmt->execute([$section]);
            $stmt = $conn->prepare("UPDATE coordinators_account SET second_assigned_section = NULL WHERE second_assigned_section = ?");
            $stmt->execute([$section]);

            // Delete from courses_sections
            $stmt = $conn->prepare("DELETE FROM courses_sections WHERE id = ?");
            $stmt->execute([$id]);

            // Log the action
            date_default_timezone_set('Asia/Manila');
            $date = date('F / d l / Y');
            $time = date('g:i A');
            $logs = "Deleted course $course with section $section.";
            $stmt = $conn->prepare("INSERT INTO admin_notification(admin_id, logs, logs_date, logs_time) VALUES (?, ?, ?, ?)");
            $stmt->execute([$adminID, $logs, $date, $time]);

            $conn->commit();
            $_SESSION['alert'] = "Success";
            $_SESSION['status'] = "Course and section deleted successfully.";
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Delete error: " . $e->getMessage());
            $_SESSION['alert'] = "Error";
            $_SESSION['status'] = $e->getMessage();
        }
    }
    header('Location: assign_advisers.php');
    exit;
}
?>