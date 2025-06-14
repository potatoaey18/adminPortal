<?php
include '../connection/config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
    header("Location: index.php");
    exit;
}

$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
$portal = filter_input(INPUT_GET, 'portal', FILTER_SANITIZE_STRING);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$action = $id ? 'update_' . $type : 'create_' . $type;

if (!in_array($type, ['announcement', 'faq']) || !in_array($portal, ['Student', 'Adviser', 'HTE', 'All'])) {
    $_SESSION['status'] = "Invalid request.";
    $_SESSION['alert'] = "Error";
    $_SESSION['status-code'] = "error";
    header("Location: dashboard.php");
    exit;
}

$title = '';
$content = '';
$question = '';
$answer = '';

if ($id) {
    $table = $type === 'announcement' ? 'announcements' : 'faqs';
    $field1 = $type === 'announcement' ? 'title' : 'question';
    $field2 = $type === 'announcement' ? 'content' : 'answer';
    $stmt = $conn->prepare("SELECT $field1, $field2 FROM $table WHERE id = ? AND created_by = ?");
    $stmt->execute([$id, $_SESSION['auth_user']['admin_id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        if ($type === 'announcement') {
            $title = $data['title'];
            $content = $data['content'];
        } else {
            $question = $data['question'];
            $answer = $data['answer'];
        }
    } else {
        $_SESSION['status'] = "Content not found or unauthorized.";
        $_SESSION['alert'] = "Error";
        $_SESSION['status-code'] = "error";
        header("Location: dashboard.php");
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
    <title>OJT Web Portal: Edit <?php echo ucfirst($type); ?></title>
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/menubar/sidebar.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/lib/sweetalert/sweetalert.css" rel="stylesheet">
    <link rel="stylesheet" href="endorsement-css/endorsement-moa.css">
    <style>
        .form-container h2 {
            font-size: 16px;
            color:#444444;
            margin-bottom: 20px;
        }
        .form-container input, .form-container textarea, .form-container select {
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
                    <a href="dashboard.php" class="back-button">
                        <span class="back-icon"><img src="images/less-than.png" alt="Back"></span>
                        Back
                    </a>
                </div>
            </div>
                <h2><?php echo $id ? 'Edit' : 'Add New'; ?> <?php echo ucfirst($type); ?> for <?php echo $portal; ?></h2>
                <form action="manage_content.php" method="POST">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($id): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>
                    <input type="hidden" name="portal" value="<?php echo $portal; ?>">
                    <?php if ($type === 'announcement'): ?>
                        <label for="title">Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" placeholder="Announcement Title" required>
                        <label for="content">Content</label>
                        <textarea name="content" placeholder="Announcement Content" rows="4" required><?php echo htmlspecialchars($content); ?></textarea>
                    <?php else: ?>
                        <label for="question">Question</label>
                        <input type="text" name="question" value="<?php echo htmlspecialchars($question); ?>" placeholder="Question" required>
                        <textarea name="answer" placeholder="Answer" rows="4" required><?php echo htmlspecialchars($answer); ?></textarea>
                    <?php endif; ?>
                    <div class="action-buttons">
                        <button type="submit" class="action-button" aria-label="<?php echo $id ? 'Update' : 'Add'; ?> <?php echo ucfirst($type); ?>">
                            <i class="ti-save"></i> <?php echo $id ? 'Update' : 'Add'; ?> <?php echo ucfirst($type); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="js/lib/jquery.min.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/preloader/pace.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/lib/sweetalert/sweetalert.min.js"></script>
    <script src="js/lib/sweetalert/sweetalert.init.js"></script>
    <?php 
    if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
    ?>
        <script>
        sweetAlert("<?php echo $_SESSION['alert']; ?>", "<?php echo $_SESSION['status']; ?>", "<?php echo $_SESSION['status-code']; ?>");
        </script>
    <?php
        unset($_SESSION['status']);
    }
    ?>
</body>
</html>