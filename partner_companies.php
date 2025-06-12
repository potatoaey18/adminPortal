<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
error_log("Session userid: " . ($_SESSION['auth_user']['userid'] ?? 'Not set'));
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
    <title>OJT Web Portal: Manage H.T.E</title>
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
        .option-button {
            padding: 8px 15px;
            margin-right: 10px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
        }
        .new-moa {
            background-color: #8B0000;
            color: white;
        }
        .plus-icon {
            margin-right: 5px;
            font-weight: bold;
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
        .view-profile-btn, .moa-btn, .download-btn {
            background-color: #8B0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin: 10px 5px;
        }
        .view-profile-btn:hover, .moa-btn:hover, .download-btn:hover {
            background-color: #700000;
        }
        .step-description {
            margin-bottom: 20px;
            color: #000;
            padding: 20px;
        }
        .move-text-up {
            margin-top: -20px !important;
        }
        .title-color {
            color: #ffc107 !important;
        }
        .button-container {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <?php require_once 'templates/admin_navbar.php'; ?>

    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div>
                <div>
                    <div class="page-header">
                        <div class="page-title"><br>
                            <h1 style="font-size: 16px;">Partnership Companies</h1><br><br>
                        </div>
                    </div>
                </div>
                <div class="search-container">
                    <select class="search-field-select" id="searchField" aria-label="Select search field">
                        <option value="all">All Fields</option>
                        <option value="company_name">Company Name</option>
                        <option value="business_nature">Nature of Business</option>
                        <option value="contact_person">Contact Person</option>
                        <option value="position">Position</option>
                        <option value="supervisor_email">Email Address</option>
                        <option value="company_address">Address</option>
                        <option value="moa_info">Date Notarized and Validity</option>
                    </select>
                    <input type="text" class="search-box" placeholder="Search companies..." id="companySearch" aria-label="Search companies">
                    <button class="search-button" id="searchButton" aria-label="Search">Search</button>
                </div>
                <div class="button-container">
                    <button class="option-button new-moa" aria-label="Add new MOA">
                        <span class="plus-icon">+</span> New MOA
                    </button>
                    <button class="download-btn" id="downloadAll" aria-label="Download all company data">Download All</button>
                </div>
                <div class="table-container">
                    <table class="company-table">
                        <thead>
                            <tr>
                                <th class="table-head">Company Name</th>
                                <th class="table-head">Nature of Business</th>
                                <th class="table-head">Contact Person</th>
                                <th class="table-head">Position</th>
                                <th class="table-head">Email Address</th>
                                <th class="table-head">Address</th>
                                <th class="table-head">Date Notarized and Validity</th>
                                <th class="table-head">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $conn->prepare("
                                    SELECT 
                                        supervisor.id AS supervisorID, 
                                        supervisor.supervisor_email, 
                                        supervisor.company_address, 
                                        supervisor.link_to_moa,  
                                        supervisor.company_name AS company_name, 
                                        supervisor.phone_number, 
                                        supervisor.position, 
                                        CONCAT(date_notarized, ' ', moa_validity) AS moa_info, 
                                        CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS contact_person, 
                                        supervisor.business_nature 
                                    FROM supervisor 
                                    LEFT JOIN company_skills_requirements 
                                        ON supervisor.company_name = company_skills_requirements.company_name 
                                    GROUP BY supervisor.id, supervisor.position
                                ");
                                $stmt->execute();
                                $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($companies as $company) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($company['company_name'] ?? 'N/A') . '</td>';
                                    echo '<td>' . htmlspecialchars($company['business_nature'] ?? 'N/A') . '</td>';
                                    echo '<td>' . htmlspecialchars($company['contact_person'] ?? 'N/A') . '<br>' . 
                                         htmlspecialchars($company['phone_number'] ?? 'N/A') . '</td>';
                                    echo '<td>' . htmlspecialchars($company['position'] ?? 'N/A') . '</td>';
                                    echo '<td>' . htmlspecialchars($company['supervisor_email'] ?? 'N/A') . '</td>';
                                    echo '<td>' . htmlspecialchars($company['company_address'] ?? 'N/A') . '</td>';
                                    echo '<td>' . htmlspecialchars($company['moa_info'] ?? 'N/A') . '</td>';
                                    echo '<td>
                                            <button class="view-profile-btn" data-supervisor-id="' . htmlspecialchars($company['supervisorID']) . '">View Profile</button>
                                            <button class="moa-btn" data-moa-link="' . htmlspecialchars($company['link_to_moa'] ?? '') . '">MOA</button>
                                          </td>';
                                    echo '</tr>';
                                }

                                if ($stmt->rowCount() == 0) {
                                    echo '<tr><td colspan="8">No companies found.</td></tr>';
                                }
                            } catch (PDOException $e) {
                                error_log("Database error: " . $e->getMessage());
                                echo '<tr><td colspan="8">Error fetching company data.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <section id="main-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>2024 © <a href="#">Mabuhay</a></p>
                            </div>
                        </div>
                    </div>
                </section>
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
            // Verify jQuery is loaded
            console.log("jQuery version:", $.fn.jquery);

            // Pass userid from PHP session to JavaScript
            var adminId = '<?php echo htmlspecialchars($_SESSION['auth_user']['userid'] ?? ''); ?>';
            console.log("Admin ID: ", adminId);

            // Search functionality
            function performSearch() {
                var value = $("#companySearch").val().toLowerCase().trim();
                var field = $("#searchField").val();
                var $rows = $(".company-table tbody tr");
                var anyVisible = false;

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
                            'company_name': 0,
                            'business_nature': 1,
                            'contact_person': 2,
                            'position': 3,
                            'supervisor_email': 4,
                            'company_address': 5,
                            'moa_info': 6
                        }[field];
                        var cellText = $row.find('td').eq(columnIndex).text().toLowerCase();
                        showRow = cellText.includes(value);
                    }

                    $row.toggle(showRow);
                    if (showRow) anyVisible = true;
                });

                // Show message if no results
                if (!anyVisible) {
                    $(".company-table tbody").append('<tr class="no-results"><td colspan="8">No companies found.</td></tr>');
                } else {
                    $(".no-results").remove();
                }
            }

            // Trigger search on button click
            $("#searchButton").on("click", performSearch);

            // Trigger search on Enter key
            $("#companySearch").on("keypress", function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    performSearch();
                }
            });

            // Clear search and show all rows
            $("#companySearch").on("input", function() {
                if ($(this).val().trim() === "") {
                    $(".company-table tbody tr").show();
                    $(".no-results").remove();
                }
            });

            // New MOA button click handler
            $(document).on("click", ".new-moa", function() {
                console.log("New MOA button clicked");
                window.location.href = "admin_new_moa.php";
            });

            // View Profile button click handler
            $(document).on("click", ".view-profile-btn", function() {
                var supervisorId = $(this).data("supervisor-id");
                console.log("View Profile clicked for supervisor ID:", supervisorId);
                window.location.href = "view_company_profile.php?supervisor=" + supervisorId;
            });

            // MOA button click handler
            $(document).on("click", ".moa-btn", function() {
                var moaLink = $(this).data("moa-link");
                console.log("MOA button clicked for link:", moaLink);
                if (moaLink) {
                    window.open(moaLink, '_blank');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'No MOA Available',
                        text: 'No MOA link is available for this company.'
                    });
                }
            });

            // Download All button click handler
            $(document).on("click", "#downloadAll", function() {
                var table = $('.company-table');
                var rows = table.find('tbody tr:visible');
                var csvContent = "Company Name,Nature of Business,Contact Person,Position,Email Address,Address,Date Notarized and Validity\n";

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
                link.setAttribute("download", "Partnership_Companies.csv");
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