<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP ITECH Admin Navigation</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
        
        @media screen and (max-width: 768px) {
            .sidenav {
                width: 100%;
                height: auto;
                position: relative;
                margin-top: 60px;
                padding-top: 10px;
                border-right: none;
                border-bottom: 2px solid rgba(68, 68, 68, 0.66);
            }
            
            .main {
                margin-left: 0;
                padding-top: 20px;
            }
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
                        <div class="dropdown-content" <?php echo (in_array($current_page, ['message_student.php', 'message_supervisor.php', 'message_faculty.php'])) ? 'style="display: block;"' : ''; ?>>
                            <a href="message_student.php" class="<?php echo ($current_page === 'message_student.php') ? 'active' : ''; ?>"><img src="images/student.png" alt="Student Icon"> Student</a>
                            <a href="message_supervisor.php" class="<?php echo ($current_page === 'message_supervisor.php') ? 'active' : ''; ?>"><img src="images/faculty.png" alt="Faculty Icon"> Faculty</a>
                            <a href="message_faculty.php" class="<?php echo ($current_page === 'message_faculty.php') ? 'active' : ''; ?>"><img src="images/supervisor.png" alt="Supervisor Icon"> Supervisor</a>
                        </div>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle"><img src="images/todo.png" alt="To Do Icon"> To Do</a>
                        <div class="dropdown-content" <?php echo (in_array($current_page, ['appointment_meetings.php', 'student_trainee.php', 'working_student_list.php', 'partner_companies.php', 'coordinators_list.php', 'admin_users.php', 'endorsement.php', 'assign_ojt_advisers.php', 'verify_users.php', 'documentation.php', 'coc.php', 'portfolio.php'])) ? 'style="display: block;"' : ''; ?>>
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
                            <a href="assign_ojt_advisers.php" class="<?php echo ($current_page === 'assign_ojt_advisers.php') ? 'active' : ''; ?>"><img src="images/endorsement.png" alt="Endorsement Icon"> Assign OJT Advisers</a>
                            <a href="verify_users.php" class="<?php echo ($current_page === 'verify_users.php') ? 'active' : ''; ?>"><img src="images/internship_docs.png" alt="MOA Icon"> Verify Users</a>
                            <a href="documentation.php" class="<?php echo ($current_page === 'documentation.php') ? 'active' : ''; ?>"><img src="images/internship_docs.png" alt="MOA Icon"> MOA Application</a>
                            <a href="endorsement.php" class="<?php echo ($current_page === 'endorsement.php') ? 'active' : ''; ?>"><img src="images/endorsement.png" alt="Endorsement Icon"> Endorsement Papers</a>
                            <a href="portfolio.php" class="<?php echo ($current_page === 'portfolio.php') ? 'active' : ''; ?>"><img src="images/portfolio.png" alt="Portfolio Icon"> Portfolio</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

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
        });
    </script>

    <script>
        var admin_id = <?php echo $_SESSION['auth_user']['admin_id']; ?>;
        var logoutTimeout;

        function startLogoutTimer() {
            logoutTimeout = setTimeout(function () {
                $.ajax({
                    type: 'POST',
                    url: 'admin_update_status_AutoLogOut.php',
                    data: { admin_id: admin_id },
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
    </script>

    <script>    
        function profile() {
            window.location.href = 'view_admin_profile.php';
        }
        function settings() {
            window.location.href = 'admin_settings.php';
        }
        function logout() {
            window.location.href = 'admin_logout.php';
        }
    </script>
</body>
</html>