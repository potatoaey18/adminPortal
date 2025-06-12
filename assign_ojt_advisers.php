<?php
include '../connection/config.php';
error_reporting(E_ALL); // Enable all error reporting for debugging
ini_set('display_errors', 1); // Display errors (remove in production)

session_start();

// Redirect if not authenticated
if (!isset($_SESSION['auth_user']['userid']) || $_SESSION['auth_user']['userid'] == 0) {
    header('Location: index.php');
    exit;
}

// Get admin data
$adminID = $_SESSION['auth_user']['userid'];
try {
    $stmt = $conn->prepare("SELECT * FROM admin_account WHERE id = ?");
    $stmt->execute([$adminID]);
    $adminData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$adminData) {
        error_log("Admin data not found for ID: $adminID");
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "Admin data not found.";
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Database error fetching admin data: " . $e->getMessage());
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "Database error: " . $e->getMessage();
    header('Location: index.php');
    exit;
}

// Handle course and section addition
if (isset($_POST['addCourseSection'])) {
    $course = filter_input(INPUT_POST, 'course', FILTER_SANITIZE_STRING);
    $section = filter_input(INPUT_POST, 'section', FILTER_SANITIZE_STRING);

    error_log("Add course request: Course=$course, Section=$section"); // Debug log

    if (empty($course) || empty($section)) {
        error_log("Add course error: Course or section missing.");
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "Course and section are required.";
    } else {
        try {
            // Check for duplicate course-section combination
            $stmt = $conn->prepare("SELECT COUNT(*) FROM courses_sections WHERE course = ? AND section = ?");
            $stmt->execute([$course, $section]);
            if ($stmt->fetchColumn() > 0) {
                error_log("Add course error: Duplicate course-section combination. Course=$course, Section=$section");
                $_SESSION['alert'] = "Error";
                $_SESSION['status'] = "Course and section already exist.";
            } else {
                // Insert new course and section
                $stmt = $conn->prepare("INSERT INTO courses_sections (course, section) VALUES (?, ?)");
                $stmt->execute([$course, $section]);

                // Log the action
                date_default_timezone_set('Asia/Manila');
                $date = date('F / d l / Y');
                $time = date('g:i A');
                $logs = "Added course $course with section $section.";
                $stmt = $conn->prepare("INSERT INTO admin_notification(userid, logs, logs_date, logs_time) VALUES (?, ?, ?, ?)");
                $stmt->execute([$adminID, $logs, $date, $time]);

                error_log("Add course success: Course=$course, Section=$section");
                $_SESSION['alert'] = "Success";
                $_SESSION['status'] = "Course and section added successfully.";
            }
        } catch (PDOException $e) {
            error_log("Add course error: Database error: " . $e->getMessage());
            $_SESSION['alert'] = "Error";
            $_SESSION['status'] = "Database error: " . $e->getMessage();
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle course and section editing
if (isset($_POST['editCourseSection'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $course = filter_input(INPUT_POST, 'course', FILTER_SANITIZE_STRING);
    $section = filter_input(INPUT_POST, 'section', FILTER_SANITIZE_STRING);

    error_log("Edit request: ID=$id, Course=$course, Section=$section"); // Debug log

    if (empty($id) || empty($course) || empty($section)) {
        error_log("Edit error: Missing required fields. ID=$id, Course=$course, Section=$section");
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "Course, section, and ID are required.";
    } else {
        try {
            // Check for duplicate course-section combination (excluding current ID)
            $stmt = $conn->prepare("SELECT COUNT(*) FROM courses_sections WHERE course = ? AND section = ? AND id != ?");
            $stmt->execute([$course, $section, $id]);
            if ($stmt->fetchColumn() > 0) {
                error_log("Edit error: Duplicate course-section combination. Course=$course, Section=$section, ID=$id");
                $_SESSION['alert'] = "Error";
                $_SESSION['status'] = "Course and section already exist.";
            } else {
                // Update course and section
                $stmt = $conn->prepare("UPDATE courses_sections SET course = ?, section = ? WHERE id = ?");
                $stmt->execute([$course, $section, $id]);

                // Log the action
                date_default_timezone_set('Asia/Manila');
                $date = date('F / d l / Y');
                $time = date('g:i A');
                $logs = "Edited course $course with section $section.";
                $stmt = $conn->prepare("INSERT INTO admin_notification(userid, logs, logs_date, logs_time) VALUES (?, ?, ?, ?)");
                $stmt->execute([$adminID, $logs, $date, $time]);

                error_log("Edit success: Course=$course, Section=$section, ID=$id");
                $_SESSION['alert'] = "Success";
                $_SESSION['status'] = "Course and section updated successfully.";
            }
        } catch (PDOException $e) {
            error_log("Edit error: Database error: " . $e->getMessage());
            $_SESSION['alert'] = "Error";
            $_SESSION['status'] = "Database error: " . $e->getMessage();
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle course and section deletion
if (isset($_POST['deleteCourseSection'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

    error_log("Delete request: ID=$id"); // Debug log

    if (empty($id)) {
        error_log("Delete error: Section ID is required.");
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
                error_log("Delete error: Section not found for ID=$id");
                throw new Exception("Section not found.");
            }
            $course = $sectionData['course'];
            $section = $sectionData['section'];

            // Check if section has students
            $stmt = $conn->prepare("SELECT COUNT(*) FROM students_data WHERE stud_course = ? AND stud_section = ?");
            $stmt->execute([$course, $section]);
            if ($stmt->fetchColumn() > 0) {
                error_log("Delete error: Cannot delete section with enrolled students. Course=$course, Section=$section");
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
            $stmt = $conn->prepare("INSERT INTO admin_notification(userid, logs, logs_date, logs_time) VALUES (?, ?, ?, ?)");
            $stmt->execute([$adminID, $logs, $date, $time]);

            $conn->commit();
            error_log("Delete success: Course=$course, Section=$section, ID=$id");
            $_SESSION['alert'] = "Success";
            $_SESSION['status'] = "Course and section deleted successfully.";
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Delete error: " . $e->getMessage());
            $_SESSION['alert'] = "Error";
            $_SESSION['status'] = $e->getMessage();
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle AJAX adviser assignment
if (isset($_POST['ajaxSaveAssignment'])) {
    header('Content-Type: application/json');
    $section = filter_input(INPUT_POST, 'section', FILTER_SANITIZE_STRING);
    $adviser_id = filter_input(INPUT_POST, 'adviser_id', FILTER_SANITIZE_NUMBER_INT);

    error_log("AJAX Save Assignment: Section=$section, Adviser ID=$adviser_id"); // Debug log

    if (empty($section) || empty($adviser_id)) {
        error_log("AJAX Error: Section or adviser_id missing. Section=$section, Adviser ID=$adviser_id");
        echo json_encode(['status' => 'error', 'message' => 'Section and adviser are required.']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // Check if adviser has less than 2 sections assigned
        $stmt = $conn->prepare("
            SELECT assigned_section, second_assigned_section 
            FROM coordinators_account 
            WHERE id = ?
        ");
        $stmt->execute([$adviser_id]);
        $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$coordinator) {
            error_log("AJAX Error: Coordinator not found for ID: $adviser_id");
            echo json_encode(['status' => 'error', 'message' => 'Adviser not found.']);
            exit;
        }

        $assignedCount = 0;
        if (!empty($coordinator['assigned_section'])) $assignedCount++;
        if (!empty($coordinator['second_assigned_section'])) $assignedCount++;
        $isAssignedToThisSection = in_array($section, array_filter([
            $coordinator['assigned_section'],
            $coordinator['second_assigned_section']
        ]));

        if ($assignedCount >= 2 && !$isAssignedToThisSection) {
            error_log("AJAX Error: Adviser ID $adviser_id already assigned to two sections.");
            echo json_encode(['status' => 'error', 'message' => 'Adviser is already assigned to two sections.']);
            exit;
        }

        // Update section_advisers table
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

        // Update coordinators_account table
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
        $stmt = $conn->prepare("INSERT INTO admin_notification(userid, logs, logs_date, logs_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$adminID, $logs, $date, $time]);

        $conn->commit();
        error_log("AJAX Success: Adviser ID $adviser_id assigned to section $section");
        echo json_encode(['status' => 'success', 'message' => 'Adviser assigned successfully.']);
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("AJAX Error: Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("AJAX Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Fetch sections, courses, and student counts
try {
    $stmt = $conn->prepare("
        SELECT cs.id, cs.course, cs.section, COUNT(sd.student_id) as total_students 
        FROM courses_sections cs
        LEFT JOIN students_data sd ON cs.course = sd.stud_course AND cs.section = sd.stud_section
        GROUP BY cs.id, cs.course, cs.section 
        ORDER BY cs.course ASC, cs.section ASC
    ");
    $stmt->execute();
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group sections by course
    $courses = [];
    foreach ($sections as $section) {
        $course = $section['course'];
        if (!isset($courses[$course])) {
            $courses[$course] = [];
        }
        $courses[$course][] = $section;
    }
} catch (PDOException $e) {
    error_log("Database error fetching sections: " . $e->getMessage());
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "Database error: " . $e->getMessage();
}

// Fetch advisers from coordinators_account
try {
    $stmt = $conn->prepare("
        SELECT id, CONCAT(first_name, ' ', last_name) as full_name, assigned_section, second_assigned_section
        FROM coordinators_account 
        WHERE verify_status = 'Verified' 
        ORDER BY first_name
    ");
    $stmt->execute();
    $advisers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate assigned sections for each adviser
    $adviserAssignments = [];
    foreach ($advisers as $adviser) {
        $assignedCount = 0;
        if (!empty($adviser['assigned_section'])) $assignedCount++;
        if (!empty($adviser['second_assigned_section'])) $assignedCount++;
        $adviserAssignments[$adviser['id']] = [
            'full_name' => $adviser['full_name'],
            'assigned_count' => $assignedCount,
            'assigned_sections' => array_filter([
                $adviser['assigned_section'],
                $adviser['second_assigned_section']
            ])
        ];
    }
} catch (PDOException $e) {
    error_log("Database error fetching advisers: " . $e->getMessage());
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "Database error: " . $e->getMessage();
}

// Fetch current adviser assignments from section_advisers
try {
    $stmt = $conn->prepare("SELECT section, adviser_id FROM section_advisers");
    $stmt->execute();
    $currentAssignments = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    error_log("Database error fetching assignments: " . $e->getMessage());
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OJT Web Portal: Assign OJT Advisers</title>
    <link rel="shortcut icon" href="images/pupLogo.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .content-wrap {
            height: 80%;
            width: 100%;
            margin: 0 auto;
        }
        .content-wrap > div {
            background-color: white;
            margin-top: 6rem;
            margin-left: 16rem;
            padding: 2rem;
        }
        .page-header {
            margin-bottom: 20px;
        }
        .page-title h1 {
            font-size: 16px;
            font-weight: bold;
        }
        .section-header {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 20px 0 10px;
            color: #8B0000;
        }
        .search-container {
            margin-bottom: 20px;
        }
        .search-box {
            width: 75%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .search-button {
            padding: 8px 20px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-button:hover {
            background-color: #700000;
        }
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 20px;
        }
        .company-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .company-table th {
            background-color: #fff;
            color: #700000;
            text-align: center;
            padding: 10px;
            min-width: 100px;
            border: 2px solid #700000;
            font-weight: 600;
        }
        .company-table td {
            padding: 10px;
            border: 2px solid #700000;
            text-align: center;
            color: #000;
        }
        .company-table tr:nth-child(odd) {
            background-color: #f2f2f2;
        }
        select {
            width: 100%;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .action-btn {
            background-color: #8B0000;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin: 0 3px;
        }
        .action-btn:hover {
            background-color: #700000;
        }
        .edit-btn {
            background-color: #007bff;
        }
        .edit-btn:hover {
            background-color: #0056b3;
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .add-course-section {
            margin-bottom: 20px;
        }
        .add-course-section input {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .add-course-section button {
            padding: 8px 20px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .add-course-section button:hover {
            background-color: #700000;
        }
        .modal-content {
            border-radius: 8px;
        }
        .modal-header {
            background-color: #8B0000;
            color: white;
        }
        .modal-footer .btn-primary {
            background-color: #8B0000;
            border: none;
        }
        .modal-footer .btn-primary:hover {
            background-color: #700000;
        }
    </style>
</head>
<body>
    <?php require_once 'templates/admin_navbar.php'; ?>

    <div class="content-wrap">
        <div>
            <div class="page-header">
                <div class="page-title"><br>
                    <h1>Assign OJT Advisers</h1><br>
                </div>
            </div>
            <!-- Form to add course and section -->
            <div class="add-course-section">
                <form method="POST" action="" id="addCourseSectionForm">
                    <input type="text" name="course" placeholder="Course (e.g., DIT)" required>
                    <input type="text" name="section" placeholder="Year & Section (e.g., 1)" required>
                    <button type="submit" name="addCourseSection">Add Course & Section</button>
                </form>
            </div>
            <div class="search-container">
                <input type="text" class="search-box" placeholder="Search by section..." id="sectionSearch" aria-label="Search sections">
                <button class="search-button" aria-label="Search">Search</button>
            </div>
            <div class="table-container">
                <?php if (empty($advisers)): ?>
                    <p>No verified advisers found. Please add or verify coordinators.</p>
                <?php endif; ?>
                <?php if (empty($courses)): ?>
                    <p>No sections found. Please add a course and section.</p>
                <?php else: ?>
                    <?php foreach ($courses as $course => $courseSections): ?>
                        <div class="section-header">Course: <?php echo htmlspecialchars($course); ?></div>
                        <table class="company-table" id="courseTable_<?php echo htmlspecialchars(str_replace(' ', '_', $course)); ?>">
                            <thead>
                                <tr>
                                    <th>Section</th>
                                    <th>Total Students</th>
                                    <th>Assigned Adviser</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courseSections as $section): ?>
                                    <tr data-section-id="<?php echo htmlspecialchars($section['id']); ?>">
                                        <td><?php echo htmlspecialchars($section['section']); ?></td>
                                        <td><?php echo htmlspecialchars($section['total_students']); ?></td>
                                        <td>
                                            <form class="assignment-form" method="POST" action="">
                                                <input type="hidden" name="section" value="<?php echo htmlspecialchars($section['section']); ?>">
                                                <select name="adviser_id" class="adviser-select">
                                                    <option value="">Select Adviser</option>
                                                    <?php foreach ($advisers as $adviser): 
                                                        $assignedCount = $adviserAssignments[$adviser['id']]['assigned_count'];
                                                        $isAssignedToThisSection = in_array($section['section'], $adviserAssignments[$adviser['id']]['assigned_sections']);
                                                        $isDisabled = ($assignedCount >= 2 && !$isAssignedToThisSection) ? 'disabled' : '';
                                                    ?>
                                                        <option value="<?php echo htmlspecialchars($adviser['id']); ?>" 
                                                            <?php echo (isset($currentAssignments[$section['section']]) && $currentAssignments[$section['section']] == $adviser['id']) ? 'selected' : ''; ?>
                                                            <?php echo $isDisabled; ?>
                                                            class="adviser-option">
                                                            <?php echo htmlspecialchars($adviser['full_name'] . " (" . $assignedCount . "/2 sections)"); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="button" class="action-btn save-btn mt-2">Save</button>
                                            </form>
                                        </td>
                                        <td>
                                            <button type="button" class="action-btn edit-btn" 
                                                    data-id="<?php echo htmlspecialchars($section['id']); ?>"
                                                    data-course="<?php echo htmlspecialchars($section['course']); ?>"
                                                    data-section="<?php echo htmlspecialchars($section['section']); ?>">Edit</button>
                                            <button type="button" class="action-btn delete-btn" 
                                                    data-id="<?php echo htmlspecialchars($section['id']); ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editSectionModal" tabindex="-1" aria-labelledby="editSectionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSectionModalLabel">Edit Course & Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="" id="editCourseSectionForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editSectionId">
                        <div class="mb-3">
                            <label for="editCourse" class="form-label">Course</label>
                            <input type="text" class="form-control" name="course" id="editCourse" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSection" class="form-label">Section</label>
                            <input type="text" class="form-control" name="section" id="editSection" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="editCourseSection" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            console.log('Document ready, jQuery version:', $.fn.jquery); // Debug log

            // Debug adviser data
            console.log("Advisers loaded: <?php echo json_encode($advisers, JSON_HEX_QUOT | JSON_HEX_APOS); ?>");

            // Sidebar dropdown
            const $dropdownToggles = $(".dropdown-toggle");
            $dropdownToggles.each(function() {
                const $parent = $(this).parent();
                const $dropdownContent = $(this).siblings(".dropdown-content");
                const $arrow = $(this).find(".dropdown-arrow");
                if ($parent.hasClass("active")) {
                    $dropdownContent.css("display", "block");
                    if ($arrow.length) $arrow.text("▲");
                }
            });
            $dropdownToggles.click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $parent = $(this).parent();
                const $dropdownContent = $(this).siblings(".dropdown-content");
                const $arrow = $(this).find(".dropdown-arrow");
                const isActive = $parent.hasClass("active");
                if (isActive) {
                    $parent.removeClass("active");
                    $dropdownContent.slideUp(200);
                    if ($arrow.length) $arrow.text("▼");
                } else {
                    $parent.addClass("active");
                    $dropdownContent.slideDown(200);
                    if ($arrow.length) $arrow.text("▲");
                }
            });

            // Profile dropdown
            $(".avatar-trigger").click(function(e) {
                e.stopPropagation();
                $(this).siblings(".dropdown-menu").toggleClass("show");
            });
            $(document).click(function() {
                $(".dropdown-menu").removeClass("show");
            });

            // Search functionality across all tables
            $("#sectionSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase().trim();
                $(".company-table tbody tr").each(function() {
                    var sectionText = $(this).find('td:first').text().toLowerCase();
                    $(this).toggle(sectionText.indexOf(value) > -1);
                });
            });

            // Sort each course table by section
            $('table.company-table').each(function() {
                const table = $(this);
                const tbody = table.find('tbody');
                const rows = tbody.find('tr').toArray();
                rows.sort(function(a, b) {
                    const aValue = $(a).find('td').eq(0).text().toLowerCase();
                    const bValue = $(b).find('td').eq(0).text().toLowerCase();
                    return aValue.localeCompare(bValue);
                });
                tbody.empty();
                $.each(rows, function(index, row) {
                    tbody.append(row);
                });
            });

            // Handle adviser assignment save
            $(document).on('click', '.save-btn', function(e) {
                e.preventDefault();
                const $button = $(this);
                if ($button.prop('disabled')) {
                    console.log('Save button disabled, preventing double submission');
                    return; // Prevent multiple submissions
                }
                console.log('Save button clicked'); // Debug log
                const $form = $button.closest('form');
                const $select = $form.find('.adviser-select');
                const section = $form.find('input[name="section"]').val();
                const adviser_id = $select.val();

                if (!adviser_id) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please select an adviser.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to assign this adviser to the selected section?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, assign it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $button.prop('disabled', true).text('Saving...');
                        $.ajax({
                            url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                            type: 'POST',
                            data: {
                                ajaxSaveAssignment: true,
                                section: section,
                                adviser_id: adviser_id
                            },
                            dataType: 'json',
                            success: function(response) {
                                console.log('AJAX Success:', response); // Debug log
                                $button.prop('disabled', false).text('Save');
                                Swal.fire({
                                    icon: response.status,
                                    title: response.status.charAt(0).toUpperCase() + response.status.slice(1),
                                    text: response.message,
                                    confirmButtonColor: '#3085d6'
                                });
                                if (response.status === 'success') {
                                    location.reload();
                                }
                            },
                            error: function(xhr, status, error) {
                                $button.prop('disabled', false).text('Save');
                                console.error('AJAX Error:', {
                                    status: status,
                                    error: error,
                                    responseText: xhr.responseText
                                }); // Detailed error logging
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to save assignment: ' + (xhr.responseText || error),
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            });

            // Handle edit button click
            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault(); // Prevent default behavior
                console.log('Edit button clicked'); // Debug log
                const $button = $(this);
                const id = $button.data('id');
                const course = $button.data('course');
                const section = $button.data('section');

                console.log('Edit data:', { id, course, section }); // Debug log

                if (!id || !course || !section) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Missing section data. Please try again.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                $('#editSectionId').val(id);
                $('#editCourse').val(course);
                $('#editSection').val(section);

                try {
                    const modal = new bootstrap.Modal(document.getElementById('editSectionModal'), {
                        keyboard: false
                    });
                    modal.show();
                } catch (error) {
                    console.error('Modal Error:', error); // Debug log
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to open edit modal: ' + error.message,
                        confirmButtonColor: '#3085d6'
                    });
                }
            });

            // Handle delete button click
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault(); // Prevent default behavior
                console.log('Delete button clicked'); // Debug log
                const id = $(this).data('id');

                if (!id) {
                    console.error('Delete Error: Missing section ID');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Missing section ID. Please try again.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete the section and its adviser assignments.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Creating delete form for ID:', id); // Debug log
                        const $form = $('<form>', {
                            'method': 'POST',
                            'action': '<?php echo $_SERVER['PHP_SELF']; ?>',
                            'html': `<input type="hidden" name="deleteCourseSection" value="1">
                                     <input type="hidden" name="id" value="${id}">`
                        }).appendTo('body');
                        console.log('Form created:', $form[0]); // Debug log
                        $form.submit();
                    }
                });
            });

            // Form validation for add/edit forms
            function validateForm($form) {
                const course = $form.find('input[name="course"]').val().trim();
                const section = $form.find('input[name="section"]').val().trim();
                if (!course || !section) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Both course and section fields are required.',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
                if (course.length > 100) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Course name cannot exceed 100 characters.',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
                if (section.length > 50) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Section name cannot exceed 50 characters.',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
                return true;
            }

            $('#addCourseSectionForm').on('submit', function(e) {
                if (!validateForm($(this))) {
                    e.preventDefault();
                }
            });

            $('#editCourseSectionForm').on('submit', function(e) {
                if (!validateForm($(this))) {
                    e.preventDefault();
                }
            });

            // Auto-logout
            var adminId = <?php echo $_SESSION['auth_user']['userid']; ?>;
            var logoutTimeout;
            function startLogoutTimer() {
                logoutTimeout = setTimeout(function() {
                    $.ajax({
                        type: 'POST',
                        url: 'admin_update_status_AutoLogOut.php',
                        data: { userid: adminId },
                        success: function() {
                            window.location.href = 'index.php';
                        },
                        error: function(xhr, status, error) {
                            console.error('Auto-logout error:', error);
                        }
                    });
                }, 360000);
            }
            function resetLogoutTimer() {
                clearTimeout(logoutTimeout);
                startLogoutTimer();
            }
            startLogoutTimer();
            document.addEventListener('mousemove', resetLogoutTimer);
            document.addEventListener('keydown', resetLogoutTimer);

            function profile() {
                window.location.href = 'view_admin_profile.php';
            }
            function settings() {
                window.location.href = 'admin_settings.php';
            }
            function logout() {
                window.location.href = 'admin_logout.php';
            }

            // SweetAlert2 for session alerts
            <?php if (isset($_SESSION['status']) && $_SESSION['status'] != ''): ?>
                Swal.fire({
                    icon: '<?php echo strtolower($_SESSION['alert']); ?>',
                    title: '<?php echo $_SESSION['alert']; ?>',
                    text: '<?php echo $_SESSION['status']; ?>',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                <?php
                unset($_SESSION['status']);
                unset($_SESSION['alert']);
                ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>