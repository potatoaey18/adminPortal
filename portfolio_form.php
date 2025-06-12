<?php
include '../connection/config.php';
session_start();

if (!isset($_SESSION['auth_user']['userid']) || $_SESSION['auth_user']['userid'] == 0) {
    $_SESSION['status'] = "Unauthorized access.";
    $_SESSION['alert'] = "Error";
    $_SESSION['status-code'] = "error";
    header("Location: index.php");
    exit;
}

$editMode = false;
$portfolio = [
    'id' => '',
    'first_name' => '',
    'middle_name' => '',
    'last_name' => '',
    'section' => '',
    'date_submitted' => date('Y-m-d')
];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $editMode = true;
    try {
        $stmt = $conn->prepare("
            SELECT id, first_name, middle_name, last_name, section, date_submitted 
            FROM portfolios 
            WHERE id = :id AND created_by = :created_by
        ");
        $stmt->execute([
            'id' => $_GET['id'],
            'created_by' => $_SESSION['auth_user']['userid']
        ]);
        $portfolio = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$portfolio) {
            $_SESSION['status'] = "Portfolio not found or you don't have permission.";
            $_SESSION['alert'] = "Error";
            $_SESSION['status-code'] = "error";
            header("Location: portfolio.php");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['status'] = "Failed to load portfolio: " . $e->getMessage();
        $_SESSION['alert'] = "Error";
        $_SESSION['status-code'] = "error";
        header("Location: portfolio.php");
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
    <title>OJT Web Portal: <?php echo $editMode ? 'Edit Portfolio' : 'Add Portfolio'; ?></title>
    <link rel="shortcut icon" href="images/pupLogo.png">
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
        .form-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container label {
            display: block;
            font-weight: 600;
            color: #700000;
            margin: 10px 0 5px;
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
                        <a href="portfolio.php" class="back-button">
                            <span class="back-icon"><img src="images/less-than.png" alt="Back"></span>
                            Back
                        </a>
                    </div>
                </div>
                <h2><?php echo $editMode ? 'Edit Portfolio' : 'Add Portfolio'; ?></h2>
                <form action="manage_portfolio.php" method="POST">
                    <input type="hidden" name="action" value="<?php echo $editMode ? 'update' : 'create'; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($portfolio['id']); ?>">
                    <?php endif; ?>
                    <label for="first_name">First Name <span style="color: #8B0000;">*</span></label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($portfolio['first_name']); ?>" placeholder="First Name" required maxlength="255">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" name="middle_name" id="middle_name" value="<?php echo htmlspecialchars($portfolio['middle_name']); ?>" placeholder="Middle Name" maxlength="255">
                    <label for="last_name">Last Name <span style="color: #8B0000;">*</span></label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($portfolio['last_name']); ?>" placeholder="Last Name" required maxlength="255">
                    <label for="section">Section <span style="color: #8B0000;">*</span></label>
                    <input type="text" name="section" id="section" value="<?php echo htmlspecialchars($portfolio['section']); ?>" placeholder="Section" required maxlength="50">
                    <label for="date_submitted">Date Submitted <span style="color: #8B0000;">*</span></label>
                    <input type="date" name="date_submitted" id="date_submitted" value="<?php echo htmlspecialchars($portfolio['date_submitted']); ?>" required>
                    <div class="action-buttons">
                        <button type="submit" class="action-button" aria-label="<?php echo $editMode ? 'Update Portfolio' : 'Add Portfolio'; ?>">
                            <i class="ti-save"></i> <?php echo $editMode ? 'Update' : 'Add'; ?> Portfolio
                        </button>
                        <a href="portfolio.php" class="action-button cancel-button" aria-label="Cancel">
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
    <script>
        $(document).ready(function() {
            // Client-side form validation
            $('#portfolioForm').on('submit', function(e) {
                var firstName = $('#first_name').val().trim();
                var lastName = $('#last_name').val().trim();
                var section = $('#section').val().trim();
                var dateSubmitted = $('#date_submitted').val();

                if (!firstName || firstName.length > 255) {
                    e.preventDefault();
                    customAlert("Error", "First name is required and must be 255 characters or less.", "error");
                    return;
                }
                if (!lastName || lastName.length > 255) {
                    e.preventDefault();
                    customAlert("Error", "Last name is required and must be 255 characters or less.", "error");
                    return;
                }
                if (!section || section.length > 50) {
                    e.preventDefault();
                    customAlert("Error", "Section is required and must be 50 characters or less.", "error");
                    return;
                }
                if (!dateSubmitted) {
                    e.preventDefault();
                    customAlert("Error", "Date submitted is required.", "error");
                    return;
                }
            });
        });
    </script>
</body>
</html>