<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
error_log("Session auth_user: " . print_r($_SESSION['auth_user'], true));
if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
    error_log("Redirecting to login due to invalid session");
    echo "<script>window.location.href='../pending/login.php'</script>";
    exit;
}

// Handle verification actions via AJAX
if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

    if ($id === false || !in_array($action, ['verify', 'disapprove'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit;
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT * FROM pending_users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $response = ['status' => 'success', 'message' => ''];
        if ($user) {
            if ($action === 'verify') {
                $role = strtolower($user['role']);
                if ($role === 'admin') {
                    $stmt = $conn->prepare("INSERT INTO admin_account (uniqueID, first_name, middle_name, last_name, id_number, address, phone_number, admin_email, admin_password, access_level, verification_code, verify_status, online_offlineStatus) 
                                            VALUES (:uniqueID, :first_name, :middle_name, :last_name, :id_number, :address, :phone_number, :email, :password, :access_level, :verification_code, :verify_status, :online_offlineStatus)");
                    $stmt->execute([
                        ':uniqueID' => $user['uniqueID'],
                        ':first_name' => $user['first_name'],
                        ':middle_name' => $user['middle_name'] ?? null,
                        ':last_name' => $user['last_name'],
                        ':id_number' => $user['id_number'],
                        ':address' => $user['address'],
                        ':phone_number' => $user['phone_number'],
                        ':email' => $user['stud_email'],
                        ':password' => $user['password'],
                        ':access_level' => $user['access_level'] ?: 1,
                        ':verification_code' => $user['verification_code'],
                        ':verify_status' => 'Verified',
                        ':online_offlineStatus' => $user['online_offlineStatus']
                    ]);
                } elseif ($role === 'adviser') {
                    $stmt = $conn->prepare("INSERT INTO coordinators_account (uniqueID, first_name, middle_name, last_name, faculty_id, complete_address, phone_number, coordinators_email, coordinators_password, access_level, verification_code, verify_status, online_offlineStatus) 
                                            VALUES (:uniqueID, :first_name, :middle_name, :last_name, :faculty_id, :complete_address, :phone_number, :email, :password, :access_level, :verification_code, :verify_status, :online_offlineStatus)");
                    $stmt->execute([
                        ':uniqueID' => $user['uniqueID'],
                        ':first_name' => $user['first_name'],
                        ':middle_name' => $user['middle_name'] ?? null,
                        ':last_name' => $user['last_name'],
                        ':faculty_id' => $user['id_number'],
                        ':complete_address' => $user['address'],
                        ':phone_number' => $user['phone_number'],
                        ':email' => $user['stud_email'],
                        ':password' => $user['password'],
                        ':access_level' => $user['access_level'] ?: 2,
                        ':verification_code' => $user['verification_code'],
                        ':verify_status' => 'Verified',
                        ':online_offlineStatus' => $user['online_offlineStatus']
                    ]);
                } elseif ($role === 'supervisor') {
                    $stmt = $conn->prepare("INSERT INTO supervisor (uniqueID, first_name, middle_name, last_name, company_address, phone_number, supervisor_email, supervisor_password, access_level, verification_code, verify_status, online_offlineStatus) 
                                            VALUES (:uniqueID, :first_name, :middle_name, :last_name, :company_address, :phone_number, :email, :password, :access_level, :verification_code, :verify_status, :online_offlineStatus)");
                    $stmt->execute([
                        ':uniqueID' => $user['uniqueID'],
                        ':first_name' => $user['first_name'],
                        ':middle_name' => $user['middle_name'] ?? null,
                        ':last_name' => $user['last_name'],
                        ':company_address' => $user['address'],
                        ':phone_number' => $user['phone_number'],
                        ':email' => $user['stud_email'],
                        ':password' => $user['password'],
                        ':access_level' => $user['access_level'] ?: 3,
                        ':verification_code' => $user['verification_code'],
                        ':verify_status' => 'Verified',
                        ':online_offlineStatus' => $user['online_offlineStatus']
                    ]);
                } elseif ($role === 'student') {
                    $stmt = $conn->prepare("INSERT INTO students_data (uniqueID, first_name, middle_name, last_name, student_ID, complete_address, stud_gender, age, phone_number, stud_email, stud_password, access_level, verification_code, verify_status, online_offlineStatus, sis_document) 
                                            VALUES (:uniqueID, :first_name, :middle_name, :last_name, :student_ID, :complete_address, :stud_gender, :age, :phone_number, :email, :password, :access_level, :verification_code, :verify_status, :online_offlineStatus, :sis_document)");
                    $stmt->execute([
                        ':uniqueID' => $user['uniqueID'],
                        ':first_name' => $user['first_name'],
                        ':middle_name' => $user['middle_name'] ?? null,
                        ':last_name' => $user['last_name'],
                        ':student_ID' => $user['id_number'],
                        ':complete_address' => $user['address'],
                        ':stud_gender' => $user['gender'],
                        ':age' => $user['age'],
                        ':phone_number' => $user['phone_number'],
                        ':email' => $user['stud_email'],
                        ':password' => $user['password'],
                        ':access_level' => $user['access_level'] ?: 4,
                        ':verification_code' => $user['verification_code'],
                        ':verify_status' => 'Verified',
                        ':online_offlineStatus' => $user['online_offlineStatus'],
                        ':sis_document' => $user['sis_document'] ?? null
                    ]);
                } elseif ($role === 'adviser') {
                    // TODO: Add logic for adviser verification
                    // Example: Insert into an advisers_account table
                    /*
                    $stmt = $conn->prepare("INSERT INTO advisers_account (uniqueID, first_name, middle_name, last_name, faculty_id, complete_address, phone_number, adviser_email, adviser_password, access_level, verification_code, verify_status, online_offlineStatus) 
                                            VALUES (:uniqueID, :first_name, :middle_name, :last_name, :faculty_id, :complete_address, :phone_number, :email, :password, :access_level, :verification_code, :verify_status, :online_offlineStatus)");
                    $stmt->execute([
                        ':uniqueID' => $user['uniqueID'],
                        ':first_name' => $user['first_name'],
                        ':middle_name' => $user['middle_name'] ?? null,
                        ':last_name' => $user['last_name'],
                        ':faculty_id' => $user['id_number'],
                        ':complete_address' => $user['address'],
                        ':phone_number' => $user['phone_number'],
                        ':email' => $user['stud_email'],
                        ':password' => $user['password'],
                        ':access_level' => $user['access_level'] ?: 5, // Adjust access level as needed
                        ':verification_code' => $user['verification_code'],
                        ':verify_status' => 'Verified',
                        ':online_offlineStatus' => $user['online_offlineStatus']
                    ]);
                    */
                    $response['status'] = 'error';
                    $response['message'] = "Adviser verification not implemented yet.";
                } else {
                    $response['status'] = 'error';
                    $response['message'] = "Invalid role for verification.";
                }
                if ($response['status'] !== 'error') {
                    $stmt = $conn->prepare("DELETE FROM pending_users WHERE id = ?");
                    $stmt->execute([$id]);
                    $response['message'] = "User verified successfully.";
                }
            } elseif ($action === 'disapprove') {
                $stmt = $conn->prepare("DELETE FROM pending_users WHERE id = ?");
                $stmt->execute([$id]);
                $response['message'] = "User disapproved and removed.";
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = "User not found.";
        }
        $conn->commit();
        echo json_encode($response);
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OJT Web Portal: Verify Users</title>
    <link rel="shortcut icon" href="images/pupLogo.png">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .search-container {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-box {
            width: 50%;
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
        .search-field-select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            background-color: white;
            cursor: pointer;
        }
        .table-container {
            width: 100%;
            max-height: auto;
            overflow-x: auto;
            overflow-y: hidden;
            margin-bottom: 20px;
            position: relative;
            -webkit-overflow-scrolling: touch;
            cursor: grab;
        }
        .table-container.dragging {
            cursor: grabbing;
            user-select: none;
        }
        .company-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }
        .company-table th {
            background-color: #fff;
            color: #700000;
            text-align: center;
            padding: 20px 50px;
            min-width: 150px;
            border: 2px solid #700000;
            font-weight: 600;
        }
        .company-table td {
            padding: 20px 50px;
            border: 2px solid #700000;
            text-align: center;
            color: #000;
        }
        .company-table tr:nth-child(odd) {
            background-color: #f2f2f2;
        }
        .action-btn {
            background-color: #8B0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin: 5px;
        }
        .action-btn:hover {
            background-color: #700000;
        }
        .page-header {
            margin-bottom: 20px;
        }
        .page-title h1 {
            font-size: 16px;
            font-weight: bold;
        }
        .button-container {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .download-btn {
            padding: 8px 20px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .download-btn:hover {
            background-color: #700000;
        }
    </style>
</head>
<body>
    <?php require_once 'templates/admin_navbar.php'; ?>

    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="page-header">
                <div class="page-title"><br>
                    <h1>For Verification List</h1><br>
                </div>
            </div>
            <div class="search-container">
                <select class="search-field-select" id="searchField" aria-label="Select search field">
                    <option value="all">All Fields</option>
                    <option value="id_number">ID Number</option>
                    <option value="full_name">Full Name</option>
                    <option value="role">Role</option>
                </select>
                <input type="text" class="search-box" placeholder="Search pending users..." id="userSearch" aria-label="Search pending users">
                <button class="search-button" id="searchButton" aria-label="Search">Search</button>
            </div>
            <div class="button-container">
                <button class="download-btn" id="downloadAll" aria-label="Download all pending user data">Download All</button>
            </div>
            <div class="table-container">
                <?php
                try {
                    if (!isset($conn) || !$conn) {
                        error_log("Database connection is null");
                        echo '<p style="text-align: center; color: #700000;">Error: Database connection failed.</p>';
                    } else {
                        $stmt = $conn->prepare("SELECT * FROM pending_users WHERE LOWER(role) IN ('admin', 'coordinator', 'supervisor', 'student', 'adviser') ORDER BY role");
                        $stmt->execute();
                        $pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        error_log("Fetched " . count($pendingUsers) . " pending users");

                        if (empty($pendingUsers)) {
                            $_SESSION['alert'] = "Info";
                            $_SESSION['status'] = "No pending users found.";
                            echo '<p style="text-align: center; color: #700000;">No pending users found.</p>';
                        } else {
                            echo '<table class="company-table" id="pendingUsersTable">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>ID Number</th>';
                            echo '<th>Full Name</th>';
                            echo '<th>Role</th>';
                            echo '<th>Action</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            foreach ($pendingUsers as $user) {
                                $fullName = trim($user['first_name'] . ' ' . ($user['middle_name'] ?? '') . ' ' . $user['last_name']);
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($user['id_number'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($fullName ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($user['role'] ?? 'N/A') . '</td>';
                                echo '<td>
                                        <button class="action-btn verify-btn" data-id="' . htmlspecialchars($user['id']) . '" aria-label="Verify user">Verify</button>
                                        <button class="action-btn disapprove-btn" data-id="' . htmlspecialchars($user['id']) . '" style="background-color: #ff4444;" aria-label="Disapprove user">Disapprove</button>
                                      </td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Database error: " . $e->getMessage());
                    $_SESSION['alert'] = "Error";
                    $_SESSION['status'] = "Database error: " . $e->getMessage();
                    echo '<p style="text-align: center; color: #700000;">Error fetching users: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Search functionality
            function performSearch() {
                var value = $("#userSearch").val().toLowerCase().trim();
                var field = $("#searchField").val();
                var $rows = $("#pendingUsersTable tbody tr");
                var anyVisible = false;

                $rows.each(function() {
                    var $row = $(this);
                    var showRow = false;

                    if (field === "all") {
                        var rowText = $row.text().toLowerCase();
                        showRow = rowText.includes(value);
                    } else {
                        var columnIndex = {
                            'id_number': 0,
                            'full_name': 1,
                            'role': 2
                        }[field];
                        var cellText = $row.find('td').eq(columnIndex).text().toLowerCase();
                        showRow = cellText.includes(value);
                    }

                    $row.toggle(showRow);
                    if (showRow) anyVisible = true;
                });

                if (!anyVisible) {
                    $(".table-container").append('<div class="no-results" style="text-align: center; padding: 20px;">No pending users found.</div>');
                } else {
                    $(".no-results").remove();
                }
            }

            $("#searchButton").on("click", performSearch);
            $("#userSearch").on("keypress", function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    performSearch();
                }
            });

            $("#userSearch").on("input", function() {
                if ($(this).val().trim() === "") {
                    $("#pendingUsersTable tbody tr").show();
                    $(".no-results").remove();
                }
            });

            // Download all as CSV
            $("#downloadAll").on("click", function() {
                var $table = $("#pendingUsersTable");
                var csvContent = "ID Number,Full Name,Role\n";

                $table.find('tbody tr:visible').each(function() {
                    var row = $(this);
                    var cols = row.find('td');
                    var rowData = [];

                    for (var i = 0; i < 3; i++) {
                        var text = cols.eq(i).text().trim();
                        text = text.replace(/"/g, '""');
                        if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                            text = `"${text}"`;
                        }
                        rowData.push(text);
                    }

                    csvContent += rowData.join(',') + '\n';
                });

                var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement("a");
                var url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "Pending_Users_All.csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Verification with popup
            $('.verify-btn').on('click', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to verify this user?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, verify!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'verify_users.php',
                            type: 'POST',
                            data: {
                                action: 'verify',
                                id: id
                            },
                            success: function(response) {
                                response = JSON.parse(response);
                                Swal.fire({
                                    icon: response.status === 'success' ? 'success' : 'error',
                                    title: response.status === 'success' ? 'Success' : 'Error',
                                    text: response.message,
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    if (response.status === 'success') {
                                        $('#pendingUsersTable tbody tr').each(function() {
                                            if ($(this).find('td').eq(3).find('button').data('id') == id) {
                                                $(this).remove();
                                            }
                                        });
                                        if ($('#pendingUsersTable tbody tr').length === 0) {
                                            location.reload();
                                        }
                                    }
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An unexpected error occurred.',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            });

            // Disapproval with popup
            $('.disapprove-btn').on('click', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to disapprove this user? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, disapprove!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'verify_users.php',
                            type: 'POST',
                            data: {
                                action: 'disapprove',
                                id: id
                            },
                            success: function(response) {
                                response = JSON.parse(response);
                                Swal.fire({
                                    icon: response.status === 'success' ? 'info' : 'error',
                                    title: response.status === 'success' ? 'Disapproved' : 'Error',
                                    text: response.message,
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    if (response.status === 'success') {
                                        $('#pendingUsersTable tbody tr').each(function() {
                                            if ($(this).find('td').eq(3).find('button').data('id') == id) {
                                                $(this).remove();
                                            }
                                        });
                                        if ($('#pendingUsersTable tbody tr').length === 0) {
                                            location.reload();
                                        }
                                    }
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An unexpected error occurred.',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            });

            const tableContainer = $('.table-container');
            let isDragging = false;
            let startX, scrollLeft;

            tableContainer.on('mousedown', function(e) {
                isDragging = true;
                tableContainer.addClass('dragging');
                startX = e.pageX - tableContainer.offset().left;
                scrollLeft = tableContainer.scrollLeft();
            });

            tableContainer.on('mouseleave mouseup', function() {
                isDragging = false;
                tableContainer.removeClass('dragging');
            });

            tableContainer.on('mousemove', function(e) {
                if (!isDragging) return;
                e.preventDefault();
                const x = e.pageX - tableContainer.offset().left;
                const walk = (x - startX) * 2;
                tableContainer.scrollLeft(scrollLeft - walk);
            });

            tableContainer.on('touchstart', function(e) {
                isDragging = true;
                tableContainer.addClass('dragging');
                startX = e.originalEvent.touches[0].pageX - tableContainer.offset().left;
                scrollLeft = tableContainer.scrollLeft();
            });

            tableContainer.on('touchend touchcancel', function() {
                isDragging = false;
                tableContainer.removeClass('dragging');
            });

            tableContainer.on('touchmove', function(e) {
                if (!isDragging) return;
                e.preventDefault();
                const x = e.originalEvent.touches[0].pageX - tableContainer.offset().left;
                const walk = (x - startX) * 2;
                tableContainer.scrollLeft(scrollLeft - walk);
            });

            <?php if (isset($_SESSION['status']) && $_SESSION['status'] != ''): ?>
                Swal.fire({
                    icon: '<?php echo strtolower($_SESSION['alert']); ?>',
                    title: '<?php echo $_SESSION['alert']; ?>',
                    text: '<?php echo $_SESSION['status']; ?>',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                <?php
                unset($_SESSION['status']);
                unset($_SESSION['alert']);
                ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>