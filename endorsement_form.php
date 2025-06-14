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

// Initialize variables for edit mode
$editMode = false;
$endorsement = [
    'id' => '',
    'student_id' => '',
    'first_name' => '',
    'middle_name' => '',
    'last_name' => '',
    'section' => '',
    'date_submitted' => date('Y-m-d')
];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $editMode = true;
    try {
        $stmt = $conn->prepare("
            SELECT e.id, e.student_id, e.first_name, e.middle_name, e.last_name, e.section, e.date_submitted 
            FROM endorsements e
            WHERE e.id = :id AND e.created_by = :created_by
        ");
        $stmt->execute([
            'id' => $_GET['id'],
            'created_by' => $_SESSION['auth_user']['admin_id']
        ]);
        $endorsement = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$endorsement) {
            $_SESSION['status'] = "Endorsement not found or you don't have permission to edit it.";
            $_SESSION['alert'] = "Error";
            $_SESSION['status-code'] = "error";
            header("Location: endorsement.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['status'] = "Failed to load endorsement: " . $e->getMessage();
        $_SESSION['alert'] = "Error";
        $_SESSION['status-code'] = "error";
        header("Location: endorsement.php");
        exit;
    }
}

// Fetch students for static dropdown
try {
    $stmt = $conn->prepare("SELECT id, first_name, middle_name, last_name, stud_section FROM students_data ORDER BY last_name, first_name, COALESCE(middle_name, '')");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching students: " . $e->getMessage());
    $students = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-name" content="focus" />
    <title>OJT Web Portal: <?php echo $editMode ? 'Edit Endorsement' : 'Add Endorsement'; ?></title>
    <link rel="shortcut icon" href="images/pupLogo.png">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/menubar/sidebar.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="endorsement-css/endorsement-moa.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .form-container h2 {
            font-size: 16px;
            color: #444444;
            margin-bottom: 20px;
        }
        .form-container select, .form-container input {
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
        .select2-container--default .select2-selection--single {
            border: 1px solid #ccc;
            border-radius: 5px;
            height: 38px;
            padding: 5px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
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
                        <a href="endorsement.php" class="back-button">
                            <span class="back-icon"><img src="images/less-than.png" alt="Back"></span>
                            Back
                        </a>
                    </div>
                </div>
                <h2><?php echo $editMode ? 'Edit Endorsement' : 'Add Endorsement'; ?></h2>
                <form action="manage_endorsement.php" method="POST">
                    <input type="hidden" name="action" value="<?php echo $editMode ? 'update' : 'create'; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($endorsement['id']); ?>">
                    <?php endif; ?>
                    <label for="student_id">Student <span style="color: #8B0000;">*</span></label>
                    <select name="student_id" id="student_id" required>
                        <option value="">Select a student</option>
                        <?php
                        if ($students) {
                            foreach ($students as $student) {
                                $selected = ($editMode && $student['id'] == $endorsement['student_id']) ? 'selected' : '';
                                $fullName = htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' - ' . $student['stud_section']);
                                echo "<option value=\"{$student['id']}\" $selected>$fullName</option>";
                            }
                        } else {
                            echo '<option value="">Error loading students</option>';
                        }
                        ?>
                    </select>
                    <label for="date_submitted">Date Submitted <span style="color: #8B0000;">*</span></label>
                    <input type="date" name="date_submitted" id="date_submitted" value="<?php echo htmlspecialchars($endorsement['date_submitted']); ?>" required>
                    <div class="action-buttons">
                        <button type="submit" class="action-button" aria-label="<?php echo $editMode ? 'Update Endorsement' : 'Add Endorsement'; ?>">
                            <i class="ti-save"></i> <?php echo $editMode ? 'Update' : 'Add'; ?> Endorsement
                        </button>
                        <a href="endorsement.php" class="back-button cancel-button" aria-label="Cancel">
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Comment out Select2 initialization to use static dropdown -->
    <!--
    <script>
        $(document).ready(function() {
            $('#student_id').select2({
                placeholder: "Select a student",
                allowClear: true,
                ajax: {
                    url: 'fetch_students.php',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.results.map(function(student) {
                                return {
                                    id: student.id,
                                    text: student.last_name + ', ' + student.first_name + ' ' + (student.middle_name || '') + ' - ' + student.section
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Select2 AJAX error:', textStatus, errorThrown, jqXHR.responseText);
                    },
                    cache: true
                },
                minimumInputLength: 0,
                sorter: function(data) {
                    return data.sort(function(a, b) {
                        return a.text.localeCompare(b.text);
                    });
                }
            });
        });
    </script>
    -->
    <?php 
    if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
    ?>
        <script>
        customAlert(<?php echo json_encode($_SESSION['alert'] ?? 'Notice'); ?>, <?php echo json_encode($_SESSION['status']); ?>, <?php echo json_encode($_SESSION['status-code']); ?>);
        </script>
    <?php
        unset($_SESSION['status']);
        unset($_SESSION['alert']);
        unset($_SESSION['status-code']);
    }
    ?>
</body>
</html>