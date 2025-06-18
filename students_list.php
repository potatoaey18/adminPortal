<?php
include '../connection/config.php';

// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
error_log("Session admin_id: " . ($_SESSION['auth_user']['id'] ?? 'Not set'));
if (!isset($_SESSION['auth_user']['id']) || $_SESSION['auth_user']['id'] <= 0) {
    header('Location: index.php');
    exit;
}

// Set current page for navigation
$current_page = 'manage_students.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OJT Web Portal: Manage Student Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="shortcut icon" href="images/Picture1.jpg">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
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
        .user-table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-table th {
            background-color: #fff;
            color: #700000;
            text-align: center;
            padding: 15px 30px;
            min-width: 150px;
            border: 2px solid #700000;
            font-weight: 600;
        }
        .user-table td {
            padding: 15px 30px;
            border: 2px solid #700000;
            text-align: center;
            color: #000;
        }
        .user-table tr:nth-child(odd) {
            background-color: #f2f2f2;
        }
        .action-button {
            background-color: #8B0000;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin: 0 10px;
            transition: background-color 0.2s;
        }
        .action-button:hover {
            background-color: #700000;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .step-description {
            margin-bottom: 20px;
            color: #000;
            padding: 20px;
            background: #f9f9f9;
        }
        .page-header {
            margin-bottom: 20px;
        }
        .page-title h1 {
            font-size: 16px;
            font-weight: bold;
        }
        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            margin-bottom: 20px;
            background-color: #8B0000;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .back-button:hover {
            background-color: #700000;
        }
        .back-icon img {
            width: 20px;
            height: auto;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php require_once 'includes/admin_menu.php'; ?>

    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="page-header">
                <div>
                    <a href="dashboard.php" class="back-button">
                        <span class="back-icon"><img src="images/less-than.png" alt="Back Icon"></span>
                        Back
                    </a>
                </div>
                <div class="page-title"><br>
                    <h1>MANAGE STUDENT USERS</h1><br>
                </div>
            </div>
            <div class="step-description">
                <p>This section allows you to view and manage student users. Use the search bar to find specific students or click "View Profile" to see detailed information for each student.</p>
            </div>
            <div class="search-container">
                <input type="text" class="search-box" placeholder="Search by name or student ID..." id="userSearch">
                <button class="search-button">Search</button>
            </div>
            <div class="table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Section</th>
                            <th>Full Name</th>
                            <th>OJT Status</th>
                            <th>HTE</th>
                            <th>HTE Address</th>
                            <th>HTE Contact</th>
                            <th>View Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $conn->prepare("
                                SELECT 
                                    s.id,
                                    s.student_ID,
                                    s.stud_section,
                                    CONCAT(s.first_name, ' ', COALESCE(s.middle_name, ''), ' ', s.last_name) AS full_name,
                                    s.ojt_status,
                                    s.stud_hte,
                                    sup.company_address,
                                    sup.phone_number
                                FROM students_data s
                                LEFT JOIN supervisor sup ON s.stud_hte = sup.company_name
                                ORDER BY s.student_ID
                            ");
                            $stmt->execute();
                            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($students as $student) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($student['student_ID'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($student['stud_section'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($student['full_name'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($student['ojt_status'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($student['stud_hte'] ?? 'Not Assigned') . '</td>';
                                echo '<td>' . htmlspecialchars($student['company_address'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($student['phone_number'] ?? 'N/A') . '</td>';
                                echo '<td class="action-buttons">
                                        <button class="action-button view-profile-btn" data-id="' . htmlspecialchars($student['id']) . '">View Profile</button>
                                      </td>';
                                echo '</tr>';
                            }

                            if (empty($students)) {
                                echo '<tr><td colspan="8">No students found.</td></tr>';
                            }
                        } catch (PDOException $e) {
                            error_log("Database error: " . $e->getMessage());
                            echo '<tr><td colspan="8">Error fetching student data.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $("#userSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $(".user-table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            $(document).on("click", ".view-profile-btn", function() {
                var id = $(this).data("id");
                window.location.href = "admin_view_student_profile.php?id=" + id;
            });
        });
    </script>
</body>
</html>