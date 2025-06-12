<?php
include '../connection/config.php';

if (!isset($_SESSION['auth_user']['userid']) || $_SESSION['auth_user']['userid'] == 0) {
    $_SESSION['status'] = "Unauthorized access.";
    $_SESSION['alert'] = "Error";
    $_SESSION['status-code'] = "error";
    header("Location: index.php");
    exit;
}

// Initialize variables for edit mode
$editMode = false;
$courseSection = [
    'id' => '',
    'course' => '',
    'section' => '',
    'adviser_id' => ''
];

// Fetch advisers for dropdown
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

    $stmt = $conn->prepare("SELECT section, adviser_id FROM section_advisers");
    $stmt->execute();
    $currentAssignments = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    error_log("Database error fetching advisers: " . $e->getMessage());
    $_SESSION['status'] = "Failed to load advisers: " . $e->getMessage();
    $_SESSION['alert'] = "Error";
    $_SESSION['status-code'] = "error";
    header("Location: assign_advisers.php");
    exit;
}

// Load data for edit mode
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $editMode = true;
    try {
        $stmt = $conn->prepare("
            SELECT cs.id, cs.course, cs.section, sa.adviser_id
            FROM courses_sections cs
            LEFT JOIN section_advisers sa ON cs.section = sa.section
            WHERE cs.id = :id
        ");
        $stmt->execute(['id' => $_GET['id']]);
        $courseSection = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$courseSection) {
            $_SESSION['status'] = "Course section not found.";
            $_SESSION['alert'] = "Error";
            $_SESSION['status-code'] = "error";
            header("Location: assign_advisers.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['status'] = "Failed to load course section: " . $e->getMessage();
        $_SESSION['alert'] = "Error";
        $_SESSION['status-code'] = "error";
        header("Location: assign_advisers.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-name" content="focus" />
    <title>OJT Web Portal: <?php echo $editMode ? 'Edit Course & Section' : 'Add Course & Section'; ?></title>
    <link rel="shortcut icon" href="images/pupLogo.png">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/menubar/sidebar.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="endorsement-css/endorsement-moa.css" rel="stylesheet">
    <style>
        .form-container h2 {
            font-size: 16px;
            color: #444444;
            margin-bottom: 20px;
        }
        .form-container input, .form-container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container label {
            display: block;
            font-weight: 600;
            color: #700000;
            margin: 10px 0 5px;
        }
        .form-container .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .form-container .action-button {
            background-color: #8B0000;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .form-container .action-button:hover {
            background-color: #700000;
            transform: translateY(-2px);
        }
        .form-container .action-button.cancel-button {
            background-color: #6c757d;
        }
        .form-container .action-button.cancel-button:hover {
            background-color: #5a6268;
        }
        .form-container .action-button:focus {
            outline: 2px solid #700000;
            outline-offset: 2px;
        }
        .form-container .action-button[aria-label] {
            position: relative;
        }
        .form-container .action-button[aria-label]:hover:after {
            content: attr(aria-label);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 10;
        }
        @media (max-width: 768px) {
            .form-container {
                margin: 20px;
                padding: 15px;
            }
            .form-container .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            .form-container .action-button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'templates/admin_navbar.php'; ?>
    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="form-container">
                <div class="page-header">
                    <div>
                        <a href="assign_advisers.php" class="back-button">
                            <span class="back-icon"><img src="images/less-than.png" alt="Back"></span>
                            Back
                        </a>
                    </div>
                </div>
                <h2><?php echo $editMode ? 'Edit Course & Section' : 'Add Course & Section'; ?></h2>
                <form action="course_section_crud.php" method="POST">
                    <input type="hidden" name="action" value="<?php echo $editMode ? 'update' : 'create'; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($courseSection['id']); ?>">
                    <?php endif; ?>
                    <label for="course">Course <span style="color: #8B0000;">*</span></label>
                    <input type="text" name="course" id="course" value="<?php echo htmlspecialchars($courseSection['course']); ?>" placeholder="Course (e.g., DIT)" required maxlength="100">
                    <label for="section">Section <span style="color: #8B0000;">*</span></label>
                    <input type="text" name="section" id="section" value="<?php echo htmlspecialchars($courseSection['section']); ?>" placeholder="Year & Section (e.g., 1)" required maxlength="50">
                    <label for="adviser_id">Adviser</label>
                    <select name="adviser_id" id="adviser_id">
                        <option value="">Select Adviser (Optional)</option>
                        <?php foreach ($advisers as $adviser): 
                            $assignedCount = $adviserAssignments[$adviser['id']]['assigned_count'];
                            $isAssignedToThisSection = in_array($courseSection['section'], $adviserAssignments[$adviser['id']]['assigned_sections']);
                            $isDisabled = ($assignedCount >= 2 && !$isAssignedToThisSection && $courseSection['adviser_id'] != $adviser['id']) ? 'disabled' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($adviser['id']); ?>" 
                                    <?php echo ($courseSection['adviser_id'] == $adviser['id']) ? 'selected' : ''; ?>
                                    <?php echo $isDisabled; ?>>
                                <?php echo htmlspecialchars($adviser['full_name'] . " (" . $assignedCount . "/2 sections)"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="action-buttons">
                        <button type="submit" class="action-button" name="saveCourseSection" aria-label="<?php echo $editMode ? 'Update Course & Section' : 'Add Course & Section'; ?>">
                            <i class="ti-save"></i> <?php echo $editMode ? 'Update' : 'Add'; ?> Course & Section
                        </button>
                        <a href="assign_advisers.php" class="action-button cancel-button" aria-label="Cancel">
                            <i class="ti-close"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="js/lib/jquery.min.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/preloader/pace.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/lib/customAlert.js"></script>
    <?php 
    if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
    ?>
        <script>
        customAlert("<?php echo $_SESSION['alert'] ?? 'Notice'; ?>", "<?php echo $_SESSION['status']; ?>", "<?php echo $_SESSION['status-code'] ?? 'info'; ?>");
        </script>
    <?php
        unset($_SESSION['status']);
        unset($_SESSION['alert']);
        unset($_SESSION['status-code']);
    }
    ?>
</body>
</html>
<?php ?>