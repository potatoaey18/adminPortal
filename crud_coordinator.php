<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

function validateInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateUniqueId() {
    return md5(uniqid(rand(), true));
}

function checkDuplicates($conn, $faculty_id, $email, $exclude_id = null) {
    try {
        $query = "SELECT id FROM coordinators_account WHERE (faculty_id = ? OR coordinators_email = ?)";
        if ($exclude_id) {
            $query .= " AND id != ?";
        }
        $stmt = $conn->prepare($query);
        $params = $exclude_id ? [$faculty_id, $email, $exclude_id] : [$faculty_id, $email];
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Duplicate check error: " . $e->getMessage());
        return false;
    }
}

try {
    if ($action == 'create') {
        $first_name = validateInput($_POST['firstName'] ?? '');
        $middle_name = validateInput($_POST['middleName'] ?? ''); // Will use default if empty
        $last_name = validateInput($_POST['lastName'] ?? '');
        $faculty_id = validateInput($_POST['facultyId'] ?? '');
        $coor_dept = validateInput($_POST['coorDept'] ?? '');
        $course_handled = validateInput($_POST['courseHandled'] ?? '');
        $assigned_section = validateInput($_POST['assignedSection'] ?? ''); // Will use default if empty
        $second_assigned_section = validateInput($_POST['secondAssignedSection'] ?? ''); // Will use default if empty
        $phone_number = validateInput($_POST['phoneNumber'] ?? '');
        $coordinators_email = validateInput($_POST['coordinatorsEmail'] ?? '');
        $complete_address = validateInput($_POST['completeAddress'] ?? '');
        $coordinators_password = password_hash('default_password', PASSWORD_DEFAULT); // Default password
        $coordinators_profile_picture = 'default.jpg'; // Default profile picture
        $verification_code = random_int(10000000, 99999999); // Random 8-digit code
        $verify_status = 'Not Verified'; // Default from table
        $online_offlineStatus = 'Offline'; // Default from table
        $active_student = 0; // Default
        $access_level = 2; // Default from table
        $unique_id = generateUniqueId();

        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($faculty_id) || empty($coor_dept) || 
            empty($course_handled) || empty($phone_number) || empty($coordinators_email) || empty($complete_address)) {
            echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
            exit;
        }

        // Validate email format
        if (!filter_var($coordinators_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
            exit;
        }

        // Validate phone number format (10-13 digits)
        if (!preg_match('/^\d{10,13}$/', $phone_number)) {
            echo json_encode(['success' => false, 'message' => 'Phone number must be 10-13 digits.']);
            exit;
        }

        // Check for duplicates
        if (checkDuplicates($conn, $faculty_id, $coordinators_email)) {
            echo json_encode(['success' => false, 'message' => 'Faculty ID or Email already exists.']);
            exit;
        }

        // Insert new coordinator
        $stmt = $conn->prepare("
            INSERT INTO coordinators_account (
                uniqueID, first_name, middle_name, last_name, faculty_id, coor_dept, 
                course_handled, assigned_section, second_assigned_section, 
                phone_number, coordinators_email, coordinators_password, 
                coordinators_profile_picture, verification_code, verify_status, 
                online_offlineStatus, active_student, access_level, complete_address
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $unique_id, $first_name, $middle_name ?: '', $last_name, $faculty_id, $coor_dept,
            $course_handled, $assigned_section ?: '', $second_assigned_section ?: '',
            $phone_number, $coordinators_email, $coordinators_password,
            $coordinators_profile_picture, $verification_code, $verify_status,
            $online_offlineStatus, $active_student, $access_level, $complete_address
        ]);

        echo json_encode(['success' => true, 'message' => 'Coordinator added successfully.']);
    } elseif ($action == 'update') {
        $id = filter_input(INPUT_POST, 'coordinatorId', FILTER_VALIDATE_INT);
        $first_name = validateInput($_POST['firstName'] ?? '');
        $middle_name = validateInput($_POST['middleName'] ?? '');
        $last_name = validateInput($_POST['lastName'] ?? '');
        $faculty_id = validateInput($_POST['facultyId'] ?? '');
        $coor_dept = validateInput($_POST['coorDept'] ?? '');
        $course_handled = validateInput($_POST['courseHandled'] ?? '');
        $assigned_section = validateInput($_POST['assignedSection'] ?? '');
        $second_assigned_section = validateInput($_POST['secondAssignedSection'] ?? '');
        $phone_number = validateInput($_POST['phoneNumber'] ?? '');
        $coordinators_email = validateInput($_POST['coordinatorsEmail'] ?? '');
        $complete_address = validateInput($_POST['completeAddress'] ?? '');

        // Validate required fields
        if (empty($id) || empty($first_name) || empty($last_name) || empty($faculty_id) || empty($coor_dept) || 
            empty($course_handled) || empty($phone_number) || empty($coordinators_email) || empty($complete_address)) {
            echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
            exit;
        }

        // Validate email format
        if (!filter_var($coordinators_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
            exit;
        }

        // Validate phone number format (10-13 digits)
        if (!preg_match('/^\d{10,13}$/', $phone_number)) {
            echo json_encode(['success' => false, 'message' => 'Phone number must be 10-13 digits.']);
            exit;
        }

        // Check for duplicates, excluding current coordinator
        if (checkDuplicates($conn, $faculty_id, $coordinators_email, $id)) {
            echo json_encode(['success' => false, 'message' => 'Faculty ID or Email already exists.']);
            exit;
        }

        // Update coordinator
        $stmt = $conn->prepare("
            UPDATE coordinators_account 
            SET 
                first_name = ?, middle_name = ?, last_name = ?, faculty_id = ?, coor_dept = ?,
                course_handled = ?, assigned_section = ?, second_assigned_section = ?,
                phone_number = ?, coordinators_email = ?, complete_address = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $first_name, $middle_name ?: '', $last_name, $faculty_id, $coor_dept,
            $course_handled, $assigned_section ?: '', $second_assigned_section ?: '',
            $phone_number, $coordinators_email, $complete_address, $id
        ]);

        echo json_encode(['success' => true, 'message' => 'Coordinator updated successfully.']);
    } elseif ($action == 'get') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid coordinator ID.']);
            exit;
        }

        $stmt = $conn->prepare("
            SELECT 
                id, uniqueID AS unique_id, first_name, middle_name, last_name, faculty_id, coor_dept,
                course_handled, assigned_section, second_assigned_section,
                phone_number, coordinators_email, complete_address, verify_status
            FROM coordinators_account 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Coordinator not found.']);
        }
    } elseif ($action == 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid coordinator ID.']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM coordinators_account WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Coordinator deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Coordinator not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>