<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['auth_user']['userid']) || $_SESSION['auth_user']['userid'] == 0) {
    echo "<script>window.location.href='index.php'</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OJT Web Portal: Coordinators</title>
    <link rel="shortcut icon" href="images/pupLogo.png">
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
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
            overflow-y: auto;
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
        .view-profile-btn, .download-btn {
            background-color: #8B0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin: 10px 5px;
        }
        .view-profile-btn:hover, .download-btn:hover {
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
        }
    </style>
</head>
<body>
    <?php require_once 'templates/admin_navbar.php'; ?>

    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="page-header">
                <div class="page-title"><br>
                    <h1>Coordinators</h1><br>
                </div>
            </div>
            <div class="search-container">
                <select class="search-field-select" id="searchField" aria-label="Select search field">
                    <option value="all">All Fields</option>
                    <option value="faculty_id">Faculty ID</option>
                    <option value="full_name">Full Name</option>
                    <option value="coor_dept">Department</option>
                    <option value="course_handled">Course Handled</option>
                    <option value="assigned_section">Assigned Section</option>
                    <option value="phone_number">Phone Number</option>
                    <option value="coordinators_email">Email</option>
                </select>
                <input type="text" class="search-box" placeholder="Search coordinators..." id="userSearch" aria-label="Search coordinators">
                <button class="search-button" id="searchButton" aria-label="Search">Search</button>
            </div>
            <div class="button-container">
                <button class="download-btn" id="downloadAll" aria-label="Download all coordinator data">Download All</button>
            </div>
            <div class="table-container">
                <table class="company-table">
                    <thead>
                        <tr>
                            <th>Faculty ID</th>
                            <th>Full Name</th>
                            <th>Department</th>
                            <th>Course Handled</th>
                            <th>Assigned Section</th>
                            <th>Phone Number</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $conn->prepare("
                                SELECT 
                                    id,
                                    faculty_id,
                                    CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS full_name,
                                    coor_dept,
                                    course_handled,
                                    assigned_section,
                                    phone_number,
                                    coordinators_email
                                FROM coordinators_account
                                ORDER BY course_handled ASC
                            ");
                            $stmt->execute();
                            $coordinators = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($coordinators as $coordinator) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($coordinator['faculty_id'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($coordinator['full_name'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($coordinator['coor_dept'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($coordinator['course_handled'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($coordinator['assigned_section'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($coordinator['phone_number'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($coordinator['coordinators_email'] ?? 'N/A') . '</td>';
                                echo '<td>
                                        <button class="view-profile-btn" data-id="' . htmlspecialchars($coordinator['id']) . '">View Profile</button>
                                      </td>';
                                echo '</tr>';
                            }

                            if ($stmt->rowCount() == 0) {
                                echo '<tr><td colspan="8">No coordinators found.</td></tr>';
                            }
                        } catch (PDOException $e) {
                            error_log("Database error: " . $e->getMessage());
                            echo '<tr><td colspan="8">Error fetching coordinator data.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
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
                var $rows = $(".company-table tbody tr");

                $rows.each(function() {
                    var $row = $(this);
                    var showRow = false;

                    if (field === "all") {
                        // Search across all columns except Action
                        var rowText = $row.text().toLowerCase();
                        showRow = rowText.includes(value);
                    } else {
                        // Search in specific column
                        var columnIndex = {
                            'faculty_id': 0,
                            'full_name': 1,
                            'coor_dept': 2,
                            'course_handled': 3,
                            'assigned_section': 4,
                            'phone_number': 5,
                            'coordinators_email': 6
                        }[field];
                        var cellText = $row.find('td').eq(columnIndex).text().toLowerCase();
                        showRow = cellText.includes(value);
                    }

                    $row.toggle(showRow);
                });

                // Show message if no results
                if ($rows.filter(':visible').length === 0) {
                    $(".company-table tbody").append('<tr class="no-results"><td colspan="8">No coordinators found.</td></tr>');
                } else {
                    $(".no-results").remove();
                }
            }

            // Trigger search on button click
            $("#searchButton").on("click", performSearch);

            // Trigger search on Enter key
            $("#userSearch").on("keypress", function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    performSearch();
                }
            });

            // Clear search and show all rows
            $("#userSearch").on("input", function() {
                if ($(this).val().trim() === "") {
                    $(".company-table tbody tr").show();
                    $(".no-results").remove();
                }
            });

            // View Profile button click handler
            $(document).on("click", ".view-profile-btn", function() {
                var id = $(this).data("id");
                window.location.href = "admin_view_coordinator_profile.php?id=" + id;
            });

            // Download All button click handler
            $(document).on("click", "#downloadAll", function() {
                var table = $('.company-table');
                var rows = table.find('tbody tr').filter(':visible'); // Only download visible rows
                var csvContent = "Faculty ID,Full Name,Department,Course Handled,Assigned Section,Phone Number,Email\n";

                rows.each(function() {
                    var row = $(this);
                    var cols = row.find('td');
                    var rowData = [];

                    // Collect data from all columns except the Action column (index 7)
                    for (var i = 0; i < cols.length - 1; i++) {
                        var text = cols.eq(i).text().trim();
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
                link.setAttribute("download", "Coordinators.csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Drag-to-scroll functionality
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

            // Support touch devices
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
        });
    </script>
</body>
</html>