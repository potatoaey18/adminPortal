<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
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
    <title>OJT Web Portal: Portfolios</title>
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
        }
        .search-box {
            width: 75%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .search-button, .action-btn {
            padding: 8px 15px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
        }
        .search-button:hover, .action-btn:hover {
            background-color: #700000;
        }
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 20px;
        }
        .company-table {
            width: 100%;
            border-collapse: collapse;
        }
        .company-table th {
            background-color: #fff;
            color: #700000;
            text-align: center;
            padding: 10px 20px;
            min-width: 100px;
            border: 2px solid #700000;
            font-weight: 600;
        }
        .company-table td {
            padding: 10px 20px;
            border: 2px solid #700000;
            text-align: center;
            color: #000;
        }
        .company-table tr:nth-child(odd) {
            background-color: #f2f2f2;
        }
        .download-section-btn, .download-btn {
            background-color: #8B0000;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin: 5px;
        }
        .download-section-btn:hover, .download-btn:hover {
            background-color: #700000;
        }
        .page-header {
            margin-bottom: 20px;
        }
        .page-title h1 {
            font-size: 16px;
            font-weight: bold;
        }
        .section-header {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 20px 0 10px;
            color: #8B0000;
        }
        .button-container {
            margin-bottom: 20px;
        }
        .swal2-confirm {
            background-color: #8B0000 !important;
            color: white !important;
        }
        .swal2-cancel {
            background-color: #6c757d !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <?php require_once 'templates/admin_navbar.php'; ?>

    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="page-header">
                <div class="page-title"><br>
                    <h1>Portfolios</h1><br>
                </div>
            </div>
            <div class="button-container">
                <a href="portfolio_form.php" class="action-btn" aria-label="Add new portfolio">Add Portfolio</a>
                <button class="download-btn" id="downloadAll" aria-label="Download all portfolio data">Download All</button>
            </div>
            <div class="search-container">
                <input type="text" class="search-box" placeholder="Search by name..." id="userSearch" aria-label="Search portfolios">
                <button class="search-button" aria-label="Search">Search</button>
            </div>
            <div class="table-container">
                <?php
                try {
                    $sectionStmt = $conn->prepare("SELECT DISTINCT section FROM portfolios ORDER BY section");
                    $sectionStmt->execute();
                    $sections = $sectionStmt->fetchAll(PDO::FETCH_COLUMN);

                    if (empty($sections)) {
                        $dummyData = [
                            [
                                'id' => 0,
                                'full_name' => 'John Michael Doe',
                                'section' => 'PORT-101',
                                'date_submitted' => '2025-05-01'
                            ],
                            [
                                'id' => 0,
                                'full_name' => 'Jane Marie Smith',
                                'section' => 'PORT-101',
                                'date_submitted' => '2025-05-02'
                            ],
                            [
                                'id' => 0,
                                'full_name' => 'Alex Robert Johnson',
                                'section' => 'PORT-101',
                                'date_submitted' => '2025-05-03'
                            ]
                        ];
                        $sections = ['PORT-101'];
                        $portfoliosBySection = ['PORT-101' => $dummyData];
                    } else {
                        $portfoliosBySection = [];
                        foreach ($sections as $section) {
                            $stmt = $conn->prepare("
                                SELECT 
                                    id,
                                    CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS full_name,
                                    section,
                                    date_submitted
                                FROM portfolios
                                WHERE section = :section
                            ");
                            $stmt->execute(['section' => $section]);
                            $portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if (!empty($portfolios)) {
                                $portfoliosBySection[$section] = $portfolios;
                            }
                        }
                        if (empty($portfoliosBySection)) {
                            $sections = ['PORT-101'];
                            $portfoliosBySection['PORT-101'] = $dummyData;
                        }
                    }

                    foreach ($sections as $index => $section) {
                        if (isset($portfoliosBySection[$section])) {
                            $portfolios = $portfoliosBySection[$section];
                            echo '<div class="section-header">Section: ' . htmlspecialchars($section) . '</div>';
                            echo '<button class="download-section-btn" data-section="' . htmlspecialchars($section) . '" id="downloadSection' . $index . '" aria-label="Download section ' . htmlspecialchars($section) . ' data">Download Section</button>';
                            echo '<table class="company-table" id="sectionTable' . $index . '">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>Name</th>';
                            echo '<th>Section</th>';
                            echo '<th>Date Submitted</th>';
                            echo '<th>Actions</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            foreach ($portfolios as $portfolio) {
                                echo '<tr data-id="' . htmlspecialchars($portfolio['id'] ?? '') . '">';
                                echo '<td>' . htmlspecialchars($portfolio['full_name'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($portfolio['section'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars($portfolio['date_submitted'] ?? 'N/A') . '</td>';
                                echo '<td>';
                                echo '<a href="portfolio_form.php?id=' . htmlspecialchars($portfolio['id']) . '" class="action-btn" ' . ($portfolio['id'] == 0 ? 'aria-disabled="true" style="pointer-events: none; opacity: 0.5;"' : '') . ' aria-label="Edit portfolio for ' . htmlspecialchars($portfolio['full_name']) . '">Edit</a>';
                                echo '<button class="action-btn delete-btn" data-id="' . htmlspecialchars($portfolio['id']) . '" ' . ($portfolio['id'] == 0 ? 'disabled' : '') . ' aria-label="Delete portfolio for ' . htmlspecialchars($portfolio['full_name']) . '">Delete</button>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Database error: " . $e->getMessage());
                    $dummyData = [
                        [
                            'id' => 0,
                            'full_name' => 'John Michael Doe',
                            'section' => 'PORT-101',
                            'date_submitted' => '2025-05-01'
                        ],
                        [
                            'id' => 0,
                            'full_name' => 'Jane Marie Smith',
                            'section' => 'PORT-101',
                            'date_submitted' => '2025-05-02'
                        ],
                        [
                            'id' => 0,
                            'full_name' => 'Alex Robert Johnson',
                            'section' => 'PORT-101',
                            'date_submitted' => '2025-05-03'
                        ]
                    ];
                    echo '<div class="section-header">Section: PORT-101</div>';
                    echo '<button class="download-section-btn" data-section="PORT-101" id="downloadSection0" aria-label="Download section PORT-101 data">Download Section</button>';
                    echo '<table class="company-table" id="sectionTable0">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>Name</th>';
                    echo '<th>Section</th>';
                    echo '<th>Date Submitted</th>';
                    echo '<th>Actions</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    foreach ($dummyData as $portfolio) {
                        echo '<tr data-id="' . htmlspecialchars($portfolio['id']) . '">';
                        echo '<td>' . htmlspecialchars($portfolio['full_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($portfolio['section']) . '</td>';
                        echo '<td>' . htmlspecialchars($portfolio['date_submitted']) . '</td>';
                        echo '<td>';
                        echo '<a href="portfolio_form.php?id=' . htmlspecialchars($portfolio['id']) . '" class="action-btn" aria-disabled="true" style="pointer-events: none; opacity: 0.5;" aria-label="Edit portfolio for ' . htmlspecialchars($portfolio['full_name']) . '">Edit</a>';
                        echo '<button class="action-btn delete-btn" data-id="' . htmlspecialchars($portfolio['id']) . '" disabled aria-label="Delete portfolio for ' . htmlspecialchars($portfolio['full_name']) . '">Delete</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Session messages
            <?php if (isset($_SESSION['status'])): ?>
                Swal.fire({
                    title: '<?php echo htmlspecialchars($_SESSION['alert']); ?>',
                    text: '<?php echo htmlspecialchars($_SESSION['status']); ?>',
                    icon: '<?php echo htmlspecialchars($_SESSION['status_code']); ?>',
                    confirmButtonColor: '#8B0000',
                    confirmButtonText: 'OK'
                }).then(() => {
                    <?php unset($_SESSION['status'], $_SESSION['alert'], $_SESSION['status_code']); ?>
                    location.reload();
                });
            <?php endif; ?>

            // Search
            $("#userSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $(".company-table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            // Sort by Name
            $('table.company-table').each(function() {
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

            // Delete
            $(document).on('click', '.delete-btn:not(:disabled)', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#8B0000',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'manage_portfolio.php',
                            type: 'POST',
                            data: { action: 'delete', id: id },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: response.success,
                                        icon: 'success',
                                        confirmButtonColor: '#8B0000',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.error || 'Failed to delete portfolio',
                                        icon: 'error',
                                        confirmButtonColor: '#8B0000',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Failed to delete portfolio: ' + error,
                                    icon: 'error',
                                    confirmButtonColor: '#8B0000',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            });

            // Download Section
            $(document).on("click", ".download-section-btn", function() {
                var section = $(this).data("section");
                var tableId = $(this).next('table').attr('id');
                var table = $('#' + tableId);
                var rows = table.find('tbody tr');
                var csvContent = "Name,Section,Date Submitted\n";

                rows.each(function() {
                    var row = $(this);
                    var cols = row.find('td').slice(0, 3);
                    var rowData = [];

                    for (var i = 0; i < cols.length; i++) {
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
                link.setAttribute("download", "Portfolios_Section_" + section + ".csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Download All
            $(document).on("click", "#downloadAll", function() {
                var tables = $('.company-table');
                var csvContent = "Name,Section,Date Submitted\n";

                tables.each(function() {
                    var rows = $(this).find('tbody tr');
                    rows.each(function() {
                        var row = $(this);
                        var cols = row.find('td').slice(0, 3);
                        var rowData = [];

                        for (var i = 0; i < cols.length; i++) {
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

                var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                var link = document.createElement("a");
                var url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "Portfolios_All.csv");
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
</body>
</html>