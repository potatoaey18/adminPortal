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
    <title>OJT Web Portal: Working Students</title>
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
            overflow-y: hidden; /* Hide vertical scrollbar */
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
        .view-profile-btn, .download-section-btn, .download-btn {
            background-color: #8B0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin: 10px 5px;
        }
        .view-profile-btn:hover, .download-section-btn:hover, .download-btn:hover {
            background-color: #700000;
        }
        .page-header {
            margin-bottom: 20px;
        }
        .page-title h1 {
            font-size: 16px;
            font-weight: bold;
        }
        .course-header {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 20px 0 10px;
            color: #8B0000;
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
                    <h1>Working Students</h1><br>
                </div>
            </div>
            <div class="search-container">
                <select class="search-field-select" id="searchField" aria-label="Select search field">
                    <option value="all">All Fields</option>
                    <option value="student_ID">Student ID</option>
                    <option value="full_name">Full Name</option>
                    <option value="stud_section">Section</option>
                    <option value="stud_course">Course</option>
                    <option value="ojt_status">OJT Status</option>
                    <option value="deployedCompany">Deployed Company</option>
                    <option value="company_address">Company Address</option>
                    <option value="phone_number">Company Phone</option>
                </select>
                <input type="text" class="search-box" placeholder="Search working students..." id="userSearch" aria-label="Search working students">
                <button class="search-button" id="searchButton" aria-label="Search">Search</button>
            </div>
            <div class="button-container">
                <button class="download-btn" id="downloadAll" aria-label="Download all working student data">Download All</button>
            </div>
            <div class="table-container">
                <?php
                try {
                    // Fetch distinct courses
                    $courseStmt = $conn->prepare("SELECT DISTINCT stud_course FROM working_students_data ORDER BY stud_course");
                    $courseStmt->execute();
                    $courses = $courseStmt->fetchAll(PDO::FETCH_COLUMN);

                    if (empty($courses)) {
                        // Dummy data for empty results
                        $dummyData = [
                            [
                                'id' => 0,
                                'student_ID' => 'WS001',
                                'full_name' => 'John Michael Doe',
                                'stud_section' => 'WS-101',
                                'stud_course' => 'BSIT',
                                'ojt_status' => 'Ongoing',
                                'deployedCompany' => 'Tech Corp',
                                'company_address' => '123 Tech St, City',
                                'phone_number' => '(123) 456-7890'
                            ],
                            [
                                'id' => 0,
                                'student_ID' => 'WS002',
                                'full_name' => 'Jane Marie Smith',
                                'stud_section' => 'WS-101',
                                'stud_course' => 'BSCS',
                                'ojt_status' => 'Ongoing',
                                'deployedCompany' => 'Soft Solutions',
                                'company_address' => '456 Soft Ave, City',
                                'phone_number' => '(123) 456-7891'
                            ],
                            [
                                'id' => 0,
                                'student_ID' => 'WS003',
                                'full_name' => 'Alex Robert Johnson',
                                'stud_section' => 'WS-101',
                                'stud_course' => 'BSIT',
                                'ojt_status' => 'Completed',
                                'deployedCompany' => 'Data Dynamics',
                                'company_address' => '789 Data Rd, City',
                                'phone_number' => '(123) 456-7892'
                            ]
                        ];
                        $courses = ['BSIT', 'BSCS'];
                        $studentsByCourse = [
                            'BSIT' => [
                                $dummyData[0],
                                $dummyData[2]
                            ],
                            'BSCS' => [$dummyData[1]]
                        ];
                    } else {
                        $studentsByCourse = [];
                        foreach ($courses as $course) {
                            $stmt = $conn->prepare("
                                SELECT 
                                    s.id,
                                    s.student_ID,
                                    CONCAT(s.first_name, ' ', COALESCE(s.middle_name, ''), ' ', s.last_name) AS full_name,
                                    s.stud_section,
                                    s.stud_course,
                                    s.ojt_status,
                                    s.stud_hte AS deployedCompany,
                                    sup.company_address,
                                    sup.phone_number
                                FROM working_students_data s
                                LEFT JOIN supervisor sup ON s.stud_hte = sup.company_name
                                WHERE s.stud_course = :course
                                ORDER BY s.stud_section
                            ");
                            $stmt->execute(['course' => $course]);
                            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (!empty($students)) {
                                $studentsByCourse[$course] = $students;
                            }
                        }
                        // If no valid students after fetching, use dummy data
                        if (empty($studentsByCourse)) {
                            $courses = ['BSIT', 'BSCS'];
                            $studentsByCourse = [
                                'BSIT' => [
                                    $dummyData[0],
                                    $dummyData[2]
                                ],
                                'BSCS' => [$dummyData[1]]
                            ];
                        }
                    }

                    // Render tables for each course
                    foreach ($courses as $index => $course) {
                        if (isset($studentsByCourse[$course])) {
                            $students = $studentsByCourse[$course];
                            echo '<div class="course-header">Course: ' . htmlspecialchars($course) . '</div>';
                            echo '<button class="download-section-btn" data-course="' . htmlspecialchars($course) . '" id="downloadCourse' . $index . '" aria-label="Download course ' . htmlspecialchars($course) . ' data">Download Course</button>';
                            echo '<table class="company-table" id="courseTable' . $index . '">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>Student ID</th>';
                            echo '<th>Full Name</th>';
                            echo '<th>Section</th>';
                            echo '<th>Course</th>';
                            echo '<th>OJT Status</th>';
                            echo '<th>Deployed Company</th>';
                            echo '<th>Company Address</th>';
                            echo '<th>Company Phone</th>';
                            echo '<th>Action</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            foreach ($students as $student) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($student['student_ID'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($student['full_name'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($student['stud_section'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($student['stud_course'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($student['ojt_status'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($student['deployedCompany'] ?? 'Not Assigned') . '</td>';
                                echo '<td>' . htmlspecialchars($student['company_address'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($student['phone_number'] ?? 'N/A') . '</td>';
                                echo '<td>
                                        <button class="view-profile-btn" data-id="' . htmlspecialchars($student['id']) . '" ' . ($student['id'] == 0 ? 'disabled' : '') . '>View Profile</button>
                                      </td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Database error: " . $e->getMessage());
                    // Use dummy data on database error
                    $dummyData = [
                        [
                            'id' => 0,
                            'student_ID' => 'WS001',
                            'full_name' => 'John Michael Doe',
                            'stud_section' => 'WS-101',
                            'stud_course' => 'BSIT',
                            'ojt_status' => 'Ongoing',
                            'deployedCompany' => 'Tech Corp',
                            'company_address' => '123 Tech St, City',
                            'phone_number' => '(123) 456-7890'
                        ],
                        [
                            'id' => 0,
                            'student_ID' => 'WS002',
                            'full_name' => 'Jane Marie Smith',
                            'stud_section' => 'WS-101',
                            'stud_course' => 'BSCS',
                            'ojt_status' => 'Ongoing',
                            'deployedCompany' => 'Soft Solutions',
                            'company_address' => '456 Soft Ave, City',
                            'phone_number' => '(123) 456-7891'
                        ],
                        [
                            'id' => 0,
                            'student_ID' => 'WS003',
                            'full_name' => 'Alex Robert Johnson',
                            'stud_section' => 'WS-101',
                            'stud_course' => 'BSIT',
                            'ojt_status' => 'Completed',
                            'deployedCompany' => 'Data Dynamics',
                            'company_address' => '789 Data Rd, City',
                            'phone_number' => '(123) 456-7892'
                        ]
                    ];
                    $courses = ['BSIT', 'BSCS'];
                    $studentsByCourse = [
                        'BSIT' => [
                            $dummyData[0],
                            $dummyData[2]
                        ],
                        'BSCS' => [$dummyData[1]]
                    ];
                    foreach ($courses as $index => $course) {
                        $students = $studentsByCourse[$course];
                        echo '<div class="course-header">Course: ' . htmlspecialchars($course) . '</div>';
                        echo '<button class="download-section-btn" data-course="' . htmlspecialchars($course) . '" id="downloadCourse' . $index . '" aria-label="Download course ' . htmlspecialchars($course) . ' data">Download Course</button>';
                        echo '<table class="company-table" id="courseTable' . $index . '">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Student ID</th>';
                        echo '<th>Full Name</th>';
                        echo '<th>Section</th>';
                        echo '<th>Course</th>';
                        echo '<th>OJT Status</th>';
                        echo '<th>Deployed Company</th>';
                        echo '<th>Company Address</th>';
                        echo '<th>Company Phone</th>';
                        echo '<th>Action</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        foreach ($students as $student) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($student['student_ID']) . '</td>';
                            echo '<td>' . htmlspecialchars($student['full_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($student['stud_section']) . '</td>';
                            echo '<td>' . htmlspecialchars($student['stud_course']) . '</td>';
                            echo '<td>' . htmlspecialchars($student['ojt_status']) . '</td>';
                            echo '<td>' . htmlspecialchars($student['deployedCompany']) . '</td>';
                            echo '<td>' . htmlspecialchars($student['company_address']) . '</td>';
                            echo '<td>' . htmlspecialchars($student['phone_number']) . '</td>';
                            echo '<td>
                                    <button class="view-profile-btn" data-id="' . htmlspecialchars($student['id']) . '" disabled>View Profile</button>
                                  </td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                    }
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
            // Search functionality across all tables
            function performSearch() {
                var value = $("#userSearch").val().toLowerCase().trim();
                var field = $("#searchField").val();
                var $tables = $(".company-table");
                var anyVisible = false;

                $tables.each(function() {
                    var $rows = $(this).find('tbody tr');
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
                                'student_ID': 0,
                                'full_name': 1,
                                'stud_section': 2,
                                'stud_course': 3,
                                'ojt_status': 4,
                                'deployedCompany': 5,
                                'company_address': 6,
                                'phone_number': 7
                            }[field];
                            var cellText = $row.find('td').eq(columnIndex).text().toLowerCase();
                            showRow = cellText.includes(value);
                        }

                        $row.toggle(showRow);
                        if (showRow) anyVisible = true;
                    });

                    // Hide course header and download button if no rows are visible
                    var $courseHeader = $(this).prevAll('.course-header').first();
                    var $downloadButton = $(this).prevAll('.download-section-btn').first();
                    var hasVisibleRows = $(this).find('tbody tr:visible').length > 0;
                    $courseHeader.toggle(hasVisibleRows);
                    $downloadButton.toggle(hasVisibleRows);
                });

                // Show message if no results
                if (!anyVisible) {
                    $(".table-container").append('<div class="no-results" style="text-align: center; padding: 20px;">No working students found.</div>');
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
                    $(".course-header, .download-section-btn").show();
                    $(".no-results").remove();
                }
            });

            // Sort each table by Section (index 2) within Course
            $('table.company-table').each(function() {
                var table = $(this);
                var tbody = table.find('tbody');
                var rows = tbody.find('tr').toArray();

                rows.sort(function(a, b) {
                    var aValue = $(a).find('td').eq(2).text().toLowerCase();
                    var bValue = $(b).find('td').eq(2).text().toLowerCase();
                    return aValue.localeCompare(bValue);
                });

                tbody.empty();
                $.each(rows, function(index, row) {
                    tbody.append(row);
                });
            });

            // View Profile button click handler
            $(document).on("click", ".view-profile-btn:not(:disabled)", function() {
                var id = $(this).data("id");
                window.location.href = "admin_view_working_student_profile.php?id=" + id;
            });

            // Download Course button click handler
            $(document).on("click", ".download-section-btn", function() {
                var course = $(this).data("course");
                var tableId = $(this).next('table').attr('id');
                var table = $('#' + tableId);
                var rows = table.find('tbody tr:visible'); // Only visible rows
                var csvContent = "Student ID,Full Name,Section,Course,OJT Status,Deployed Company,Company Address,Company Phone\n";

                rows.each(function() {
                    var row = $(this);
                    var cols = row.find('td');
                    var rowData = [];

                    // Collect data from all columns except the Action column (index 8)
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
                link.setAttribute("download", "Working_Students_Course_" + course + ".csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Download All button click handler
            $(document).on("click", "#downloadAll", function() {
                var tables = $('.company-table');
                var csvContent = "Student ID,Full Name,Section,Course,OJT Status,Deployed Company,Company Address,Company Phone\n";

                tables.each(function() {
                    var rows = $(this).find('tbody tr:visible'); // Only visible rows
                    rows.each(function() {
                        var row = $(this);
                        var cols = row.find('td');
                        var rowData = [];

                        // Collect data from all columns except the Action column (index 8)
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
                });

                // Create a Blob and trigger download
                var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement("a");
                var url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "Working_Students_All.csv");
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