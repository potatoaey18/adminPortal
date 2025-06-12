<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if ($_SESSION['auth_user']['userid'] == 0) {
    echo "<script>window.location.href='index.php'</script>";
} else {
    // Fetch admin's first name
    $userid = $_SESSION['auth_user']['userid'];
    $query = "SELECT first_name FROM admin_account WHERE id = :userid";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':userid', $userid, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $first_name = isset($result['first_name']) ? $result['first_name'] : "Guest";

    // Fetch total number of students
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_students FROM students_data");
    if (!$stmt->execute()) {
        die("Error fetching total students: " . print_r($stmt->errorInfo(), true));
    }
    $total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total_students'];

    // Fetch distinct courses and counts for Trainee Per Course chart
    $stmt = $conn->prepare("SELECT DISTINCT stud_course FROM students_data");
    if (!$stmt->execute()) {
        die("Error fetching distinct courses: " . print_r($stmt->errorInfo(), true));
    }
    $courses = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $conn->prepare("SELECT stud_course, COUNT(*) AS course_count 
                            FROM students_data 
                            GROUP BY stud_course");
    if (!$stmt->execute()) {
        die("Error fetching course counts: " . print_r($stmt->errorInfo(), true));
    }
    $course_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Color mapping for courses
    $courseColors = [
        'Diploma in Information Technology (DIT)' => '#4A90E2',
        'Diploma in Office Management Technology (DOMT)' => '#A9A9A9',
        'Diploma in Civil Engineering Technology (DCvET)' => '#87CEEB',
        'Diploma in Railway Engineering Technology (DRET)' => '#2ECC71',
        'Diploma in Computer Engineering Technology (DCET)' => '#9966FF',
        'Diploma in Electrical Engineering Technology (DEET)' => '#FF6384',
        'Diploma in Electronics Engineering Technology (DECET)' => '#4BC0C0',
        'Diploma in Mechanical Engineering Technology (DMET)' => '#FFCE56',
    ];

    $defaultColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];
    $colorIndex = 0;

    // Prepare Trainee Per Course chart data
    $courseLabels = [];
    $courseData = [];
    $courseColorsArray = [];

    foreach ($course_data as $row) {
        $course = $row['stud_course'];
        $count = $row['course_count'];
        $percentage = $total_students > 0 ? ($count / $total_students) * 100 : 0;

        $courseLabels[] = $course;
        $courseData[] = round($percentage, 2);
        $courseColorsArray[] = isset($courseColors[$course]) ? $courseColors[$course] : $defaultColors[$colorIndex % count($defaultColors)];
        $colorIndex++;
    }

    // Fetch distinct ojt_status and counts for Trainee Status chart
    $stmt = $conn->prepare("SELECT DISTINCT ojt_status FROM students_data WHERE ojt_status IS NOT NULL");
    if (!$stmt->execute()) {
        die("Error fetching distinct statuses: " . print_r($stmt->errorInfo(), true));
    }
    $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $conn->prepare("SELECT ojt_status, COUNT(*) AS status_count 
                            FROM students_data 
                            WHERE ojt_status IS NOT NULL 
                            GROUP BY ojt_status");
    if (!$stmt->execute()) {
        die("Error fetching status counts: " . print_r($stmt->errorInfo(), true));
    }
    $status_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Color mapping for statuses
    $statusColors = [
        'Not Yet Deployed' => '#4A90E2',
        'Deployed' => '#A9A9A9',
        'Completed' => '#87CEEB',
        'Drop' => '#2ECC71',
        'In Progress' => '#F39C12',
    ];

    $colorIndex = 0;

    // Prepare Trainee Status chart data
    $statusLabels = [];
    $statusData = [];
    $statusColorsArray = [];

    foreach ($status_data as $row) {
        $status = $row['ojt_status'];
        $count = $row['status_count'];
        $percentage = $total_students > 0 ? ($count / $total_students) * 100 : 0;

        $statusLabels[] = $status;
        $statusData[] = round($percentage, 2);
        $statusColorsArray[] = isset($statusColors[$status]) ? $statusColors[$status] : $defaultColors[$colorIndex % count($defaultColors)];
        $colorIndex++;
    }

    // Fetch announcements and FAQs by portal
    $portals = ['Student', 'Adviser', 'HTE', 'All'];
    $announcements = [];
    $faqs = [];

    foreach ($portals as $portal) {
        $stmt = $conn->prepare("SELECT * FROM announcements WHERE portal = ? OR portal = 'All' ORDER BY created_at DESC");
        $stmt->execute([$portal]);
        $announcements[$portal] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("SELECT * FROM faqs WHERE portal = ? OR portal = 'All' ORDER BY created_at DESC");
        $stmt->execute([$portal]);
        $faqs[$portal] = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>OJT Web Portal: Admin Dashboard</title>
    <link href="css/lib/calendar2/pignose.calendar.min.css" rel="stylesheet">
    <link href="css/lib/chartist/chartist.min.css" rel="stylesheet">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/owl.carousel.min.css" rel="stylesheet" />
    <link href="css/lib/owl.theme.default.min.css" rel="stylesheet" />
    <link href="css/lib/weather-icons.css" rel="stylesheet" />
    <link href="css/lib/menubar/sidebar.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/lib/sweetalert/sweetalert.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {box-sizing: border-box}

        .slideshow-container {
            max-width: 1000px;
            position: relative;
            margin: auto;
        }

        .mySlides {
            display: none;
        }

        .dot {
            cursor: pointer;
            height: 10px;
            width: 10px;
            margin: 0 2px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.6s ease;
        }

        .active, .dot:hover {
            background-color: #717171;
        }

        .fade {
            animation-name: fade;
            animation-duration: 1.5s;
        }

        @keyframes fade {
            from {opacity: .4}
            to {opacity: 1}
        }

        .dashboard {
            max-width: 62.5rem;
            margin: 0 auto;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 150px;
        }

        .dashboard-content {
            min-width: 150px;
            min-height: 150px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .dashboard-content img {
            height: 50px;
            display: block;
            margin: 0 auto;
        }

        .dashboard > :nth-child(1) {
            border: 3px solid #0054B2;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 4px 4px 8px rgba(0, 0, 0, 0.8);
        }

        .dashboard > :nth-child(2) {
            border: 3px solid #DA7700;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 4px 4px 8px rgba(0, 0, 0, 0.8);
        }

        .dashboard > :nth-child(3) {
            border: 3px solid #EAE100;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 4px 4px 8px rgba(0, 0, 0, 0.8);
        }

        .stat {
            align-self: center;
            justify-self: center;
        }

        .deadline-container {
            width: 300px;
            border-radius: 10px;
            overflow: hidden;
            font-family: 'Source Serif 4', serif;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
        }

        .header {
            background-color: #8B0000;
            color: #ffffff;
            padding: 15px 20px;
            font-size: 18px;
            position: relative;
            margin: 0 auto;
            text-align: center;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            width: 8px;
            height: 8px;
            background-color: black;
            border-radius: 50%;
        }

        .content {
            background-color: #f0f0f0;
            color: #000000;
            padding: 30px 20px;
            text-align: center;
            font-size: 16px;
        }

        .faq-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .faq-item {
            background-color: white;
            margin: 20px 0;
            border-radius: 5px;
            overflow: hidden;
            transition: all 0.8s ease;
        }

        .faq-header {
            padding: 15px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            background-color: rgb(104, 104, 104);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
        }

        .faq-header:hover {
            background-color: rgb(152, 152, 152);
            color: #000;
        }

        .faq-header span {
            font-size: 24px;
            font-weight: 200;
            transition: transform 0.3s ease;
        }

        .faq-item.active .faq-header span {
            transform: rotate(180deg);
        }

        .faq-content {
            padding: 15px;
            display: block;
            background-color: rgb(255, 255, 255);
            font-size: 16px;
            color: #000;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.8s ease, opacity 0.8s ease;
        }

        .faq-item.active .faq-content {
            max-height: 1000px;
            opacity: 1;
        }

        .manage-section {
            max-width: 1000px;
            margin: 20px auto;
        }

        .manage-section h2 {
            font-size: 16px;
            color: #700000;
            margin-left: 5rem;
        }

        .item-list {
            margin-top: 20px;
        }

        .item-list .item {
            background-color: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .action-button {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-button i {
            font-size: 16px;
        }

        .edit-announcement, .edit-faq {
            background-color: #8B0000;
            color: white;
        }

        .edit-announcement:hover, .edit-faq:hover {
            background-color: #700000;
            transform: translateY(-2px);
        }

        .delete-announcement, .delete-faq {
            background-color: #d32f2f;
            color: white;
        }

        .delete-announcement:hover, .delete-faq:hover {
            background-color: #b71c1c;
            transform: translateY(-2px);
        }

        .action-button:focus {
            outline: 2px solid #700000;
            outline-offset: 2px;
        }

        .action-button[aria-label] {
            position: relative;
        }

        .action-button[aria-label]:hover:after {
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

        .edit-button {
            background-color: #8B0000;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 5rem;
        }

        .edit-button:hover {
            background-color: #700000;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            text-align: center;
        }

        .modal-content h2 {
            color: #700000;
            margin-bottom: 20px;
        }

        .modal-content button {
            background-color: #8B0000;
            color: white;
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal-content button:hover {
            background-color: #700000;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .deadline-container {
                width: 100%;
                max-width: 300px;
            }
            .manage-section {
                margin-left: 1rem;
                margin-right: 1rem;
            }
            .manage-section h2 {
                margin-left: 1rem;
            }
            .edit-button {
                margin-left: 1rem;
            }
        }
    </style>
</head>

<body>
<?php require_once 'templates/admin_navbar.php'; ?>
<div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
    <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
        <div>
            <div>
                <div>
                    <div class="page-header">
                        <div class="page-title">
                            <h1 style="font-size: 16px;">HOME</h1>
                        </div>
                    </div>
                </div>
            </div>
            <section id="main-content">
                <!-- Slideshow container -->
                <br><br>
                <div class="slideshow-container" style="position: relative;">
                    <div class="mySlides fade" style="position: relative;">
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: #700000; opacity: 0.5; z-index: 1; border-radius: 40px;"></div>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #FABC3F; font-size: 48px; z-index: 2; text-align: center; font-family: 'Source Serif 4', serif">
                            Iskolar ng Bayan!
                        </div>
                        <img src="images/pup-carousel.jpg" style="height: 50%; width: 100%; position: relative; z-index: 0; border-radius: 40px;">
                    </div>
                    <div class="mySlides fade" style="position: relative;">
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: #700000; opacity: 0.5; z-index: 1; border-radius: 40px;"></div>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #FABC3F; font-size: 48px; z-index: 2; text-align: center; font-family: 'Source Serif 4', serif">
                            Iskolar ng Bayan!
                        </div>
                        <img src="images/pup-carousel.jpg" style="height: 50%; width: 100%; position: relative; z-index: 0; border-radius: 40px;">
                    </div>
                    <div class="mySlides fade" style="position: relative;">
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: #700000; opacity: 0.5; z-index: 1; border-radius: 40px;"></div>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #FABC3F; font-size: 48px; z-index: 2; text-align: center; font-family: 'Source Serif 4', serif">
                            Iskolar ng Bayan!
                        </div>
                        <img src="images/pup-carousel.jpg" style="height: 50%; width: 100%; position: relative; z-index: 0; border-radius: 40px;">
                    </div>
                    <div style="position: absolute; bottom: 10%; left: 50%; transform: translateX(-50%); text-align: center;">
                        <span class="dot" onclick="currentSlide(1)"></span>
                        <span class="dot" onclick="currentSlide(2)"></span>
                        <span class="dot" onclick="currentSlide(3)"></span>
                    </div>
                </div>
                <br><br>
                <div class="page-title">
                    <h1 style="font-size: 16px; color: #700000; margin-left: 5rem;">DASHBOARD</h1>
                </div>
                <br><br>
                <div class="row dashboard">
                    <div>
                        <div>
                            <div class="dashboard-content">
                                <div>
                                    <img src="images/profile.png" alt="Students Icon">
                                </div>
                                <div>
                                    <?php $stmt = $conn->prepare("SELECT COUNT(*) AS student_count FROM students_data");
                                    $stmt->execute();
                                    $student_count = $stmt->fetch(PDO::FETCH_ASSOC)['student_count'];
                                    ?>
                                    <div>Students</div>
                                    <div class="stat"><?php echo $student_count; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div>
                            <div class="dashboard-content">
                                <div>
                                    <img src="images/profile.png" alt="Faculty Icon">
                                </div>
                                <div>
                                    <?php $stmt = $conn->prepare("SELECT COUNT(*) AS faculty_count FROM coordinators_account");
                                    $stmt->execute();
                                    $faculty_count = $stmt->fetch(PDO::FETCH_ASSOC)['faculty_count'];
                                    ?>
                                    <div>Faculty</div>
                                    <div class="stat"><?php echo $faculty_count; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div>
                            <div class="dashboard-content">
                                <div>
                                    <img src="images/profile.png" alt="HTE Icon">
                                </div>
                                <div>
                                    <?php $stmt = $conn->prepare("SELECT COUNT(*) AS hte_count FROM supervisor");
                                    $stmt->execute();
                                    $hte_count = $stmt->fetch(PDO::FETCH_ASSOC)['hte_count'];
                                    ?>
                                    <div>HTE</div>
                                    <div class="stat"><?php echo $hte_count; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br><br>
                <div class="page-title">
                    <h1 style="font-size: 16px; color: #700000; margin-left: 5rem;">TRAINEE PER COURSE</h1>
                </div>
                <br><br>
                <div style="max-width: 600px; margin: 0 auto;">
                    <canvas id="traineePerCourseChart"></canvas>
                </div>
                <br><br>
                <div class="page-title">
                    <h1 style="font-size: 16px; color: #700000; margin-left: 5rem;">TRAINEE STATUS</h1>
                </div>
                <br><br>
                <div style="max-width: 600px; margin: 0 auto;">
                    <canvas id="traineeStatusChart"></canvas>
                </div>
                <br><br>
                <div class="page-title">
                    <h1 style="font-size: 16px; color: #700000; margin-left: 5rem;">ANNOUNCEMENTS</h1>
                </div>
                <br><br>
                <button class="edit-button" onclick="openModal('announcement')">Add New Announcement</button>
                <br><br>
                <?php foreach ($portals as $portal): ?>
                    <?php if (!empty($announcements[$portal])): ?>
                        <div class="manage-section">
                            <h2><?php echo $portal; ?> Portal</h2>
                            <div style="max-width: 62.5rem; margin: 0 auto; display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 80px; flex-wrap: wrap;">
                                <?php foreach ($announcements[$portal] as $announcement): ?>
                                    <div class="deadline-container" data-announcement-id="<?php echo $announcement['id']; ?>">
                                        <div class="header">
                                            <?php echo htmlspecialchars($announcement['title']); ?>
                                        </div>
                                        <div class="content">
                                            <?php echo htmlspecialchars($announcement['content']); ?>
                                        </div>
                                        <div class="item-actions" style="padding: 10px; display: flex; justify-content: center; gap: 10px;">
                                            <button class="action-button edit-announcement" 
                                                    data-id="<?php echo $announcement['id']; ?>" 
                                                    data-portal="<?php echo $portal; ?>" 
                                                    aria-label="Edit Announcement">
                                                <i class="ti-pencil"></i> Edit
                                            </button>
                                            <button class="action-button delete-announcement" 
                                                    data-id="<?php echo $announcement['id']; ?>" 
                                                    aria-label="Delete Announcement">
                                                <i class="ti-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <br><br>
                <?php endforeach; ?>
                <br><br>
                <div class="page-title" style="display: flex; align-items: center; margin-left: 5rem;">
                    <img src="images/faqs.png" alt="faqs" style="margin-right: 10px;">
                    <h1 style="font-size: 16px; color: #700000; margin: 0;">FAQs</h1>
                </div>
                <br><br>
                <button class="edit-button" onclick="openModal('faq')">Add New FAQ</button>
                <br><br>
                <?php foreach ($portals as $portal): ?>
                    <?php if (!empty($faqs[$portal])): ?>
                        <div class="manage-section">
                            <h2><?php echo $portal; ?> Portal</h2>
                            <div class="faq-container">
                                <?php foreach ($faqs[$portal] as $faq): ?>
                                    <div class="faq-item" data-faq-id="<?php echo $faq['id']; ?>">
                                        <div class="faq-header"><?php echo htmlspecialchars($faq['question']); ?><span>v</span></div>
                                        <div class="faq-content">
                                            <?php echo htmlspecialchars($faq['answer']); ?>
                                            <div class="item-actions" style="padding: 10px; display: flex; justify-content: center; gap: 10px;">
                                                <button class="action-button edit-faq" 
                                                        data-id="<?php echo $faq['id']; ?>" 
                                                        data-portal="<?php echo $portal; ?>" 
                                                        aria-label="Edit FAQ">
                                                    <i class="ti-pencil"></i> Edit
                                                </button>
                                                <button class="action-button delete-faq" 
                                                        data-id="<?php echo $faq['id']; ?>" 
                                                        aria-label="Delete FAQ">
                                                    <i class="ti-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <br><br>
                
            </section>
        </div>
    </div>
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <h2>Choose the user site you want to edit</h2>
            <button onclick="window.location.href='edit_content_form.php?type=' + modalType + '&portal=Student'">Student</button>
            <button onclick="window.location.href='edit_content_form.php?type=' + modalType + '&portal=Adviser'">Adviser</button>
            <button onclick="window.location.href='edit_content_form.php?type=' + modalType + '&portal=HTE'">HTE</button>
            <button onclick="window.location.href='edit_content_form.php?type=' + modalType + '&portal=All'">All</button>
        </div>
    </div>
    <script src="js/lib/jquery.min.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/preloader/pace.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/lib/sweetalert/sweetalert.min.js"></script>
    <script src="js/lib/sweetalert/sweetalert.init.js"></script>
    <script>
        let slideIndex = 0;
        let modalType = '';
        showSlides();

        function showSlides() {
            let slides = document.getElementsByClassName("mySlides");
            let dots = document.getElementsByClassName("dot");
            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slideIndex++;
            if (slideIndex > slides.length) { slideIndex = 1 }
            slides[slideIndex - 1].style.display = "block";
            for (let i = 0; i < dots.length; i++) {
                dots[i].className = dots[i].className.replace(" active", "");
            }
            dots[slideIndex - 1].className += " active";
            setTimeout(showSlides, 3000);
        }

        function currentSlide(n) {
            slideIndex = n - 1;
            showSlides();
        }

        const courseCtx = document.getElementById('traineePerCourseChart');
        if (courseCtx) {
            new Chart(courseCtx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($courseLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($courseData); ?>,
                        backgroundColor: <?php echo json_encode($courseColorsArray); ?>,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { font: { size: 14 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    return `${label}: ${value}%`;
                                }
                            }
                        }
                    }
                }
            });
        }

        const statusCtx = document.getElementById('traineeStatusChart');
        if (statusCtx) {
            new Chart(statusCtx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($statusLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($statusData); ?>,
                        backgroundColor: <?php echo json_encode($statusColorsArray); ?>,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { font: { size: 14 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    return `${label}: ${value}%`;
                                }
                            }
                        }
                    }
                }
            });
        }

        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            const header = item.querySelector('.faq-header');
            header.addEventListener('click', () => {
                item.classList.toggle('active');
            });
        });

        function openModal(type) {
            modalType = type;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Event delegation for announcement edit buttons
            document.querySelectorAll('.edit-announcement').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const portal = this.getAttribute('data-portal');
                    window.location.href = `edit_content_form.php?type=announcement&portal=${portal}&id=${id}`;
                });
            });

            // Event delegation for announcement delete buttons
            document.querySelectorAll('.delete-announcement').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This announcement will be permanently deleted.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#8B0000',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `manage_content.php?action=delete_announcement&id=${id}`;
                        }
                    });
                });
            });

            // Event delegation for FAQ edit buttons
            document.querySelectorAll('.edit-faq').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const portal = this.getAttribute('data-portal');
                    window.location.href = `edit_content_form.php?type=faq&portal=${portal}&id=${id}`;
                });
            });

            // Event delegation for FAQ delete buttons
            document.querySelectorAll('.delete-faq').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This FAQ will be permanently deleted.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#8B0000',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `manage_content.php?action=delete_faq&id=${id}`;
                        }
                    });
                });
            });
        });

        <?php 
        if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
        ?>
            sweetAlert("<?php echo $_SESSION['alert']; ?>", "<?php echo $_SESSION['status']; ?>", "<?php echo $_SESSION['status-code']; ?>");
        <?php
            unset($_SESSION['status']);
        }
        ?>
    </script>
</body>
</html>