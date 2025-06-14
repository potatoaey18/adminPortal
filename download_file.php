<?php
include '../connection/config.php';
session_start();
if (!isset($_SESSION['auth_user']['admin_id']) || $_SESSION['auth_user']['admin_id'] == 0) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT moa_document_path FROM new_moa_processing WHERE id = :id");
        $stmt->execute(['id' => $_GET['id']]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($file && !empty($file['moa_document_path'])) {
            // Handle absolute or relative paths
            $filePath = $file['moa_document_path'];
            
            // If path is absolute (starts with /), try to resolve it
            if (strpos($filePath, '/') === 0) {
                // Option 1: Assume absolute path is relative to server root
                $serverRoot = $_SERVER['DOCUMENT_ROOT'];
                $filePath = $serverRoot . $filePath;
                error_log("Trying absolute path: $filePath");
            } else {
                // Option 2: Assume relative path from project root
                $basePath = dirname(__DIR__); // Project root (one level up)
                $filePath = $basePath . '/' . ltrim($filePath, '/');
                error_log("Trying relative path: $filePath");
            }

            // Debug: Log file path and existence check
            error_log("Checking file: $filePath");
            if (file_exists($filePath)) {
                $fileName = basename($filePath);
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit;
            } else {
                error_log("File not found: $filePath");
                http_response_code(404);
                echo 'File not found at: ' . htmlspecialchars($filePath);
            }
        } else {
            error_log("No file path in database for id: " . $_GET['id']);
            http_response_code(404);
            echo 'No file associated with this application.';
        }
    } catch (PDOException $e) {
        error_log("File download error: " . $e->getMessage());
        http_response_code(500);
        echo 'Error retrieving file from database.';
    }
} else {
    http_response_code(400);
    echo 'Invalid request.';
}
?>