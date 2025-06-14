<?php
include '../connection/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['upload'])) {
    $adminID = $_SESSION['auth_user']['admin_id'];
    $uploadDirectory = 'C:/xampp/htdocs/PUP/admin_file_images/'; // Absolute path for file operations
    $webPathPrefix = '/PUP/admin_file_images/'; // Relative path for web access

    // Create directory if it doesn't exist
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    // Validate file upload
    if (isset($_FILES['img_admin']) && $_FILES['img_admin']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        $fileType = $_FILES['img_admin']['type'];
        $fileSize = $_FILES['img_admin']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['alert'] = "Error!";
            $_SESSION['status'] = "Invalid file type. Only JPEG, PNG, and GIF are allowed.";
            $_SESSION['status-code'] = "error";
        } elseif ($fileSize > $maxFileSize) {
            $_SESSION['alert'] = "Error!";
            $_SESSION['status'] = "File size exceeds 5MB limit.";
            $_SESSION['status-code'] = "error";
        } else {
            // Sanitize filename
            $originalFilename = $_FILES['img_admin']['name'];
            $cleanFilename = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $originalFilename);
            $uniqueFilename = uniqid() . '-' . $cleanFilename;
            $imagePath = $uploadDirectory . $uniqueFilename; // Absolute path for saving
            $webImagePath = $webPathPrefix . $uniqueFilename; // Relative path for database/web

            try {
                // Check current profile picture
                $sql = $conn->prepare("SELECT admin_profile_picture FROM admin_account WHERE id = ?");
                $sql->execute([$adminID]);
                $row = $sql->fetch(PDO::FETCH_ASSOC);
                $currentImagePath = $row['admin_profile_picture'];

                // Delete old image if it exists
                if (!empty($currentImagePath)) {
                    $absoluteOldPath = str_replace($webPathPrefix, $uploadDirectory, $currentImagePath);
                    if (file_exists($absoluteOldPath)) {
                        unlink($absoluteOldPath);
                    }
                }

                // Move uploaded file
                if (move_uploaded_file($_FILES['img_admin']['tmp_name'], $imagePath)) {
                    // Update database with web path
                    $sql = $conn->prepare("UPDATE admin_account SET admin_profile_picture = ? WHERE id = ?");
                    if ($sql->execute([$webImagePath, $adminID])) {
                        // Log the action
                        date_default_timezone_set('Asia/Manila');
                        $date = date('F / d l / Y');
                        $time = date('g:i A');
                        $logs = 'Profile picture updated successfully.';

                        try {
                            $sql2 = $conn->prepare("INSERT INTO system_notification (student_id, logs, logs_date, logs_time, status) VALUES (?, ?, ?, ?, ?)");
                            $sql2->execute([0, $logs, $date, $time, 'Unread']);
                        } catch (PDOException $e) {
                            error_log("Notification error for adminID $adminID: " . $e->getMessage(), 3, 'C:/xampp/htdocs/PUP/adminportal/errors.log');
                        }

                        $_SESSION['alert'] = "Success!";
                        $_SESSION['status'] = "Profile picture updated successfully.";
                        $_SESSION['status-code'] = "success";

                        // Reload admin data to reflect the new image
                        $stmt = $conn->prepare("SELECT * FROM admin_account WHERE id = ?");
                        $stmt->execute([$adminID]);
                        $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    } else {
                        $_SESSION['alert'] = "Error!";
                        $_SESSION['status'] = "Failed to update database.";
                        $_SESSION['status-code'] = "error";
                        error_log("Database update failed for adminID $adminID: admin_profile_picture = $webImagePath", 3, 'C:/xampp/htdocs/PUP/adminportal/errors.log');
                    }
                } else {
                    $_SESSION['alert'] = "Error!";
                    $_SESSION['status'] = "Failed to upload image to $imagePath.";
                    $_SESSION['status-code'] = "error";
                    error_log("Upload failed for adminID $adminID: Unable to move file to $imagePath", 3, 'C:/xampp/htdocs/PUP/adminportal/errors.log');
                }
            } catch (PDOException $e) {
                $_SESSION['alert'] = "Error!";
                $_SESSION['status'] = "Database error: " . $e->getMessage();
                $_SESSION['status-code'] = "error";
                error_log("Database error for adminID $adminID: " . $e->getMessage(), 3, 'C:/xampp/htdocs/PUP/adminportal/errors.log');
            }
        }
    } else {
        $_SESSION['alert'] = "Error!";
        $_SESSION['status'] = "No file uploaded or upload error: " . ($_FILES['img_admin']['error'] ?? 'Unknown');
        $_SESSION['status-code'] = "error";
    }
}

