<?php
include '../connection/config.php';

// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
error_log("Session userid: " . ($_SESSION['auth_user']['userid'] ?? 'Not set'));
if (!isset($_SESSION['auth_user']['userid']) || $_SESSION['auth_user']['userid'] == 0) {
    echo "<script>window.location.href='index.php'</script>";
    exit;
}

// Fetch supervisor data based on ID from URL
$supervisor_id = isset($_GET['supervisor']) ? (int)$_GET['supervisor'] : 0;
if ($supervisor_id <= 0) {
    echo "<script>window.location.href='partner_companies.php'</script>";
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            id AS supervisorID,
            company_name,
            company_address,
            supervisor_email,
            phone_number,
            position,
            date_notarized,
            moa_validity,
            supervisor_profile_picture,
            CONCAT(first_name, ' ', middle_name, ' ', last_name) AS contact_person
        FROM supervisor
        WHERE id = :supervisor_id
    ");
    $stmt->bindParam(':supervisor_id', $supervisor_id, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo "<script>window.location.href='partner_companies.php'</script>";
        exit;
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "<script>alert('Error fetching supervisor data. Please try again.'); window.location.href='partner_companies.php'</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OJT Web Portal: Supervisor Profile</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="images/pupLogo.png">
    <!-- Styles -->
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
            overflow: hidden;
        }
        .profile-card {
            max-width: 1500px;
            margin: 0 auto;
            margin-top: -35px;
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
            align-items: center 
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
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php require_once 'templates/admin_navbar.php'; ?>

    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="page-header">
                <div>
                    <a href="partner_companies.php" class="back-button">
                        <span class="back-icon"><img src="images/less-than.png" alt="Back"></span>
                        Back
                    </a>
                </div>
                <div class="page-title">
                    <h1 style="font-size: 16px;">VIEW PROFILE</h1>
                </div>
            </div>
            <div class="profile-card">
                <div class="profile-content">
                    <div class="profile-image">
                        <div class="image-placeholder">
                            <?php if (!empty($data['supervisor_profile_picture']) && file_exists($data['supervisor_profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($data['supervisor_profile_picture']); ?>" alt="Profile Image">
                            <?php else: ?>
                                <img src="images/placeholder.png" alt="Profile Placeholder" class="placeholder-icon">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="student-info">
                        <div class="student-name">
                            <?php echo htmlspecialchars($data['company_name'] ?? 'N/A'); ?>
                            <span class="student-badge">HTE</span>
                        </div>
                        <div class="student-id">
                            Address: <?php echo htmlspecialchars($data['company_address'] ?? 'N/A'); ?>
                        </div>
                        <br>
                        <div class="student-details">
                            <div class="detail-row">
                                <div class="detail-label">Name of Contact Person</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['contact_person'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Contact Number</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['phone_number'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['supervisor_email'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Position</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['position'] ?? 'N/A'); ?></div>
                            </div>
                            <br><br><br>
                            <div class="detail-row">
                                <div class="detail-label">MOA Start Date</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['date_notarized'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">MOA End Date</div>
                                <div class="detail-value">: <?php echo htmlspecialchars($data['moa_validity'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Remaining Time of Validity</div>
                                <div class="detail-value">: 
                                    <?php
                                    if (!empty($data['moa_validity'])) {
                                        try {
                                            $validityDate = new DateTime($data['moa_validity']);
                                            $today = new DateTime();
                                            $interval = $today->diff($validityDate);
                                            if ($validityDate >= $today) {
                                                echo $interval->format('%a days remaining');
                                            } else {
                                                echo 'Expired';
                                            }
                                        } catch (Exception $e) {
                                            echo 'Invalid date format';
                                        }
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/lib/jquery.min.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/menubar/sidebar.js"></script>
    <script src="js/lib/preloader/pace.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="js/lib/sweetalert/sweetalert.min.js"></script>
    <script src="js/lib/sweetalert/sweetalert.init.js"></script>

    <?php 
    if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
    ?>
        <script>
        sweetAlert("<?php echo $_SESSION['alert']; ?>", "<?php echo $_SESSION['status']; ?>", "<?php echo $_SESSION['status-code']; ?>");
        </script>
    <?php
        unset($_SESSION['status']);
    }
    ?>
</body>
</html>