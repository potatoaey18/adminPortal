<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Fetch courses and sections
try {
    $stmt = $conn->prepare("SELECT DISTINCT course FROM courses_sections ORDER BY course ASC");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $conn->prepare("SELECT course, section FROM courses_sections ORDER BY course ASC, section ASC");
    $stmt->execute();
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching courses/sections: " . $e->getMessage());
    $courses = [];
    $sections = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-name" content="focus" />
    <title>OJT Web Portal: Coordinators</title>
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/menubar/sidebar.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/lib/sweetalert/sweetalert.css" rel="stylesheet">
    <link rel="stylesheet" href="endorsement-css/endorsement-moa.css">
    <style>
        .search-container {
            margin-bottom: 20px;
            display: flex !important;
            align-items: center;
            gap: 10px;
            position: relative;
            width: 100% !important;
            overflow: visible !important;
            z-index: 10;
            min-height: 40px; /* Ensure container has height */
        }
        .search-box {
            width: 50% !important;
            padding: 8px !important;
            border-radius: 4px !important;
            border: 1px solid #ccc !important;
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
            background-color: #fff !important;
            z-index: 100 !important;
            box-sizing: border-box;
            min-height: 34px; /* Ensure input has visible height */
        }
        .search-button {
            padding: 8px 20px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .add-coordinator-btn {
            padding: 0px 20px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            max-height: 33px;
            margin-top: 10px;
        }
        .search-button:hover, .add-coordinator-btn:hover {
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
        .view-profile-btn, .download-btn, .edit-btn, .delete-btn {
            background-color: #8B0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin: 10px 5px;
        }
        .view-profile-btn:hover, .download-btn:hover, .edit-btn:hover, .delete-btn:hover {
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
            gap: 10px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .form-container h2 {
            font-size: 16px;
            color: #444444;
            margin-bottom: 20px;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #444444;
        }
        .form-container input, .form-container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .form-container input[readonly] {
            background-color: #f0f0f0;
            cursor: default;
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
        .back-button {
            display: inline-flex;
            align-items: center;
            color: #8B0000;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .back-button .back-icon img {
            width: 16px;
            height: 16px;
            margin-right: 5px;
        }
        .back-button:hover {
            color: #700000;
        }
        /* Override potential input[type="text"] conflicts */
        input[type="text"].search-box {
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
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
            .search-container {
                flex-direction: column;
                align-items: stretch;
                width: 100% !important;
            }
            .search-box {
                display: block !important;
                width: 100% !important;
                visibility: visible !important;
                opacity: 1 !important;
                min-height: 34px !important;
            }
            .search-field-select, .search-button {
                width: 100%;
            }
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
                <input type="text" class="search-box" placeholder="Search coordinators..." id="userSearch" aria-label="Search coordinators" style="display: inline-block !important; visibility: visible !important; opacity: 1 !important; width: 50%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; background-color: #fff; z-index: 100;">
                <button class="search-button" id="searchButton" aria-label="Search">Search</button>
            </div>
            <div class="button-container">
                <button class="add-coordinator-btn" id="addCoordinator" aria-label="Add new coordinator">Add Coordinator</button>
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
                                        <button class="edit-btn" data-id="' . htmlspecialchars($coordinator['id']) . '">Edit</button>
                                        <button class="delete-btn" data-id="' . htmlspecialchars($coordinator['id']) . '">Delete</button>
                                      </td>';
                                echo '</tr>';
                            }

                            if ($stmt->rowCount() == 0) {
                                echo '<tr><td colspan="8">No coordinators found.</td></tr>';
                            }
                        } catch (PDOException $e) {
                            error_log("Database error: " . $e->getMessage());
                            echo '<tr><td colspan="8">Error fetching coordinator data: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Coordinator -->
    <div id="coordinatorModal" class="modal">
        <div class="modal-content">
            <div class="form-container">
                <a href="javascript:void(0)" class="back-button close">
                    <span class="back-icon"><img src="images/less-than.png" alt="Back"></span>
                    Back
                </a>
                <h2 id="modalTitle">Add Coordinator</h2>
                <form id="coordinatorForm">
                    <input type="hidden" id="coordinatorId" name="coordinatorId">
                    <label for="firstName">First Name *</label>
                    <input type="text" id="firstName" name="firstName" placeholder="First Name" required pattern="[A-Za-z\s]+" title="First name must contain only letters and spaces">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName" placeholder="Middle Name" pattern="[A-Za-z\s]*" title="Middle name must contain only letters and spaces">
                    <label for="lastName">Last Name *</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Last Name" required pattern="[A-Za-z\s]+" title="Last name must contain only letters and spaces">
                    <label for="facultyId">Faculty ID *</label>
                    <input type="text" id="facultyId" name="facultyId" placeholder="Faculty ID" required pattern="[A-Za-z0-9\-]+" title="Faculty ID must contain letters, numbers, or hyphens">
                    <label for="coorDept">Department *</label>
                    <input type="text" id="coorDept" name="coorDept" value="ITECH" readonly required>
                    <label for="courseHandled">Course Handled *</label>
                    <select id="courseHandled" name="courseHandled" required>
                        <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                            <option value="<?= htmlspecialchars($course) ?>"><?= htmlspecialchars($course) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="assignedSection">Assigned Section</label>
                    <select id="assignedSection" name="assignedSection">
                        <option value="">Select Section</option>
                    </select>
                    <label for="secondAssignedSection">Second Assigned Section</label>
                    <select id="secondAssignedSection" name="secondAssignedSection">
                        <option value="">Select Section</option>
                    </select>
                    <label for="phoneNumber">Phone Number *</label>
                    <input type="text" id="phoneNumber" name="phoneNumber" placeholder="Phone Number" required pattern="^\d{10,13}$" title="Phone number must be 10-13 digits">
                    <label for="coordinatorsEmail">Email *</label>
                    <input type="email" id="coordinatorsEmail" name="coordinatorsEmail" placeholder="Email" required>
                    <label for="completeAddress">Complete Address *</label>
                    <input type="text" id="completeAddress" name="completeAddress" placeholder="Complete Address" required>
                    <div class="action-buttons">
                        <button type="submit" class="action-button" aria-label="Save Coordinator">
                            <i class="ti-save"></i> Save Coordinator
                        </button>
                        <button type="button" class="action-button cancel-button close" aria-label="Cancel">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/lib/jquery.min.js"></script>
    <script>
        // Fallback for jQuery if local file fails
        window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
    </script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/preloader/pace.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/lib/sweetalert/sweetalert.min.js"></script>
    <script src="js/lib/sweetalert/sweetalert.init.js"></script>
    <script>
        $(document).ready(function() {
            // Enhanced debugging for search input
            const $searchInput = $("#userSearch");
            console.log("Search input found:", $searchInput.length);
            if ($searchInput.length) {
                console.log("Search input display:", $searchInput.css("display"));
                console.log("Search input visibility:", $searchInput.css("visibility"));
                console.log("Search input opacity:", $searchInput.css("opacity"));
                console.log("Search input parent:", $searchInput.parent().prop("tagName"), $searchInput.parent().attr("class"));
            } else {
                console.log("Search input not found in DOM!");
            }

            // Section data
            const sectionsData = <?php echo json_encode($sections); ?>;

            // Update section dropdowns based on selected course
            function updateSectionDropdowns(selectedCourse) {
                const $assignedSection = $("#assignedSection");
                const $secondAssignedSection = $("#secondAssignedSection");
                
                $assignedSection.empty().append('<option value="">Select Section</option>');
                $secondAssignedSection.empty().append('<option value="">Select Section</option>');

                sectionsData.forEach(function(item) {
                    if (item.course === selectedCourse) {
                        const option = `<option value="${item.section}">${item.section}</option>`;
                        $assignedSection.append(option);
                        $secondAssignedSection.append(option);
                    }
                });
            }

            // Course change handler
            $("#courseHandled").on("change", function() {
                const selectedCourse = $(this).val();
                updateSectionDropdowns(selectedCourse);
            });

            // Modal handling
            const modal = $("#coordinatorModal");
            const closeBtn = $(".close");
            const coordinatorForm = $("#coordinatorForm");

            // Open modal for adding new coordinator
            $("#addCoordinator").on("click", function() {
                $("#modalTitle").text("Add Coordinator");
                coordinatorForm[0].reset();
                $("#coordinatorId").val("");
                $("#coorDept").val("ITECH");
                $("#assignedSection").empty().append('<option value="">Select Section</option>');
                $("#secondAssignedSection").empty().append('<option value="">Select Section</option>');
                modal.show();
            });

            // Open modal for editing coordinator
            $(document).on("click", ".edit-btn", function() {
                const id = $(this).data("id");
                $("#modalTitle").text("Edit Coordinator");
                $("#coordinatorId").val(id);

                // Fetch coordinator data
                $.ajax({
                    url: "crud_coordinator.php",
                    type: "POST",
                    data: { action: "get", id: id },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            $("#firstName").val(data.first_name);
                            $("#middleName").val(data.middle_name || "");
                            $("#lastName").val(data.last_name);
                            $("#facultyId").val(data.faculty_id);
                            $("#coorDept").val("ITECH");
                            $("#courseHandled").val(data.course_handled);
                            updateSectionDropdowns(data.course_handled);
                            $("#assignedSection").val(data.assigned_section || "");
                            $("#secondAssignedSection").val(data.second_assigned_section || "");
                            $("#phoneNumber").val(data.phone_number);
                            $("#coordinatorsEmail").val(data.coordinators_email);
                            $("#completeAddress").val(data.complete_address);
                            modal.show();
                        } else {
                            swal("Error", response.message, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        swal("Error", "Failed to fetch coordinator data: " + error, "error");
                    }
                });
            });

            // Close modal
            closeBtn.on("click", function() {
                modal.hide();
                coordinatorForm[0].reset();
            });

            // Close modal when clicking outside
            $(window).on("click", function(event) {
                if (event.target == modal[0]) {
                    modal.hide();
                    coordinatorForm[0].reset();
                }
            });

            // Form submission
            coordinatorForm.on("submit", function(e) {
                e.preventDefault();
                if (!this.checkValidity()) {
                    this.reportValidity();
                    return;
                }
                const formData = new FormData(this);
                const action = $("#coordinatorId").val() ? "update" : "create";
                formData.append("action", action);

                $.ajax({
                    url: "crud_coordinator.php",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            swal({
                                title: "Success",
                                text: response.message,
                                type: "success"
                            }, function() {
                                location.reload();
                            });
                        } else {
                            swal("Error", response.message, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        swal("Error", "An error occurred while processing the request: " + error, "error");
                    }
                });
            });

            // Delete coordinator
            $(document).on("click", ".delete-btn", function() {
                const id = $(this).data("id");
                swal({
                    title: "Are you sure?",
                    text: "This action cannot be undone!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#8B0000",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                }, function(isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            url: "crud_coordinator.php",
                            type: "POST",
                            data: { action: "delete", id: id },
                            dataType: "json",
                            success: function(response) {
                                if (response.success) {
                                    swal({
                                        title: "Deleted!",
                                        text: response.message,
                                        type: "success"
                                    }, function() {
                                        location.reload();
                                    });
                                } else {
                                    swal("Error", response.message, "error");
                                }
                            },
                            error: function(xhr, status, error) {
                                swal("Error", "Failed to delete coordinator: " + error, "error");
                            }
                        });
                    }
                });
            });

            // Search functionality
            function performSearch() {
                const value = $("#userSearch").val().toLowerCase().trim();
                const field = $("#searchField").val();
                const $rows = $(".company-table tbody tr:not(.no-results)");

                $rows.each(function() {
                    const $row = $(this);
                    let showRow = false;

                    if (field === "all") {
                        const rowText = $row.text().toLowerCase();
                        showRow = rowText.includes(value);
                    } else {
                        const columnIndex = {
                            'faculty_id': 0,
                            'full_name': 1,
                            'coor_dept': 2,
                            'course_handled': 3,
                            'assigned_section': 4,
                            'phone_number': 5,
                            'coordinators_email': 6
                        }[field];
                        const cellText = $row.find('td').eq(columnIndex).text().toLowerCase();
                        showRow = cellText.includes(value);
                    }

                    $row.toggle(showRow);
                });

                $(".no-results").remove();
                if ($rows.filter(':visible').length === 0) {
                    $(".company-table tbody").append('<tr class="no-results"><td colspan="8">No coordinators found.</td></tr>');
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
                    $(".company-table tbody tr").show();
                    $(".no-results").remove();
                }
            });

            // View Profile button click handler
            $(document).on("click", ".view-profile-btn", function() {
                const id = $(this).data("id");
                window.location.href = "admin_view_coordinator_profile.php?id=" + id;
            });

            // Download All button click handler
            $(document).on("click", "#downloadAll", function() {
                const table = $('.company-table');
                const rows = table.find('tbody tr').filter(':visible');
                let csvContent = "Faculty ID,Full Name,Department,Course Handled,Assigned Section,Phone Number,Email\n";

                rows.each(function() {
                    const row = $(this);
                    const cols = row.find('td');
                    const rowData = [];

                    for (let i = 0; i < cols.length - 1; i++) {
                        let text = cols.eq(i).text().trim();
                        text = text.replace(/"/g, '""');
                        if (text.includes(',') || text.includes('"') || text.includes('\n')) {
                            text = `"${text}"`;
                        }
                        rowData.push(text);
                    }

                    csvContent += rowData.join(',') + '\n';
                });

                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement("a");
                const url = URL.createObjectURL(blob);
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