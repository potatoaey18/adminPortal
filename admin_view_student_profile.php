<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "You must be logged in as an admin to view this page.";
    header("Location: ../pending/login.php");
    exit();
}

// Get student ID from URL parameter
$studID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($studID <= 0) {
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "Invalid or missing student ID.";
    header("Location: student_trainee.php");
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
            s.ojt_status,
            s.stud_hte AS deployedCompany,
            s.total_rendered_hours AS rendered_hours,
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
        header("Location: student_trainee.php");
        exit();
    }

    // Fetch pre-internship documents
    $docsStmt = $conn->prepare("SELECT * FROM endorsement_documents WHERE student_id = :student_id");
    $docsStmt->execute(['student_id' => $studID]);
    $documents = $docsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "Database error: " . htmlspecialchars($e->getMessage());
    header("Location: student_trainee.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OJT Web Portal: View Progress</title>
    <link rel="shortcut icon" href="images/pupLogo.png">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/menubar/sidebar.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/lib/sweetalert/sweetalert.css" rel="stylesheet">
    
    <link href="endorsement-css/endorsement-moa.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f8ff;
            font-family: Arial, sans-serif;
            color: #000;
        }
        .progress-card {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .student-info {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            min-width: 150px;
        }
        .info-value {
            flex-grow: 1;
        }
        .documents-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .documents-table th {
            background-color: #8B0000;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .documents-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .documents-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .no-submission {
            color: #999;
            font-style: italic;
        }
        .view-button {
            background-color: #8B0000;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }
        .view-button:hover {
            background-color: #700000;
        }
        .back-icon {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php require_once 'templates/admin_navbar.php'; ?>
    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="page-header">
                <div>
                    <a href="student_trainee.php" class="back-button">
                        <span class="back-icon"><img src="images/less-than.png" alt="Back"></span>
                        Back
                    </a>
                </div>
                <div class="page-header">
                    <div class="page-title">
                        <h1 style="font-size: 16px;">Student Progress</h1>
                    </div>
                </div>
                <br>    
            </div>
            
            <div>
                <div class="student-info">
                    <div class="info-row">
                        <div class="info-label">Student's Name:</div>
                        <div class="info-value"><?php echo htmlspecialchars($data['full_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Section:</div>
                        <div class="info-value"><?php echo htmlspecialchars($data['stud_section'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Status:</div>
                        <div class="info-value"><?php echo htmlspecialchars($data['ojt_status'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">OJT Adviser:</div>
                        <div class="info-value"><?php echo htmlspecialchars($data['qjt_adviser'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Company name:</div>
                        <div class="info-value"><?php echo htmlspecialchars($data['deployedCompany'] ?? 'Not Assigned'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Target internship hours:</div>
                        <div class="info-value"><?php echo htmlspecialchars($data['target_hours'] ?? '300'); ?>hrs</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Rendered hours:</div>
                        <div class="info-value"><?php echo htmlspecialchars($data['rendered_hours'] ?? '0'); ?>hrs</div>
                    </div>
                </div>

                <h3>Pre-Internship Papers</h3>
                <table class="documents-table">
                    <thead>
                        <tr>
                            <th>Document name</th>
                            <th>Date Accomplished</th>
                            <th>Adviser's remarks</th>
                            <th>View Document</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $requiredDocs = [
                            'Medical Certificate',
                            'Memorandum of Agreement (MOA)',
                            'Internship of Agreement'
                        ];
                        
                        foreach ($requiredDocs as $docName): 
                            $foundDoc = null;
                            foreach ($documents as $doc) {
                                if ($doc['document_name'] === $docName) {
                                    $foundDoc = $doc;
                                    break;
                                }
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($docName); ?></td>
                                <td>
                                    <?php if ($foundDoc && !empty($foundDoc['upload_date'])): ?>
                                        <?php echo htmlspecialchars($foundDoc['upload_date']); ?>
                                    <?php else: ?>
                                        <span class="no-submission">No submission yet.</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($foundDoc && !empty($foundDoc['remarks'])): ?>
                                        <?php echo htmlspecialchars($foundDoc['remarks']); ?>
                                    <?php elseif ($docName === 'Memorandum of Agreement (MOA)'): ?>
                                        Wrong file.
                                    <?php elseif ($docName === 'Internship of Agreement'): ?>
                                        For checking.
                                    <?php else: ?>
                                        <span class="no-submission">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($foundDoc && !empty($foundDoc['uploaded_path'])): ?>
                                        <a href="<?php echo htmlspecialchars($foundDoc['uploaded_path']); ?>" class="view-button" target="_blank">View</a>
                                    <?php else: ?>
                                        <span class="no-submission">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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