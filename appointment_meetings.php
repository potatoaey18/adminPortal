<?php
include '../connection/config.php';
// Enable error display for debugging (revert in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
error_log("Reached appointment_meetings.php");

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if not authenticated
if (!isset($_SESSION['auth_user']['admin_id']) || !$_SESSION['auth_user']['admin_id']) {
    error_log("Session auth_user[admin_id] not set, redirecting to index.php");
    header('Location: index.php');
    exit;
}

// Fetch meetings from the database
$admin_id = (int)$_SESSION['auth_user']['admin_id'];
error_log("Fetching meetings for user ID: $admin_id");

try {
    $stmt = $conn->prepare("SELECT * FROM meetings WHERE created_by = ? ORDER BY meeting_date DESC");
    $stmt->execute([$admin_id]);
    $meetings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error in appointment_meetings.php: " . $e->getMessage());
    $_SESSION['alert'] = "Error";
    $_SESSION['status'] = "Failed to fetch meetings: " . htmlspecialchars($e->getMessage());
    $_SESSION['status-code'] = "error";
    $meetings = [];
}

// Group meetings by portal
$student_meetings = [];
$faculty_meetings = [];
$hte_meetings = [];

foreach ($meetings as $meeting) {
    if ($meeting['portal'] === 'student' || $meeting['portal'] === 'all') {
        $student_meetings[] = $meeting;
    }
    if ($meeting['portal'] === 'faculty' || $meeting['portal'] === 'all') {
        $faculty_meetings[] = $meeting;
    }
    if ($meeting['portal'] === 'hte' || $meeting['portal'] === 'all') {
        $hte_meetings[] = $meeting;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OJT Web Portal: Appointment Meetings</title>
    <link rel="shortcut icon" href="images/pupLogo.png">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/menubar/sidebar.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="endorsement-css/endorsement-moa.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* [Existing styles unchanged] */
        .appointment-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .appointment-title { font-size: 24px; font-weight: 600; color: #333; margin-bottom: 30px; }
        .portal-section { margin-bottom: 40px; }
        .portal-header { font-size: 14px; font-weight: 600; color: #800000; margin-bottom: 15px; padding-bottom: 5px; }
        .meetings-grid { display: flex; gap: 20px; flex-wrap: wrap; justify-content: flex-start; }
        .meeting-card { background: white; border: 2px solid #ddd; border-radius: 12px; padding: 20px; width: 300px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .meeting-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .meeting-header { display: flex; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .meeting-icon { width: 24px; height: auto; margin-right: 10px; color: #4a90e2; }
        .meeting-type { font-size: 18px; font-weight: 600; color: #333; }
        .meeting-details { display: flex; flex-direction: column; gap: 8px; }
        .detail-row { display: flex; align-items: flex-start; }
        .detail-label { font-weight: 600; color: #555; min-width: 80px; margin-right: 10px; }
        .detail-value { color: #666; flex: 1; word-break: break-all; }
        .meeting-link { color: #4a90e2; text-decoration: none; }
        .meeting-link:hover { text-decoration: underline; }
        .action-buttons { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; }
        .action-button { padding: 8px 16px; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; transition: background-color 0.3s ease, transform 0.2s ease; display: flex; align-items: center; gap: 5px; text-decoration: none; }
        .action-button i { font-size: 16px; }
        .edit-button { background-color: #8B0000; color: white; }
        .edit-button:hover { background-color: #700000; transform: translateY(-2px); }
        .delete-button { background-color: #d32f2f; color: white; }
        .delete-button:hover { background-color: #b71c1c; transform: translateY(-2px); }
        .action-button:focus { outline: 2px solid #700000; outline-offset: 2px; }
        .action-button[aria-label] { position: relative; }
        .action-button[aria-label]:hover:after { content: attr(aria-label); position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background-color: #333; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; white-space: nowrap; z-index: 10; }
        .add-button { background-color: #8B0000; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 20px; font-size: 14px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
        .add-button:hover { background-color: #700000; }
        .no-meetings { color: #666; font-size: 16px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 400px; border-radius: 8px; position: relative; }
        .modal-content h2 { font-size: 18px; color: #333; margin-bottom: 20px; text-align: center; }
        .modal-content .close { position: absolute; top: 10px; right: 15px; color: #aaa; font-size: 24px; font-weight: bold; cursor: pointer; }
        .modal-content .close:hover, .modal-content .close:focus { color: #000; text-decoration: none; cursor: pointer; }
        .modal-content button { background-color: #8B0000; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-size: 14px; margin-bottom: 10px; transition: background-color 0.3s ease; }
        .modal-content button:hover { background-color: #700000; }
        @media (max-width: 768px) {
            .meetings-grid { flex-direction: column; align-items: center; }
            .meeting-card { width: 100%; max-width: 400px; }
            .action-buttons { flex-direction: column; gap: 10px; }
            .action-button { width: 100%; justify-content: center; }
            .modal-content { width: 90%; }
        }
    </style>
</head>
<body>
    <?php require_once 'templates/admin_navbar.php'; ?>
    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div class="content-wrapper" style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="appointment-container">
                <div class="page-header">
                    <div class="page-title">
                        <h1 style="font-size: 16px;">Appointment Meetings</h1><br>
                    </div>
                </div>
                <button class="add-button" onclick="openModal()" aria-label="Add New Meeting">
                    <i class="ti-plus"></i> Add New Meeting
                </button>
                <div id="addMeetingModal" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">×</span>
                        <h2>Choose Portal for the New Meeting</h2>
                        <button onclick="window.location.href='edit_meeting_form.php?portal=student'">Student</button>
                        <button onclick="window.location.href='edit_meeting_form.php?portal=faculty'">Faculty</button>
                        <button onclick="window.location.href='edit_meeting_form.php?portal=hte'">HTE</button>
                        <button onclick="window.location.href='edit_meeting_form.php?portal=all'">All</button>
                    </div>
                </div>

                <!-- Student Portal Section -->
                <div class="portal-section">
                    <h2 class="portal-header">Student Portal</h2>
                    <div class="meetings-grid">
                        <?php if (empty($student_meetings)): ?>
                            <p class="no-meetings">No meetings found for Student Portal.</p>
                        <?php else: ?>
                            <?php foreach ($student_meetings as $meeting): ?>
                                <div class="meeting-card" data-meeting-id="<?php echo htmlspecialchars($meeting['id']); ?>">
                                    <div class="meeting-header">
                                        <img src="images/video-cam.png" alt="Meeting Icon" class="meeting-icon">
                                        <span class="meeting-type"><?php echo htmlspecialchars($meeting['meeting_type'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="meeting-details">
                                        <div class="detail-row">
                                            <span class="detail-label">Link</span>
                                            <span class="detail-value">: <a href="<?php echo htmlspecialchars($meeting['link'] ?? '#'); ?>" class="meeting-link" target="_blank"><?php echo htmlspecialchars($meeting['link'] ?? 'N/A'); ?></a></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Passcode</span>
                                            <span class="detail-value">: <?php echo htmlspecialchars($meeting['passcode'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Date</span>
                                            <span class="detail-value">: <?php echo isset($meeting['meeting_date']) ? date('F d, Y', strtotime($meeting['meeting_date'])) : 'N/A'; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Time</span>
                                            <span class="detail-value">: <?php echo htmlspecialchars($meeting['meeting_time'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Agenda</span>
                                            <span class="detail-value">: <?php echo htmlspecialchars($meeting['agenda'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Created On</span>
                                            <span class="detail-value">: <?php echo isset($meeting['created_at']) ? date('F d, Y H:i', strtotime($meeting['created_at'])) : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                    <div class="action-buttons">
                                        <a href="edit_meeting_form.php?id=<?php echo htmlspecialchars($meeting['id']); ?>" class="action-button edit-button" aria-label="Edit Meeting">
                                            <i class="ti-pencil"></i> Edit
                                        </a>
                                        <button class="action-button delete-button" onclick="confirmDelete(<?php echo htmlspecialchars($meeting['id']); ?>)" aria-label="Delete Meeting">
                                            <i class="ti-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Faculty Portal Section -->
                <div class="portal-section">
                    <h2 class="portal-header">Faculty Portal</h2>
                    <div class="meetings-grid">
                        <?php if (empty($faculty_meetings)): ?>
                            <p class="no-meetings">No meetings found for Faculty Portal.</p>
                        <?php else: ?>
                            <?php foreach ($faculty_meetings as $meeting): ?>
                                <div class="meeting-card" data-meeting-id="<?php echo htmlspecialchars($meeting['id']); ?>">
                                    <div class="meeting-header">
                                        <img src="images/video-cam.png" alt="Meeting Icon" class="meeting-icon">
                                        <span class="meeting-type"><?php echo htmlspecialchars($meeting['meeting_type'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="meeting-details">
                                        <div class="detail-row">
                                            <span class="detail-label">Link</span>
                                            <span class="detail-value">: <a href="<?php echo htmlspecialchars($meeting['link'] ?? '#'); ?>" class="meeting-link" target="_blank"><?php echo htmlspecialchars($meeting['link'] ?? 'N/A'); ?></a></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Passcode</span>
                                            <span class="detail-value">: <?php echo htmlspecialchars($meeting['passcode'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Date</span>
                                            <span class="detail-value">: <?php echo isset($meeting['meeting_date']) ? date('F d, Y', strtotime($meeting['meeting_date'])) : 'N/A'; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Time</span>
                                            <span class="detail-value">: <?php echo htmlspecialchars($meeting['meeting_time'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Agenda</span>
                                            <span class="detail-value">: <?php echo htmlspecialchars($meeting['agenda'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Created On</span>
                                            <span class="detail-value">: <?php echo isset($meeting['created_at']) ? date('F d, Y H:i', strtotime($meeting['created_at'])) : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                    <div class="action-buttons">
                                        <a href="edit_meeting_form.php?id=<?php echo htmlspecialchars($meeting['id']); ?>" class="action-button edit-button" aria-label="Edit Meeting">
                                            <i class="ti-pencil"></i> Edit
                                        </a>
                                        <button class="action-button delete-button" onclick="confirmDelete(<?php echo htmlspecialchars($meeting['id']); ?>)" aria-label="Delete Meeting">
                                            <i class="ti-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- HTE Portal Section -->
                <div class="portal-section">
                    <h2 class="portal-header">HTE Portal</h2>
                    <div class="meetings-grid">
                        <?php if (empty($hte_meetings)): ?>
                            <p class="no-meetings">No meetings found for HTE Portal.</p>
                        <?php else: ?>
                            <?php foreach ($hte_meetings as $meeting): ?>
                                <div class="meeting-card" data-meeting-id="<?php echo htmlspecialchars($meeting['id']); ?>">
                                    <div class="meeting-header">
                                        <img src="images/video-cam.png" alt="Meeting Icon" class="meeting-icon">
                                        <span class="meeting-type"><?php echo htmlspecialchars($meeting['meeting_type'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="meeting-details">
                                        <div class="detail-row">
                                            <span class="detail-label">Link</span>
                                            <span class="detail-value">: <a href="<?php echo htmlspecialchars($meeting['link'] ?? '#'); ?>" class="meeting-link" target="_blank"><?php echo htmlspecialchars($meeting['link'] ?? 'N/A'); ?></a></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Passcode</span>
                                            <span class="detail-value">: <?php echo htmlspecialchars($meeting['passcode'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Date</span>
                                            <span class="detail-value">: <?php echo isset($meeting['meeting_date']) ? date('F d, Y', strtotime($meeting['meeting_date'])) : 'N/A'; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Time</span>
                                            <span class="detail-value">: <?php echo htmlspecialchars($meeting['meeting_time'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Agenda</span>
                                            <span class="detail-value">: <?php echo htmlspecialchars($meeting['agenda'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Created On</span>
                                            <span class="detail-value">: <?php echo isset($meeting['created_at']) ? date('F d, Y H:i', strtotime($meeting['created_at'])) : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                    <div class="action-buttons">
                                        <a href="edit_meeting_form.php?id=<?php echo htmlspecialchars($meeting['id']); ?>" class="action-button edit-button" aria-label="Edit Meeting">
                                            <i class="ti-pencil"></i> Edit
                                        </a>
                                        <button class="action-button delete-button" onclick="confirmDelete(<?php echo htmlspecialchars($meeting['id']); ?>)" aria-label="Delete Meeting">
                                            <i class="ti-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/lib/jquery.min.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/preloader/pace.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="js/lib/customAlert.js"></script>
    <script>
        function openModal() {
            document.getElementById('addMeetingModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addMeetingModal').style.display = 'none';
        }

        function confirmDelete(meetingId) {
            try {
                Swal.fire({
                    title: 'Confirm Delete',
                    text: 'Are you sure you want to delete this meeting?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal-custom-popup',
                        icon: 'swal-custom-icon',
                        title: 'swal-custom-title',
                        htmlContainer: 'swal-custom-html',
                        confirmButton: 'swal-confirm-button',
                        cancelButton: 'swal-cancel-button'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Deleting meeting ID:', meetingId);
                        window.location.href = `manage_meetings.php?action=delete_meeting&id=${meetingId}`;
                    }
                });
            } catch (error) {
                console.error('Error in confirmDelete:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'An error occurred while trying to delete the meeting. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'swal-confirm-button'
                    }
                });
            }
        }

        <?php if (isset($_SESSION['status']) && $_SESSION['status'] != ''): ?>
            try {
                if ("<?php echo htmlspecialchars($_SESSION['status-code'] ?? 'error'); ?>" === "success") {
                    showSuccessAlert("<?php echo htmlspecialchars($_SESSION['alert'] ?? 'Notice'); ?>", "<?php echo addslashes($_SESSION['status']); ?>");
                } else {
                    Swal.fire({
                        title: "<?php echo htmlspecialchars($_SESSION['alert'] ?? 'Notice'); ?>",
                        html: `<span style="color: #333;">${"<?php echo addslashes($_SESSION['status']); ?>"}</span>`,
                        icon: "<?php echo htmlspecialchars($_SESSION['status-code'] ?? 'error'); ?>",
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'swal-custom-popup',
                            icon: 'swal-custom-icon',
                            title: 'swal-custom-title',
                            htmlContainer: 'swal-custom-html',
                            confirmButton: 'swal-confirm-button'
                        }
                    });
                }
            } catch (error) {
                console.error('Error displaying session alert:', error);
                alert("<?php echo addslashes($_SESSION['status']); ?>");
            }
            <?php
            unset($_SESSION['status']);
            unset($_SESSION['alert']);
            unset($_SESSION['status-code']);
            ?>
        <?php endif; ?>
    </script>
</body>
</html>