$adminID = $_SESSION['auth_user']['admin_id'];
try {
    $stmt = $conn->prepare("SELECT * FROM admin_account WHERE id = ?");
    $stmt->execute([$adminID]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) {
        $_SESSION['alert'] = "Error!";
        $_SESSION['status'] = "Admin account not found.";
        $_SESSION['status-code'] = "error";
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['alert'] = "Error!";
    $_SESSION['status'] = "Database error: " . $e->getMessage();
    $_SESSION['status-code'] = "error";
    error_log("Database error fetching adminID $adminID: " . $e->getMessage(), 3, 'C:/xampp/htdocs/PUP/adminportal/errors.log');
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>OJT Web Portal: Admin Profile</title>
    <!-- ================= Favicon ================== -->
    <link rel="shortcut icon" href="images/pupLogo.png">
    
    <!-- Common -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWdWSSV5z2U6gL5i0vO2oT2qB8K0gqG2g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/menubar/sidebar.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/lib/sweetalert/sweetalert.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f0f8ff;
            font-family: Arial, sans-serif;
            color: #000;
            overflow: hidden;
        }
        
        .profile-card {
            max-width: 1500px;
            margin: 0 auto;
            background-color: white;
            overflow: hidden;
        }
        
        .profile-header {
            padding: 15px;
            font-weight: bold;
        }
        
        .profile-content {
            display: flex;
            padding: 20px;
            margin-top: 40px;
        }
        
        .profile-image {
            flex: 0 0 300px;
            text-align: center;
            padding: 20px;
        }
        
        .image-placeholder {
            width: 400px;
            height: 400px;
            margin: 0 auto;
            border-radius: 50%;
            border: 2px solid #e0e0e0;
            background-color: #f8f8f8;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 10px solid #D9D9D9;
        }
        
        .image-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .placeholder-icon {
            width: 100px;
            height: auto;
            opacity: 0.3;
        }
        
        .choose-text {
            margin-top: 10px;
            color: #888;
            font-size: 14px;
        }
        
        .admin-info {
            flex: 1;
            padding: 10px 120px;
        }
        
        .admin-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 50px;
        }
        
        .admin-badge {
            background-color: #ffc107;
            color: #333;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .admin-details {
            margin-top: 20px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .detail-label {
            flex: 0 0 150px;
            font-weight: 500;
        }
        
        .detail-value {
            flex: 1;
            font-weight: bold;
        }
        
        .abnormal {
            color: #dc3545;
            font-weight: bold;
        }
        
        .upload-form {
            margin-top: 15px;
        }
        
        .upload-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        .edit-profile-btn {
            border: none;
            background-color: transparent;
            color: #700000;
            font-weight: bold;
            margin-top: 10px;
            cursor: pointer;
            display: center;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>

<body>
    <!---------NAVIGATION BAR-------->
    <?php
    require_once 'templates/admin_navbar.php';
    ?>
    <!---------NAVIGATION BAR ENDS-------->
    <div class="content-wrap" style="height: 80%; width: 100%; margin: 0 auto;">
        <div style="background-color: white; margin-top: 6rem; margin-left: 16rem; padding: 2rem;">
            <div class="page-header">
                <div class="page-title">
                    <h1 style="font-size: 16px;">MY PROFILE</h1>
                </div>
            </div>
            <div class="profile-card">
                <div class="profile-content">
                    <div class="profile-image">
                        <div class="image-placeholder" onclick="document.getElementById('profile-input').click();">
                            <?php if(!empty($data['admin_profile_picture']) && file_exists('C:/xampp/htdocs' . $data['admin_profile_picture'])): ?>
                                <img src="<?php echo $data['admin_profile_picture']; ?>?<?php echo time(); ?>" alt="Profile Image">
                            <?php else: ?>
                                <img src="images/placeholder.png" alt="Profile Placeholder" class="placeholder-icon">
                            <?php endif; ?>
                        </div>
                        
                        <form action="" method="POST" enctype="multipart/form-data" class="upload-form">
                            <input type="file" name="img_admin" id="profile-input" onchange="uploadImage(event)" required accept="image/*" style="display: none;">
                            <input type="submit" name="upload" id="upload-submit" style="display: none;">
                        </form>

                        <button class="edit-profile-btn" onclick="toSettings()">
                            <i class="fa-solid fa-pencil"></i>
                            Edit Profile Information
                        </button>
                    </div>
                    
                    <div class="admin-info">
                        <div class="admin-name">
                            <span>PUP Admin</span>
                            <span class="admin-badge">admin</span>
                        </div>
                        
                        <div class="admin-id">
                            Admin No.: <?php echo isset($data['id_number']) ? $data['id_number'] : 'N/A'; ?>
                        </div>
                        <br>
                        <div class="admin-details">
                            <div class="detail-row">
                                <div class="detail-label">Name</div>
                                <div class="detail-value">:
                                    <?php echo isset($data['first_name']) ? $data['first_name'] : ''; ?> 
                                    <?php echo isset($data['middle_name']) ? $data['middle_name'] : ''; ?> 
                                    <?php echo isset($data['last_name']) ? $data['last_name'] : ''; ?>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Department</div>
                                <div class="detail-value">: <?php echo isset($data['position']) ? $data['position'] : 'N/A'; ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">: <?php echo isset($data['admin_email']) ? $data['admin_email'] : 'N/A'; ?></div>
                            </div>
                            
                            <!-- Removed age as it's not in schema -->
                            
                            <br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for image preview -->
    <script>
        function previewImage(event) {
            try {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const output = document.querySelector('.image-placeholder img');
                        output.src = e.target.result;
                        output.classList.remove('placeholder-icon');
                    };
                    reader.onerror = function() {
                        console.error('Error reading file');
                    };
                    reader.readAsDataURL(file);
                } else {
                    console.error('No file selected');
                }
            } catch (error) {
                console.error('Preview error:', error);
            }
        }

        function uploadImage(event) {
            try {
                previewImage(event);
                setTimeout(() => {
                    const submitButton = document.getElementById('upload-submit');
                    if (submitButton) {
                        submitButton.click();
                    } else {
                        console.error('Submit button not found');
                    }
                }, 100);
            } catch (error) {
                console.error('Upload error:', error);
            }
        }

        function toSettings() {
            window.location.href = "admin_settings.php";
        }
    </script>

    <!-- Common Scripts -->
    <script src="js/lib/jquery.min.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/menubar/sidebar.js"></script>
    <script src="js/lib/preloader/pace.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/scripts.js"></script>
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