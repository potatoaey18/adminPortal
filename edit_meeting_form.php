<?php
include '../connection/config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['auth_user']['userid']) || $_SESSION['auth_user']['userid'] == 0) {
    header("Location: index.php");
    exit;
}

$userid = $_SESSION['auth_user']['userid'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$action = $id ? 'update_meeting' : 'create_meeting';
$portal = filter_input(INPUT_GET, 'portal', FILTER_SANITIZE_STRING);
$portal = in_array($portal, ['student', 'faculty', 'hte', 'all']) ? $portal : 'student'; // Default to 'student'

$meeting_type = 'Zoom Meeting';
$link = '';
$passcode = '';
$meeting_date = '';
$meeting_time = '';
$agenda = '';

if ($id) {
    $stmt = $conn->prepare("SELECT meeting_type, link, passcode, meeting_date, meeting_time, agenda, portal FROM meetings WHERE id = ? AND created_by = ?");
    $stmt->execute([$id, $userid]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        $meeting_type = $data['meeting_type'];
        $link = $data['link'];
        $passcode = $data['passcode'];
        $meeting_date = $data['meeting_date'];
        $meeting_time = $data['meeting_time'];
        $agenda = $data['agenda'];
        $portal = $data['portal'];
    } else {
        $_SESSION['status'] = "Meeting not found or unauthorized.";
        $_SESSION['alert'] = "Error";
        $_SESSION['status-code'] = "error";
        header("Location: appointment_meetings.php");
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
    <title>OJT Web Portal: <?php echo $id ? 'Edit Meeting' : 'Add New Meeting - ' . ucfirst($portal); ?></title>
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
        .form-container input, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
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
                        <a href="appointment_meetings.php" class="back-button">
                            <span class="back-icon"><img src="images/less-than.png" alt="Back"></span>
                            Back
                        </a>
                    </div>
                </div>
                <h2><?php echo $id ? 'Edit Meeting' : 'Add New Meeting - ' . ucfirst($portal); ?></h2>
                <form action="manage_meetings.php" method="POST">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <input type="hidden" name="portal" value="<?php echo htmlspecialchars($portal); ?>">
                    <?php if ($id): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>
                    <label for="meeting_type">Meeting Type</label>
                    <input type="text" name="meeting_type" id="meeting_type" value="<?php echo htmlspecialchars($meeting_type); ?>" placeholder="e.g., Zoom Meeting" maxlength="100">
                    <label for="link">Meeting Link</label>
                    <input type="url" name="link" id="link" value="<?php echo htmlspecialchars($link); ?>" placeholder="https://..." required maxlength="255">
                    <label for="passcode">Passcode</label>
                    <input type="text" name="passcode" id="passcode" value="<?php echo htmlspecialchars($passcode); ?>" placeholder="Passcode" required maxlength="50">
                    <label for="meeting_date">Date</label>
                    <input type="date" name="meeting_date" id="meeting_date" value="<?php echo htmlspecialchars($meeting_date); ?>" required>
                    <label for="meeting_time">Time</label>
                    <input type="text" name="meeting_time" id="meeting_time" value="<?php echo htmlspecialchars($meeting_time); ?>" placeholder="e.g., 9am - 12nn" required maxlength="50">
                    <label for="agenda">Agenda</label>
                    <textarea name="agenda" id="agenda" rows="4" placeholder="Meeting agenda" required><?php echo htmlspecialchars($agenda); ?></textarea>
                    <div class="action-buttons">
                        <button type="submit" class="action-button" aria-label="<?php echo $id ? 'Update' : 'Add'; ?> Meeting">
                            <i class="ti-save"></i> <?php echo $id ? 'Update' : 'Add'; ?> Meeting
                        </button>
                        <a href="appointment_meetings.php" class="action-button cancel-button" aria-label="Cancel">
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
        customAlert("<?php echo $_SESSION['alert'] ?? 'Notice'; ?>", "<?php echo $_SESSION['status']; ?>", "<?php echo $_SESSION['status-code']; ?>");
        </script>
    <?php
        unset($_SESSION['status']);
        unset($_SESSION['alert']);
        unset($_SESSION['status-code']);
    }
    ?>
</body>
</html>