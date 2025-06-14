<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
    echo "<script>window.location.href='index.php'</script>";
    exit;
}

// Handle AJAX status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['status'])) {
    try {
        // Validate status against allowed ENUM values
        $validStatuses = ['checking_info', 'ulco_review', 'returned_to_coordinator', 'dean_vpaa_signature', 'signed_moa_retrieved', 'rejected'];
        if (!in_array($_POST['status'], $validStatuses)) {
            throw new Exception('Invalid status value');
        }

        $stmt = $conn->prepare("UPDATE new_moa_processing SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute([
            'status' => $_POST['status'],
            'id' => $_POST['id']
        ]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log("Status update error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
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
    <title>OJT Web Portal: MOA Applications</title>
    <link rel="shortcut icon" href="images/pupLogo.png">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .search-container { margin-bottom: 20px; }
        .search-box { width: 75%; padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        .search-button { padding: 8px 20px; background-color: #8B0000; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .search-button:hover { background-color: #700000; }
        .table-container { width: 100%; overflow-x: auto; margin-bottom: 20px; }
        .company-table { width: 100%; border-collapse: collapse; }
        .company-table th { background-color: #fff; color: #700000; text-align: center; padding: 20px 50px; min-width: 150px; border: 2px solid #700000; font-weight: 600; }
        .company-table td { padding: 20px 50px; border: 2px solid #700000; text-align: center; color: #000; }
        .company-table tr:nth-child(odd) { background-color: #f2f2f2; }
        .download-status-btn, .download-btn { background-color: #8B0000; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 12px; margin: 10px 5px; }
        .download-status-btn:hover, .download-btn:hover { background-color: #700000; }
        .company-table td .download-btn { display: inline-block; text-align: center; min-width: 80px; text-decoration: none; }
        .page-header { margin-bottom: 20px; }
        .page-title h1 { font-size: 16px; font-weight: bold; }
        .status-tabs { margin-bottom: 20px; }
        .status-tab { display: inline-block; padding: 10px 20px; margin-right: 5px; background-color: #f2f2f2; color: #8B0000; cursor: pointer; border-radius: 4px 4px 0 0; font-weight: 600; }
        .status-tab.active { background-color: #8B0000; color: white; }
        .status-tab:hover { background-color: #700000; color: white; }
        .status-dropdown { padding: 5px; border-radius: 4px; border: 1px solid #ccc; font-size: 12px; }
        .button-container { margin-bottom: 20px; }
        .no-data { text-align: center; padding: 20px; color: #700000; font-weight: bold; }
    </style>
</head>
<body>
    <?php require_once 'templates/admin_navbar.php'; ?>

    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="page-header">
                <div class="page-title"><br>
                    <h1>MOA Applications</h1><br>
                </div>
            </div>
            <div class="search-container">
                <input type="text" class="search-box" placeholder="Search by name, email, or company..." id="userSearch" aria-label="Search MOA applications">
                <button class="search-button" aria-label="Search">Search</button>
            </div>
            <div class="button-container">
                <button class="download-btn" id="downloadAll" aria-label="Download all MOA application data">Download All</button>
            </div>
            <div class="status-tabs">
                <?php
                // Statuses matching new_moa_processing ENUM
                $statuses = [
                    'checking_info' => 'Checking Info',
                    'ulco_review' => 'ULCO Review',
                    'returned_to_coordinator' => 'Returned to Coordinator',
                    'dean_vpaa_signature' => 'Dean/VPAA Signature',
                    'signed_moa_retrieved' => 'Signed MOA Retrieved',
                    'rejected' => 'Rejected'
                ];
                foreach ($statuses as $enum => $display) {
                    $tabId = 'tab' . array_search($enum, array_keys($statuses));
                    echo '<div class="status-tab" data-tab="' . $tabId . '" aria-label="Show ' . htmlspecialchars($display) . ' applications">' . htmlspecialchars($display) . '</div>';
                }
                ?>
            </div>
            <div class="table-container">
                <?php
                try {
                    $applicationsByStatus = [];
                    $hasData = false;

                    foreach (array_keys($statuses) as $status) {
                        $stmt = $conn->prepare("
                            SELECT 
                                nmp.id,
                                CONCAT(s.first_name, ' ', COALESCE(s.middle_name, ''), ' ', s.last_name) AS filer_name,
                                s.stud_email AS filer_email,
                                nmp.company_name,
                                nmp.nature_of_business,
                                nmp.company_address,
                                nmp.request_date AS date_filed,
                                nmp.status,
                                nmp.moa_document_path
                            FROM new_moa_processing nmp
                            LEFT JOIN students_data s ON nmp.student_id = s.id
                            WHERE nmp.status = :status
                        ");
                        $stmt->execute(['status' => $status]);
                        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $applicationsByStatus[$status] = $applications;
                        if (!empty($applications)) {
                            $hasData = true;
                        }
                    }

                    // Render tables for each status
                    foreach ($statuses as $enum => $display) {
                        $tabId = 'tab' . array_search($enum, array_keys($statuses));
                        $displayStyle = array_search($enum, array_keys($statuses)) === 0 ? 'block' : 'none';
                        echo '<div class="status-table" id="' . $tabId . '" style="display: ' . $displayStyle . ';">';
                        echo '<button class="download-status-btn" data-status="' . htmlspecialchars($enum) . '" id="downloadStatus' . array_search($enum, array_keys($statuses)) . '" aria-label="Download ' . htmlspecialchars($display) . ' data">Download ' . htmlspecialchars($display) . '</button>';
                        echo '<table class="company-table" id="statusTable' . array_search($enum, array_keys($statuses)) . '">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Filer\'s Name</th>';
                        echo '<th>Email</th>';
                        echo '<th>Company Name</th>';
                        echo '<th>Nature of Business</th>';
                        echo '<th>Address</th>';
                        echo '<th>Date Filed</th>';
                        echo '<th>Update Status</th>';
                        echo '<th>Download MOA</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        if (!empty($applicationsByStatus[$enum])) {
                            foreach ($applicationsByStatus[$enum] as $app) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($app['filer_name'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($app['filer_email'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($app['company_name'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($app['nature_of_business'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($app['company_address'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($app['date_filed'] ?? 'N/A') . '</td>';
                                echo '<td>';
                                echo '<select class="status-dropdown" data-id="' . htmlspecialchars($app['id']) . '" aria-label="Update status for ' . htmlspecialchars($app['filer_name']) . '">';
                                foreach ($statuses as $s => $s_display) {
                                    $selected = $s === $app['status'] ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($s) . '" ' . $selected . '>' . htmlspecialchars($s_display) . '</option>';
                                }
                                echo '</select>';
                                echo '</td>';
                                echo '<td>';
                                if (!empty($app['moa_document_path'])) {
                                    echo '<a href="download_file.php?id=' . htmlspecialchars($app['id']) . '" class="download-btn" aria-label="Download MOA document for ' . htmlspecialchars($app['filer_name']) . '">Download</a>';
                                } else {
                                    echo 'No file';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="8" class="no-data">No applications found for this status.</td></tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }

                    if (!$hasData) {
                        echo '<div class="no-data">No MOA applications available.</div>';
                    }
                } catch (PDOException $e) {
                    error_log("Database error: " . $e->getMessage());
                    echo '<div class="no-data">Error fetching data: ' . htmlspecialchars($e->getMessage()) . '</div>';
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
            // Tab switching
            $('.status-tab').on('click', function() {
                $('.status-tab').removeClass('active');
                $(this).addClass('active');
                var tabId = $(this).data('tab');
                $('.status-table').hide();
                $('#' + tabId).show();
                $('#userSearch').val(''); // Clear search
                $('#' + tabId + ' .company-table tbody tr').show(); // Reset search filter
            });

            // Set first tab as active
            $('.status-tab').first().addClass('active');

            // Search functionality for active tab
            $("#userSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $('.status-table:visible .company-table tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            // Sort each table by Filer's Name (index 0)
            $('.company-table').each(function() {
                var table = $(this);
                var tbody = table.find('tbody');
                var rows = tbody.find('tr').toArray();

                rows.sort(function(a, b) {
                    var aValue = $(a).find('td').eq(0).text().toLowerCase();
                    var bValue = $(b).find('td').eq(0).text().toLowerCase();
                    return aValue.localeCompare(bValue);
                });

                tbody.empty();
                $.each(rows, function(index, row) {
                    tbody.append(row);
                });
            });

            // Status dropdown change handler
            $(document).on('change', '.status-dropdown', function() {
                var id = $(this).data('id');
                var newStatus = $(this).val();
                var $dropdown = $(this);

                $.ajax({
                    url: 'documentation.php',
                    type: 'POST',
                    data: { id: id, status: newStatus },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Status Updated',
                                text: 'The status has been updated successfully.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload(); // Reload to reflect new status tab
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Update Failed',
                                text: response.error || 'Failed to update status.'
                            });
                            $dropdown.val($dropdown.data('original-value')); // Revert on error
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while updating the status.'
                        });
                        $dropdown.val($dropdown.data('original-value')); // Revert on error
                    }
                });

                // Store original value for revert
                $dropdown.data('original-value', newStatus);
            });

            // Download Status button click handler
            $(document).on("click", ".download-status-btn", function() {
                var status = $(this).data("status");
                var tableId = $(this).next('table').attr('id');
                var table = $('#' + tableId);
                var rows = table.find('tbody tr');
                var csvContent = "Filer's Name,Email,Company Name,Nature of Business,Address,Date Filed,Status\n";

                rows.each(function() {
                    var row = $(this);
                    var cols = row.find('td');
                    var rowData = [];

                    // Collect data from all columns except the last (Download MOA)
                    for (var i = 0; i < cols.length - 1; i++) {
                        var text = cols.eq(i).find('select').length ? cols.eq(i).find('select').val() : cols.eq(i).text().trim();
                        text = text.replace(/"/g, '""');
                        if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                            text = `"${text}"`;
                        }
                        rowData.push(text);
                    }

                    csvContent += rowData.join(',') + '\n';
                });

                // Create a Blob and trigger download
                var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement("a");
                var url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "MOA_Applications_" + status.replace(/[^a-zA-Z0-9]/g, '_') + ".csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Download All button click handler
            $(document).on("click", "#downloadAll", function() {
                var tables = $('.company-table');
                var csvContent = "Filer's Name,Email,Company Name,Nature of Business,Address,Date Filed,Status\n";

                tables.each(function() {
                    var rows = $(this).find('tbody tr');
                    rows.each(function() {
                        var row = $(this);
                        var cols = row.find('td');
                        var rowData = [];

                        // Collect data from all columns except the last (Download MOA)
                        for (var i = 0; i < cols.length - 1; i++) {
                            var text = cols.eq(i).find('select').length ? cols.eq(i).find('select').val() : cols.eq(i).text().trim();
                            text = text.replace(/"/g, '""');
                            if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                                text = `"${text}"`;
                            }
                            rowData.push(text);
                        }

                        csvContent += rowData.join(',') + '\n';
                    });
                });

                // Create a Blob and trigger download
                var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement("a");
                var url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "MOA_Applications_All.csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
</body>
</html>