<?php
include '../connection/config.php';
error_reporting(E_ALL & ~E_NOTICE); // Show all errors except notices

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
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) {
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "Admin data not found.";
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "Database error: " . $e->getMessage();
}

// Handle update information form submission
if (isset($_POST['updateInfo'])) {
    $adminID = filter_input(INPUT_POST, 'adminID', FILTER_SANITIZE_NUMBER_INT);
    $fname = filter_input(INPUT_POST, 'first_NAME', FILTER_SANITIZE_STRING);
    $mname = filter_input(INPUT_POST, 'middle_NAME', FILTER_SANITIZE_STRING);
    $lname = filter_input(INPUT_POST, 'last_NAME', FILTER_SANITIZE_STRING);
    $c_address = filter_input(INPUT_POST, 'complete_address', FILTER_SANITIZE_STRING);
    $cp_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);

    // Server-side validation
    if (empty($fname) || empty($mname) || empty($lname) || empty($c_address) || empty($cp_number)) {
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "All fields are required.";
    } elseif (!preg_match('/^\d{10,12}$/', $cp_number)) {
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "Phone number must be 10-12 digits.";
    } elseif (strlen($fname) > 70 || strlen($mname) > 70 || strlen($lname) > 70 || strlen($c_address) > 200) {
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "Input exceeds maximum length.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT first_name, middle_name, last_name, complete_address, phone_number FROM admin_account WHERE id = ?");
            $stmt->execute([$adminID]);
            $currentData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($fname !== $currentData['first_name'] ||
                $mname !== $currentData['middle_name'] ||
                $lname !== $currentData['last_name'] ||
                $c_address !== $currentData['complete_address'] ||
                $cp_number !== $currentData['phone_number']) {

                $stmt = $conn->prepare("UPDATE admin_account SET first_name=?, middle_name=?, last_name=?, complete_address=?, phone_number=? WHERE id=?");
                $stmt->execute([$fname, $mname, $lname, $c_address, $cp_number, $adminID]);

                if ($stmt->rowCount() > 0) {
                    date_default_timezone_set('Asia/Manila');
                    $date = date('F / d l / Y');
                    $time = date('g:i A');
                    $logs = 'You successfully updated your information.';

                    $sql2 = $conn->prepare("INSERT INTO system_notification(admin_id, logs, logs_date, logs_time) VALUES (?, ?, ?, ?)");
                    $sql2->execute([$adminID, $logs, $date, $time]);

                    $_SESSION['alert'] = "Success";
                    $_SESSION['status'] = "Update Success";
                } else {
                    $_SESSION['alert'] = "Error";
                    $_SESSION['status'] = "Update Failed";
                }
            } else {
                $_SESSION['alert'] = "Info";
                $_SESSION['status'] = "Nothing has changed.";
            }
        } catch (PDOException $e) {
            $_SESSION['alert'] = "Error";
            $_SESSION['status'] = "Database error: " . $e->getMessage();
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle password update form submission
if (isset($_POST['updatePword'])) {
    $adminID = filter_input(INPUT_POST, 'adminID', FILTER_SANITIZE_NUMBER_INT);
    $cpword = $_POST['Cpword'];
    $npword = $_POST['Npword'];
    $rnpword = $_POST['RNpword'];

    // Server-side validation
    if ($npword !== $rnpword) {
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "New Password and Repeat Password do not match.";
    } elseif (strlen($npword) > 200) {
        $_SESSION['alert'] = "Error";
        $_SESSION['status'] = "New password exceeds maximum length.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT admin_password FROM admin_account WHERE id = ?");
            $stmt->execute([$adminID]);
            $currentData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (md5($cpword) === $currentData['admin_password']) { // WARNING: MD5 is insecure; consider using password_hash in production
                $hashedPassword = md5($npword);
                $stmt = $conn->prepare("UPDATE admin_account SET admin_password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $adminID]);

                if ($stmt->rowCount() > 0) {
                    date_default_timezone_set('Asia/Manila');
                    $date = date('F / d l / Y');
                    $time = date('g:i A');
                    $logs = 'You successfully updated your password.';

                    $sql2 = $conn->prepare("INSERT INTO system_notification(admin_id, logs, logs_date, logs_time) VALUES (?, ?, ?, ?)");
                    $sql2->execute([$adminID, $logs, $date, $time]);

                    $_SESSION['alert'] = "Success";
                    $_SESSION['status'] = "Password Updated!";
                } else {
                    $_SESSION['alert'] = "Error";
                    $_SESSION['status'] = "Password update failed.";
                }
            } else {
                $_SESSION['alert'] = "Error";
                $_SESSION['status'] = "Incorrect current password.";
            }
        } catch (PDOException $e) {
            $_SESSION['alert'] = "Error";
            $_SESSION['status'] = "Database error: " . $e->getMessage();
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OJT Web Portal: Admin Settings</title>
    <link rel="shortcut icon" href="./images/pupLogo.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/menubar/sidebar.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="endorsement-css/endorsement-moa.css" rel="stylesheet">
    <style>
        .header-icon {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            position: relative;
        }
        
        .avatar-trigger {
            display: flex;
            align-items: center;
        }
        
        .user-name {
            font-weight: 500;
            color: #000;
        }
        
        .avatar-img {
            height: 40px;
            width: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .nav-1 {
            font-family: 'Source Serif 4', serif;
            background: #fff;
            border-bottom: 2px solid rgba(68, 68, 68, 0.66);
            color: #D11010;
            text-align: left;
            align-items: center;
            font-size: 20px;
            font-weight: 400;
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            display: flex;
            align-items: left;
            margin-bottom: 20px;
            background-clip: padding-box;
            z-index: 1000;
        }

        .nav-logo {
            height: 50px;
            margin-left: 20px;
        }

        .nav-title-caption-container {
            display: flex;
            margin-left: 20px;
        }

        .nav-title {
            font-size: 24px;
            font-weight: bold;
        }

        .sidenav {
            width: 15%;
            background: #fff;
            border-right: 2px solid rgba(68, 68, 68, 0.66);
            height: 100%;
            top: 0;
            position: fixed; 
            padding-top: 20px;
            padding-right: 20px;
            margin-top: 70px;
            margin-left: -10px;
            z-index: 1;
        }

        .sidenav img {
            height: 20px;
            margin-right: 10px;
            filter: brightness(0) invert(0);
        }
        
        .sidenav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .sidenav a {
            padding: 10px 16px;
            text-decoration: none;
            font-size: 14px;
            color: rgb(0, 0, 0);
            background-color: #fff;
            display: flex;
            align-items: center;
        }

        .sidenav a:active {
            color: #f1f1f1!important;
            background-color: #700000!important;
        }

        .sidenav a:hover {
            color: #f1f1f1;
            background-color: #700000;
            border-radius: 0px 20px 20px 0px;
        }

        .sidenav a:hover img {
            filter: brightness(0) invert(1);
        }
        
        .sidenav a.active {
            color: #f1f1f1;
            background-color: #700000;
            border-radius: 0px 20px 20px 0px;
        }
        
        .sidenav a.active img {
            filter: brightness(0) invert(1);
        }
        
        .dropdown-toggle {
            align-items: center;
            width: 90%;
        }

        .dropdown-toggle.active {
            color: #f1f1f1;
            background-color: #700000;
        }

        .sidenav .dropdown-toggle.active {
            background-color: #700000 !important;
        }
        
        .dropdown-toggle.active img {
            filter: brightness(0) invert(1);
        }
        
        .dropdown-arrow {
            margin-left: auto;
            font-size: 12px;
        }

        .user {
            margin-right: 20px;
            margin-left: auto;
        }
        
        .main {
            margin-left: 160px; 
            padding: 0px 10px;
        }   

        .user-info {
            display: flex; 
            flex-direction: column; 
            color: #000; 
            align-items: center; 
            justify-content: center; 
            margin-right: 20px; 
            font-family: 'Source Serif 4', serif; 
            font-size: 14px; 
        }

        .user-name {
            margin-bottom: 10px;
        }

        .dropdown-content {
            display: none;
            min-width: 100%;
            padding: 0;
            z-index: 1;
        }
        
        .dropdown-content a {
            padding: 8px 16px 8px 24px;
            text-decoration: none;
            font-size: 13px;
            color: rgb(0, 0, 0);
            display: flex;
            align-items: center;
        }

        .dropdown-content a:hover {
            color: #f1f1f1;
            background-color: #700000;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            background-color: #fff;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            top: 100%;
            right: 0;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-content-body ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .dropdown-content-body li {
            display: flex;
            align-items: center;
        }

        .dropdown-content-body a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            text-decoration: none;
            color: #000;
            font-size: 14px;
            width: 100%;
        }

        .dropdown-content-body a:hover {
            background-color: #f1f1f1;
        }

        .dropdown-content-body a:hover i.material-icons {
            color: #000;
        }

        .dropdown-icon {
            font-size: 20px;
            margin-right: 10px;
            color: #000;
        }
        
        .btn-download {
            display: block;
            margin-bottom: 1rem;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .hidden {
            display: none;
        }
        .document-area {
            width: 100%;
            height: 500px;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }
        .document-area iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
        .document-area.enlarged {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 80%;
            z-index: 1000;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .overlay.active {
            display: block;
        }
        .portfolio-requirements {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .portfolio-requirements h3 {
            margin-top: 0;
            color: #333;
            font-size: 1.2rem;
        }
        .portfolio-requirements ul {
            padding-left: 20px;
            margin-bottom: 0;
        }
        .portfolio-requirements li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <nav class="nav-1">
        <img src="images/pupLogo.png" alt="PUP Logo" class="nav-logo">
        <div class="nav-title-caption-container">
            <div class="nav-title">Polytechnic University of the Philippines-ITECH</div>
        </div>
        <div class="user">
            <div class="header-icon">
                <div class="avatar-trigger" data-toggle="dropdown">
                    <?php
                    if (isset($_SESSION['auth_user']['admin_id'])) {
                        $adminID = $_SESSION['auth_user']['admin_id'];
                        $stmt = $conn->prepare("SELECT first_name, last_name, id_number, admin_profile_picture FROM admin_account WHERE id = ?");
                        $stmt->execute([$adminID]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $profileImage = $result['admin_profile_picture'] ? $result['admin_profile_picture'] : 'admin/images/profile.png';
                    }
                    ?>
                    
                    <div class="user-info">
                        <span class="user-name">
                            <?php echo isset($result['first_name']) ? htmlspecialchars($result['first_name'] . ' ' . $result['last_name']) : 'Admin'; ?>
                        </span>
                        <span class="schoolID">
                            <?php echo isset($result['id_number']) ? htmlspecialchars($result['id_number']) : 'N/A'; ?>
                        </span>
                    </div>

                    <?php if (isset($profileImage)): ?>
                        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="User Avatar" class="avatar-img">
                    <?php else: ?>
                        <span>No Image</span>
                    <?php endif; ?>
                </div>
                <div class="drop-down dropdown-profile dropdown-menu dropdown-menu-right">
                    <div class="dropdown-content-body">
                        <ul>
                            <li>
                                <a href="#" onclick="profile();">
                                    <i class="material-icons dropdown-icon">person</i>
                                    <span>Profile</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" onclick="settings();">
                                    <i class="material-icons dropdown-icon">settings</i>
                                    <span>Setting</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" onclick="logout();">
                                    <i class="material-icons dropdown-icon">logout</i>
                                    <span>Logout</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>		
    </nav>
    <div>
        <div>
            <div class="sidenav">
                <?php
                // Get the current page filename
                $current_page = basename($_SERVER['PHP_SELF']);
                ?>
                <ul>
                    <li><a href="dashboard.php" class="<?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>"><img src="images/home.png" alt="Home Icon"> Home</a></li>
                    <li><a href="view_admin_profile.php" class="<?php echo ($current_page === 'view_admin_profile.php') ? 'active' : ''; ?>"><img src="images/profile.png" alt="Profile Icon"> Profile</a></li>
                    <li><a href="notification.php" class="<?php echo ($current_page === 'notification.php') ? 'active' : ''; ?>"><img src="images/notification.png" alt="Notifications Icon"> Notifications</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle"><img src="images/message.png" alt="Messages Icon"> Messages</a>
                        <div class="dropdown-content" <?php echo (in_array($current_page, ['stud_message.php', 'chat_supervisor.php', 'chat_faculty.php', 'chat_admin.php'])) ? 'style="display: block;"' : ''; ?>>
                            <a href="stud_message.php" class="<?php echo ($current_page === 'stud_message.php') ? 'active' : ''; ?>"><img src="images/student.png" alt="Student Icon"> Student</a>
                            <a href="chat_supervisor.php" class="<?php echo ($current_page === 'chat_supervisor.php') ? 'active' : ''; ?>"><img src="images/faculty.png" alt="Faculty Icon"> Faculty</a>
                            <a href="chat_faculty.php" class="<?php echo ($current_page === 'chat_faculty.php') ? 'active' : ''; ?>"><img src="images/supervisor.png" alt="Supervisor Icon"> Supervisor</a>
                            <a href="chat_admin.php" class="<?php echo ($current_page === 'chat_admin.php') ? 'active' : ''; ?>"><img src="images/admin.png" alt="Admin Icon"> Admin</a>
                        </div>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle"><img src="images/todo.png" alt="To Do Icon"> To Do</a>
                        <div class="dropdown-content" <?php echo (in_array($current_page, ['appointment_meetings.php', 'student_trainee.php', 'working_student_list.php', 'partner_companies.php', 'coordinators_list.php', 'admin_users.php', 'endorsement.php', 'documentation.php', 'coc.php', 'portfolio.php'])) ? 'style="display: block;"' : ''; ?>>
                            <a href="appointment_meetings.php" class="<?php echo ($current_page === 'appointment_meetings.php') ? 'active' : ''; ?>"><img src="images/search.png" alt="Appointment Meetings Icon"> Appointment Meetings</a>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle"><img src="images/user.png" alt="User List Icon"> User List</a>
                                <div class="dropdown-content" <?php echo (in_array($current_page, ['student_trainee.php', 'working_student_list.php', 'partner_companies.php', 'coordinators_list.php', 'admin_users.php'])) ? 'style="display: block;"' : ''; ?>>
                                    <a href="student_trainee.php" class="<?php echo ($current_page === 'student_trainee.php') ? 'active' : ''; ?>"><img src="images/student.png" alt="Student Users Icon"> Student Users</a>
                                    <a href="working_student_list.php" class="<?php echo ($current_page === 'working_student_list.php') ? 'active' : ''; ?>"><img src="images/student.png" alt="Working Student Users Icon"> Working Student List</a>
                                    <a href="partner_companies.php" class="<?php echo ($current_page === 'partner_companies.php') ? 'active' : ''; ?>"><img src="images/supervisor.png" alt="HTE Icon"> HTE</a>
                                    <a href="coordinators_list.php" class="<?php echo ($current_page === 'coordinators_list.php') ? 'active' : ''; ?>"><img src="images/faculty.png" alt="Advisers Icon"> Advisers</a>
                                    <a href="admin_users.php" class="<?php echo ($current_page === 'admin_users.php') ? 'active' : ''; ?>"><img src="images/admin.png" alt="Admins Icon"> Admins</a>
                                </div>
                            </div>
                            <a href="documentation.php" class="<?php echo ($current_page === 'documentation.php') ? 'active' : ''; ?>"><img src="images/internship_docs.png" alt="MOA Icon"> MOA Application</a>
                            <a href="endorsement.php" class="<?php echo ($current_page === 'endorsement.php') ? 'active' : ''; ?>"><img src="images/endorsement.png" alt="Endorsement Icon"> Endorsement Applications</a>
                            <a href="portfolio.php" class="<?php echo ($current_page === 'portfolio.php') ? 'active' : ''; ?>"><img src="images/portfolio.png" alt="Portfolio Icon"> Portfolio</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto; position: relative;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="page-header">
                <div class="page-title">
                    <h1 style="font-size: 16px;"><b>Settings</b></h1><br>
                </div>
            </div>
            <div>
                <div style="margin-bottom: 30px;">
                    <p style="color: #666; margin-bottom: 20px;">
                        <strong>Note:</strong> You are only allowed to edit some of your basic information. If you notice any incorrect non-editable information, kindly contact the system administrator for assistance.
                    </p>

                    <form id="updateInfoForm" action="" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="adminID" value="<?php echo htmlspecialchars($data['id']); ?>" required>

                        <!-- First row: First name, Middle Initial, Last name -->
                        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                            <div style="flex: 1;">
                                <label for="first_NAME" style="display: block; margin-bottom: 5px; font-weight: normal;">First Name</label>
                                <input type="text" class="form-control" name="first_NAME" id="first_NAME" value="<?php echo htmlspecialchars($data['first_name']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                            </div>
                            <div style="flex: 1;">
                                <label for="middle_NAME" style="display: block; margin-bottom: 5px; font-weight: normal;">Middle Initial</label>
                                <input type="text" class="form-control" name="middle_NAME" id="middle_NAME" value="<?php echo htmlspecialchars($data['middle_name']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                            </div>
                            <div style="flex: 1;">
                                <label for="last_NAME" style="display: block; margin-bottom: 5px; font-weight: normal;">Last Name</label>
                                <input type="text" class="form-control" name="last_NAME" id="last_NAME" value="<?php echo htmlspecialchars($data['last_name']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                            </div>
                        </div>

                        <!-- Second row: Complete Address, Phone Number -->
                        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                            <div style="flex: 1;">
                                <label for="phone_number" style="display: block; margin-bottom: 5px; font-weight: normal;">Phone Number</label>
                                <input type="text" class="form-control" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($data['phone_number']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required pattern="\d{10,12}">
                            </div>
                        </div>

                        <button type="submit" name="updateInfo" id="updateInfoButton" style="background-color: #0000FF; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Update changes</button>
                    </form>
                </div>

                <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #eee;">
                    <h2 style="font-size: 18px; margin-bottom: 20px;">Update Password</h2>
                    <form id="updatePwordForm" action="" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="adminID" value="<?php echo htmlspecialchars($data['id']); ?>" required>

                        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                            <div style="flex: 1;">
                                <label for="Cpword" style="display: block; margin-bottom: 5px; font-weight: normal;">Current Password</label>
                                <input type="password" class="form-control" name="Cpword" id="Cpword" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div style="flex: 1;">
                                <label for="Npword" style="display: block; margin-bottom: 5px; font-weight: normal;">New Password</label>
                                <input type="password" class="form-control" name="Npword" id="Npword" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                            <div style="flex: 1;">
                                <label for="RNpword" style="display: block; margin-bottom: 5px; font-weight: normal;">Repeat New Password</label>
                                <input type="password" class="form-control" name="RNpword" id="RNpword" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            </div>
                        </div>

                        <button type="submit" name="updatePword" id="updatePwordButton" style=" background-color: #0000FF; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="overlay" id="overlay"></div>

    <script src="js/lib/jquery.min.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/menubar/sidebar.js"></script>
    <script src="js/lib/preloader/pace.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="js/lib/form-validation/jquery.validate.min.js"></script>
    <script src="js/lib/form-validation/jquery.validate-init.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Sidebar dropdown
            const $dropdownToggles = $(".dropdown-toggle");
            
            // Initialize sidebar dropdowns
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

            // Close profile dropdown when clicking outside
            $(document).click(function() {
                $(".dropdown-menu").removeClass("show");
            });

            // Initialize form validation for Update Info
            $('#updateInfoForm').validate({
                rules: {
                    first_NAME: { required: true, maxlength: 70 },
                    middle_NAME: { required: true, maxlength: 70 },
                    last_NAME: { required: true, maxlength: 70 },
                    complete_address: { required: true, maxlength: 200 },
                    phone_number: { required: true, pattern: /^\d{10,12}$/ }
                },
                messages: {
                    first_NAME: { required: "First name is required.", maxlength: "First name cannot exceed 70 characters." },
                    middle_NAME: { required: "Middle initial is required.", maxlength: "Middle initial cannot exceed 70 characters." },
                    last_NAME: { required: "Last name is required.", maxlength: "Last name cannot exceed 70 characters." },
                    complete_address: { required: "Address is required.", maxlength: "Address cannot exceed 200 characters." },
                    phone_number: { required: "Phone number is required.", pattern: "Phone number must be 10-12 digits." }
                },
                errorElement: 'div',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('div').append(error);
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                }
            });

            // Function to check if all required fields in Update Info form are filled
            function checkUpdateInfoFields() {
                const fields = [
                    $('#first_NAME').val(),
                    $('#middle_NAME').val(),
                    $('#last_NAME').val(),
                    $('#complete_address').val(),
                    $('#phone_number').val()
                ];
                const allFilled = fields.every(field => field && field.trim() !== '');
                const phoneValid = $('#phone_number').val().match(/^\d{10,12}$/);
                $('#updateInfoButton').css('background-color', allFilled && phoneValid ? '#007bff' : '#888');
            }

            // Function to check if all fields in Update Password form are filled
            function checkUpdatePwordFields() {
                const fields = [
                    $('#Cpword').val(),
                    $('#Npword').val(),
                    $('#RNpword').val()
                ];
                const allFilled = fields.every(field => field !== '');
                const passwordsMatch = $('#Npword').val() === $('#RNpword').val();
                $('#updatePwordButton').css('background-color', allFilled && passwordsMatch ? '#007bff' : '#888');
            }

            // Bind input events to check fields
            $('#updateInfoForm input').on('input', checkUpdateInfoFields);
            $('#updatePwordForm input').on('input', checkUpdatePwordFields);

            // Initial check on page load
            checkUpdateInfoFields();
            checkUpdatePwordFields();
        });

        var adminId = <?php echo $_SESSION['auth_user']['admin_id']; ?>;
        var logoutTimeout;

        function startLogoutTimer() {
            logoutTimeout = setTimeout(function () {
                $.ajax({
                    type: 'POST',
                    url: 'admin_update_status_AutoLogOut.php',
                    data: { admin_id: adminId },
                    success: function (response) {
                        window.location.href = 'index.php';
                    },
                    error: function (xhr, status, error) {
                        console.error('Auto-logout error:', error);
                    }
                });
            }, 360000); // 6 minutes
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

        <?php 
        if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
        ?>
            alert("<?php echo $_SESSION['alert'] . ': ' . $_SESSION['status']; ?>");
            <?php
            unset($_SESSION['status']);
            unset($_SESSION['alert']);
        }
        ?>
    </script>
</body>
</html>