<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1); // Remove in production

session_start();

// Redirect if not authenticated
if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
    header('Location: index.php');
    exit;
}

$adminID = $_SESSION['auth_user']['admin_id'];

// Handle course and section addition
if (isset($_POST['addCourseSection'])) {
    $course = filter_input(INPUT_POST, 'course', FILTER_SANITIZE_STRING);
    $section = filter_input(INPUT_POST, 'section', FILTER_SANITIZE_STRING);

    error_log("Add course request: Course=$course, Section=$section");

    if (empty($course) || empty($section)) {
        error_log("Add course error: Course or section missing.");
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "Course and section are required.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM courses_sections WHERE course = ? AND section = ?");
            $stmt->execute([$course, $section]);
            if ($stmt->fetchColumn() > 0) {
                error_log("Add course error: Duplicate course-section combination.");
                $_SESSION['alert'] = "Error";
                $_SESSION['status'] = "Course and section already exist.";
            } else {
                $stmt = $conn->prepare("INSERT INTO courses_sections (course, section) VALUES (?, ?)");
                $stmt->execute([$course, $section]);
                error_log("Add course success: Course=$course, Section=$section");
                $_SESSION['alert'] = "Success";
                $_SESSION['status'] = "Course and section added successfully.";
            }
        } catch (PDOException $e) {
            error_log("Add course error: " . $e->getMessage());
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

    error_log("Edit course request: ID=$id, Course=$course, Section=$section");

    if (empty($id) || empty($course) || empty($section)) {
        error_log("Edit course error: Missing required fields.");
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "Course, section, and ID are required.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM courses_sections WHERE course = ? AND section = ? AND id != ?");
            $stmt->execute([$course, $section, $id]);
            if ($stmt->fetchColumn() > 0) {
                error_log("Edit course error: Duplicate course-section combination.");
                $_SESSION['alert'] = "Error";
                $_SESSION['status'] = "Course and section already exist.";
            } else {
                $stmt = $conn->prepare("UPDATE courses_sections SET course = ?, section = ? WHERE id = ?");
                $stmt->execute([$course, $section, $id]);
                error_log("Edit course success: Course=$course, Section=$section, ID=$id");
                $_SESSION['alert'] = "Success";
                $_SESSION['status'] = "Course and section updated successfully.";
            }
        } catch (PDOException $e) {
            error_log("Edit course error: " . $e->getMessage());
            $_SESSION['alert'] = "Error";
            $_SESSION['status'] = "Database error: " . $e->getMessage();
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle AJAX delete request
if (isset($_POST['ajaxDeleteSection'])) {
    header('Content-Type: application/json');
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

    error_log("AJAX Delete request: ID=$id");

    if (empty($id) || $id <= 0) {
        error_log("AJAX Delete error: Invalid section ID.");
        echo json_encode(['status' => 'error', 'message' => 'Valid section ID is required.']);
        exit;
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT section FROM courses_sections WHERE id = ?");
        $stmt->execute([$id]);
        $sectionData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sectionData) {
            error_log("AJAX Delete error: Section not found for ID=$id");
            throw new Exception("Section not found.");
        }
        $section = $sectionData['section'];

        // Clear assignments
        $stmt = $conn->prepare("UPDATE coordinators_account SET assigned_section = NULL WHERE assigned_section = ?");
        $stmt->execute([$section]);
        $stmt = $conn->prepare("UPDATE coordinators_account SET second_assigned_section = NULL WHERE second_assigned_section = ?");
        $stmt->execute([$section]);
        error_log("Cleared assignments for section: $section");

        // Delete section
        $stmt = $conn->prepare("DELETE FROM courses_sections WHERE id = ?");
        $stmt->execute([$id]);
        error_log("Deleted section: ID=$id");

        $conn->commit();
        error_log("AJAX Delete success: Section=$section, ID=$id");
        echo json_encode(['status' => 'success', 'message' => 'Section deleted successfully.']);
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("AJAX Delete error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("AJAX Delete error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle AJAX adviser assignment (for both dropdown and modal)
if (isset($_POST['ajaxSaveAssignment'])) {
    header('Content-Type: application/json');
    $section = filter_input(INPUT_POST, 'section', FILTER_SANITIZE_STRING);
    $adviser_id = filter_input(INPUT_POST, 'adviser_id', FILTER_SANITIZE_NUMBER_INT);

    error_log("AJAX Save Assignment: Section=$section, Adviser ID=$adviser_id");

    if (empty($section) || empty($adviser_id) || $adviser_id <= 0) {
        error_log("AJAX Error: Invalid section or adviser_id.");
        echo json_encode(['status' => 'error', 'message' => 'Valid section and adviser ID are required.']);
        exit;
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT COUNT(*) FROM courses_sections WHERE section = ?");
        $stmt->execute([$section]);
        if ($stmt->fetchColumn() === 0) {
            error_log("AJAX Error: Section not found: $section");
            echo json_encode(['status' => 'error', 'message' => 'Section not found.']);
            exit;
        }

        $stmt = $conn->prepare("
            SELECT id, assigned_section, second_assigned_section 
            FROM coordinators_account 
            WHERE id = ? AND verify_status = 'Verified' 
        ");
        $stmt->execute([$adviser_id]);
        $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$coordinator) {
            error_log("AJAX Error: Verified coordinator not found for ID=$adviser_id");
            echo json_encode(['status' => 'error', 'message' => 'Verified coordinator not found']);
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
            error_log("AJAX Error: Adviser ID=$adviser_id already assigned to two sections");
            echo json_encode(['status' => 'error', 'message' => 'Adviser is already assigned to two sections']);
            exit;
        }

        // Clear existing assignment if necessary
        if ($coordinator['assigned_section'] === $section) {
            $stmt = $conn->prepare("UPDATE coordinators_account SET assigned_section = NULL WHERE id = ?");
            $stmt->execute([$adviser_id]);
            error_log("Cleared assigned_section for Adviser ID=$adviser_id");
        } elseif ($coordinator['second_assigned_section'] === $section) {
            $stmt = $conn->prepare("UPDATE coordinators_account SET second_assigned_section = NULL WHERE id = ?");
            $stmt->execute([$adviser_id]);
            error_log("Cleared second_assigned_section for Adviser ID=$adviser_id");
        }

        // Assign to available slot
        if (empty($coordinator['assigned_section'])) {
            $stmt = $conn->prepare("UPDATE coordinators_account SET assigned_section = ? WHERE id = ?");
            $stmt->execute([$section, $adviser_id]);
            error_log("Set assigned_section: Section=$section, Adviser ID=$adviser_id");
        } elseif (empty($coordinator['second_assigned_section'])) {
            $stmt = $conn->prepare("UPDATE coordinators_account SET second_assigned_section = ? WHERE id = ?");
            $stmt->execute([$section, $adviser_id]);
            error_log("Set second_assigned_section: Section=$section, Adviser ID=$adviser_id");
        }

        $conn->commit();
        error_log("AJAX Success: Adviser ID=$adviser_id assigned to section $section");
        echo json_encode(['status' => 'success', 'message' => 'Adviser assigned successfully']);
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

// Fetch sections and courses
try {
    $stmt = $conn->prepare("SELECT id, course, section FROM courses_sections ORDER BY course ASC, section ASC");
    $stmt->execute();
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Fetch advisers
try {
    $stmt = $conn->prepare("
        SELECT id, CONCAT(first_name, ' ', last_name) as full_name, assigned_section, second_assigned_section
        FROM coordinators_account 
        WHERE verify_status = 'Verified' 
        ORDER BY first_name
    ");
    $stmt->execute();
    $advisers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Determine current assignments
$currentAssignments = [];
foreach ($advisers as $adviser) {
    if (!empty($adviser['assigned_section'])) {
        $currentAssignments[$adviser['assigned_section']] = $adviser['id'];
    }
    if (!empty($adviser['second_assigned_section'])) {
        $currentAssignments[$adviser['second_assigned_section']] = $adviser['id'];
    }
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
    <!-- Use CDN with local fallback for Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <!-- Use CDN with local fallback for SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" integrity="sha384-7j+Nj+ZX7eWDA8rPQzK0ZY3vDoS7V+BIYuwZ6kS+xfnkr0G+RT1M4HrwE3N2k00+" crossorigin="anonymous">
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
        .edit-adviser-btn {
            background-color: #28a745;
        }
        .edit-adviser-btn:hover {
            background-color: #218838;
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
                                    <th>Assigned Adviser</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courseSections as $section): ?>
                                    <tr data-section-id="<?php echo htmlspecialchars($section['id']); ?>">
                                        <td><?php echo htmlspecialchars($section['section']); ?></td>
                                        <td>
                                            <form class="assignment-form">
                                                <input type="hidden" name="section" value="<?php echo htmlspecialchars($section['section']); ?>">
                                                <select name="adviser_id" class="adviser-select" 
                                                        <?php echo isset($currentAssignments[$section['section']]) ? 'disabled' : ''; ?>>
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
                                            </form>
                                        </td>
                                        <td>
                                            <!-- Ensure data attributes are properly set -->
                                            <button type="button" class="action-btn edit-btn" 
                                                    data-id="<?php echo htmlspecialchars($section['id']); ?>"
                                                    data-course="<?php echo htmlspecialchars($section['course']); ?>"
                                                    data-section="<?php echo htmlspecialchars($section['section']); ?>">Edit</button>
                                            <button type="button" class="action-btn edit-adviser-btn" 
                                                    data-section="<?php echo htmlspecialchars($section['section']); ?>"
                                                    data-adviser-id="<?php echo isset($currentAssignments[$section['section']]) ? htmlspecialchars($currentAssignments[$section['section']]) : ''; ?>">Edit Adviser</button>
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

    <!-- Edit Course/Section Modal -->
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

    <!-- Edit Adviser Modal -->
    <div class="modal fade" id="editAdviserModal" tabindex="-1" aria-labelledby="editAdviserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAdviserModalLabel">Edit Adviser Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAdviserForm">
                    <div class="modal-body">
                        <input type="hidden" name="section" id="editAdviserSection">
                        <div class="mb-3">
                            <label for="editAdviserSelect" class="form-label">Select Adviser</label>
                            <select class="form-control" name="adviser_id" id="editAdviserSelect" required>
                                <option value="">Select Adviser</option>
                                <?php foreach ($advisers as $adviser): 
                                    $assignedCount = $adviserAssignments[$adviser['id']]['assigned_count'];
                                ?>
                                    <option value="<?php echo htmlspecialchars($adviser['id']); ?>" 
                                            data-assigned-count="<?php echo $assignedCount; ?>">
                                        <?php echo htmlspecialchars($adviser['full_name'] . " (" . $assignedCount . "/2 sections)"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Use CDN with local fallback for scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <!-- Fallback for jQuery -->
    <script>window.jQuery || document.write('<script src="js/lib/jquery-3.7.0.min.js"><\/script>')</script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js" integrity="sha384-Y6p+zYkS3H5Qk3ngH6+a+JHLzTqT0DHMp6O0AEBi0l1bW0l2H3q0Yz3Y3d1k5k0+" crossorigin="anonymous"></script>
    <!-- Fallback for SweetAlert2 -->
    <script>typeof Swal === 'undefined' && document.write('<script src="js/lib/sweetalert2.min.js"><\/script>')</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <!-- Fallback for Bootstrap -->
    <script>typeof bootstrap === 'undefined' && document.write('<script src="js/lib/bootstrap.bundle.min.js"><\/script>')</script>
    <script>
        $(document).ready(function() {
            console.log('Document ready, jQuery version:', $.fn.jquery, 'Bootstrap:', typeof bootstrap, 'SweetAlert2:', typeof Swal);

            // Adviser selection (dropdown)
            $(document).on('change', '.adviser-select', function() {
                if ($(this).is(':disabled')) {
                    console.log('Ignoring change event for disabled select');
                    return;
                }
                console.log('Adviser select change event triggered');
                saveAdviserAssignment($(this));
            });

            // Edit adviser button
            $(document).on('click', '.edit-adviser-btn', function(e) {
                e.preventDefault();
                console.log('Edit adviser button clicked:', $(this).data()); // Debug log
                const section = $(this).data('section');
                const currentAdviserId = $(this).data('adviser-id');

                if (!section) {
                    console.error('Missing section data');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Missing section data.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                $('#editAdviserSection').val(section);
                $('#editAdviserSelect').val(currentAdviserId || '');

                try {
                    const modal = new bootstrap.Modal(document.getElementById('editAdviserModal'));
                    modal.show();
                } catch (err) {
                    console.error('Modal initialization failed:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to open modal. Check console for details.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });

            // Edit adviser form submission
            $('#editAdviserForm').on('submit', function(e) {
                e.preventDefault();
                console.log('Edit adviser form submitted');
                saveAdviserAssignment($(this).find('select[name="adviser_id"]'));
                try {
                    bootstrap.Modal.getInstance(document.getElementById('editAdviserModal')).hide();
                } catch (err) {
                    console.error('Modal hide failed:', err);
                }
            });

            // Delete button
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                console.log('Delete button clicked:', $(this).data()); // Debug log
                const id = $(this).data('id');

                if (!id || id <= 0) {
                    console.error('Invalid section ID:', id);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Invalid section ID.',
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
                        console.log('Confirmed deletion for ID:', id);
                        $.ajax({
                            url: window.location.href,
                            type: 'POST',
                            data: {
                                ajaxDeleteSection: true,
                                id: id
                            },
                            dataType: 'json',
                            beforeSend: function() {
                                console.log('Sending delete AJAX request for ID:', id);
                            },
                            success: function(response) {
                                console.log('AJAX Delete Success:', response);
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
                                console.error('AJAX Delete Error:', { status, error, responseText: xhr.responseText });
                                let errorMsg = 'Failed to delete section.';
                                try {
                                    const jsonResponse = JSON.parse(xhr.responseText);
                                    errorMsg += ' ' + (jsonResponse.message || xhr.responseText);
                                } catch (e) {
                                    errorMsg += ' ' + (xhr.responseText || error);
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMsg,
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            });

            // Save adviser assignment (used by both dropdown and modal)
            function saveAdviserAssignment($select) {
                const $form = $select.closest('form');
                const section = $form.find('input[name="section"]').val() || $('#editAdviserSection').val();
                const adviser_id = $select.val();

                console.log('Saving adviser assignment:', { section, adviser_id });

                if (!section || !adviser_id || adviser_id === "") {
                    console.warn('Missing section or adviser_id');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Please select a valid adviser.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        ajaxSaveAssignment: true,
                        section: section,
                        adviser_id: adviser_id
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        console.log('Sending AJAX request for assignment');
                        $select.prop('disabled', true);
                    },
                    success: function(response) {
                        console.log('AJAX Success:', response);
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
                        console.error('AJAX Error:', { status, error, responseText: xhr.responseText });
                        let errorMsg = 'Failed to save assignment.';
                        try {
                            const jsonResponse = JSON.parse(xhr.responseText);
                            errorMsg += ' ' + (jsonResponse.message || xhr.responseText);
                        } catch (e) {
                            errorMsg += ' ' + (xhr.responseText || error);
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg,
                            confirmButtonColor: '#3085d6'
                        });
                    },
                    complete: function() {
                        $select.prop('disabled', false);
                        console.log('AJAX request completed');
                    }
                });
            }

            // Edit course/section button
            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                console.log('Edit course/section button clicked:', $(this).data()); // Debug log
                const id = $(this).data('id');
                const course = $(this).data('course');
                const section = $(this).data('section');

                if (!id || !course || !section) {
                    console.error('Missing section data:', { id, course, section });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Missing section data.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                $('#editSectionId').val(id);
                $('#editCourse').val(course);
                $('#editSection').val(section);

                try {
                    const modal = new bootstrap.Modal(document.getElementById('editSectionModal'));
                    modal.show();
                } catch (err) {
                    console.error('Modal initialization failed:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to open modal. Check console for details.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });

            // Search functionality
            $("#sectionSearch").on("keyup", function() {
                const value = $(this).val().toLowerCase().trim();
                $(".company-table tbody tr").each(function() {
                    const sectionText = $(this).find('td:first').text().toLowerCase();
                    $(this).toggle(sectionText.includes(value));
                });
            });

            // Sort tables
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

            // Form validation
            function validateForm($form) {
                const course = $form.find('input[name="course"]').val()?.trim();
                const section = $form.find('input[name="section"]').val()?.trim();
                if (course !== undefined && section !== undefined) {
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
                }
                return true;
            }

            $('#addCourseSectionForm, #editCourseSectionForm').on('submit', function(e) {
                if (!validateForm($(this))) {
                    e.preventDefault();
                }
            });

            // Session alerts
            <?php if (isset($_SESSION['status']) && $_SESSION['status'] != ''): ?>
                Swal.fire({
                    icon: '<?php echo strtolower($_SESSION['alert']); ?>',
                    title: '<?php echo $_SESSION['alert']; ?>',
                    text: '<?php echo $_SESSION['status']; ?>',
                    confirmButtonColor: '#3085d6'
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