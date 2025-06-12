<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['auth_user']['userid']) || $_SESSION['auth_user']['userid'] == 0) {
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "You must be logged in as an admin to view this page.";
    header("Location: index.php");
    exit();
}

// Get student ID from URL parameter
$studID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($studID <= 0) {
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "Invalid or missing student ID.";
    header("Location: admin_student_trainees.php");
    exit();
}

try {
    // Fetch student data
    $stmt = $conn->prepare("
        SELECT 
            s.id,
            s.student_ID,
            s.first_name,
            s.middle_name,
            s.last_name,
            CONCAT(s.first_name, ' ', COALESCE(s.middle_name, ''), ' ', s.last_name) AS full_name,
            s.stud_section,
            s.stud_course,
            s.year_level,
            s.age,
            s.ojt_status,
            s.stud_hte AS deployedCompany,
            s.medical_condition,
            s.profile_picture,
            sup.company_address,
            sup.phone_number
        FROM students_data s
        LEFT JOIN supervisor sup ON s.stud_hte = sup.company_name
        WHERE s.id = :id
    ");
    $stmt->execute(['id' => $studID]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "No student found with ID: " . htmlspecialchars($studID);
        header("Location: admin_student_trainees.php");
        exit();
    }

    // Fetch skills
    $skillsStmt = $conn->prepare("SELECT * FROM stud_skills WHERE stud_id = :stud_id");
    $skillsStmt->execute(['stud_id' => $studID]);
    $skills = $skillsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Set student type (fallback if not in database)
    $studentType = isset($data['is_working_student']) && $data['is_working_student'] == 'yes' ? 'Working Student' : 'Student';
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "Database error occurred.";
    header("Location: admin_student_trainees.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OJT Web Portal: Student Profile</title>
    <link rel="shortcut icon" href="images/pupLogo.png">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/menubar/sidebar.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/lib/sweetalert/sweetalert.css" rel="stylesheet">
    <link href="../endorsement-css/endorsement-moa.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f8ff;
            font-family: Arial, sans-serif;
            color: #000;
            overflow: hidden;
        }
        .profile-card {
            max-width: 1500px;
            margin: 0 auto;
            background-color: white;
            overflow: hidden;
        }
        .profile-header {
            padding: 15px;
            font-weight: bold;
        }
        .profile-content {
            display: flex;
            padding: 20px;
            margin-top: 40px;
        }
        .profile-image {
            flex: 0 0 300px;
            text-align: center;
            padding: 20px;
        }
        .image-placeholder {
            width: 400px;
            height: 400px;
            margin: 0 auto;
            border-radius: 50%;
            border: 2px solid #e0e0e0;
            background-color: #f8f8f8;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 10px solid #D9D9D9;
        }
        .image-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .placeholder-icon {
            width: 100px;
            height: auto;
            opacity: 0.3;
        }
        .student-info {
            flex: 1;
            padding: 10px 120px;
        }
        .student-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 50px;
        }
        .student-badge {
            background-color: #ffc107;
            color: #333;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
        }
        .student-details {
            margin-top: 20px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            flex: 0 0 150px;
            font-weight: 500;
        }
        .detail-value {
            flex: 1;
            font-weight: bold;
        }
        .abnormal {
            color: #dc3545;
            font-weight: bold;
        }
        .back-btn {
            background-color: #8B0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 20px;
        }
        .back-btn:hover {
            background-color: #700000;
        }
    </style>
</head>
<body>
    <?php require_once 'templates/admin_navbar.php'; ?>
    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="page-header"><div class="page-header">
                <div>
                    <a href="student_trainee.php" class="back-button">
                        <span class="back-icon"><img src="images/less-than.png" alt="Back"></span>
                        Back
                    </a>
                </div>
            </div>
                <div class="page-title">
                    <h1 style="font-size: 16px;">STUDENT PROFILE</h1>
                </div>
            </div>
            <div class="profile-card">
                <div class="profile-content">
                    <div class="profile-image">
                        <div class="image-placeholder">
                            <?php if (!empty($data['profile_picture']) && file_exists($data['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($data['profile_picture']); ?>" alt="Profile Image">
                            <?php else: ?>
                                <img src="images/placeholder.png" alt="Profile Placeholder" class="placeholder-icon">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="student-info">
                        <div class="student-name">
                            <?php echo htmlspecialchars($data['first_name'] ?? ''); ?> 
                            <?php echo htmlspecialchars($data['middle_name'] ?? ''); ?> 
                            <?php echo htmlspecialchars($data['last_name'] ?? ''); ?>
                            <span class="student-badge"><?php echo htmlspecialchars($studentType); ?></span>
                        </div>
                        <div class="student-id">
                            Student No.: <?php echo htmlspecialchars($data['student_ID'] ?? 'N/A'); ?>
                        </div>
                        <br>
                        <div class="student-details">
                            <div class="detail-row">
                                <div class="detail-label">Course</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['stud_course'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Year</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['year_level'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Section</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['stud_section'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Age</div>
                                <div class="detail-value">: <?php echo isset($data['age']) ? htmlspecialchars($data['age']) . ' years old' : 'N/A'; ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">OJT Status</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['ojt_status'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">HTE</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['deployedCompany'] ?? 'Not Assigned'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Company Address</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['company_address'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Company Phone</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['phone_number'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Medical Condition</div>
                                <div class="detail-value <?php echo (isset($data['medical_condition']) && $data['medical_condition'] == 'Abnormal') ? 'abnormal' : ''; ?>">
                                    : <?php echo htmlspecialchars($data['medical_condition'] ?? 'N/A'); ?>
                                </div>
                            </div>
                            <?php if (!empty($skills)): ?>
                                <div class="detail-row">
                                    <div class="detail-label">Skills</div>
                                    <div class="detail-value">: <?php echo htmlspecialchars(implode(', ', array_column($skills, 'skill_name'))); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/lib/jquery.min.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/menubar/sidebar.js"></script>
    <script src="js/lib/preloader/pace.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="js/lib/sweetalert/sweetalert.min.js"></script>
    <script src="js/lib/sweetalert/sweetalert.init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/customAlert.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (isset($_SESSION['status']) && $_SESSION['status'] != ''): ?>
                Swal.fire({
                    icon: '<?php echo strtolower($_SESSION['alert']); ?>',
                    title: '<?php echo $_SESSION['alert']; ?>',
                    text: '<?php echo $_SESSION['status']; ?>',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                }).then(() => {
                    <?php
                    unset($_SESSION['status']);
                    unset($_SESSION['alert']);
                    ?>
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>