<?php
include '../connection/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set error logging
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error_log');

session_start();

header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check authentication
if (!isset($_SESSION['auth_user']['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Get and validate input
$admin_id = $_SESSION['auth_user']['admin_id'];
$coordinator_id = isset($_POST['coordinator_id']) ? trim($_POST['coordinator_id']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

// Validate input
if (empty($coordinator_id) || empty($content)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit();
}

// Sanitize input
$content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

// Start transaction
$conn->beginTransaction();

// Initialize variables to avoid undefined variable warnings
$message_id = null;
$message_data = null;

try {
    // Find or create conversation
    $stmt = $conn->prepare("SELECT id FROM conversations_admin_coordinator WHERE coordinator_id = ? AND admin_id = ?");
    $stmt->execute([$coordinator_id, $admin_id]);
    $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$conversation) {
        // Create a new conversation if it doesn't exist
        $stmt = $conn->prepare("INSERT INTO conversations_admin_coordinator (coordinator_id, admin_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$coordinator_id, $admin_id]);
        $conversation_id = $conn->lastInsertId();
    } else {
        $conversation_id = $conversation['id'];
    }
    
    // First, get the admin's email from the admin_account table using admin_id
    $stmt = $conn->prepare("SELECT admin_email FROM admin_account WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin || empty($admin['admin_email'])) {
        throw new Exception("Admin email not found in database for admin ID: " . $admin_id);
    }
    
    $admin_email = $admin['admin_email'];
    
    // Get the admin's user_id from the users table using their email
    $stmt = $conn->prepare("SELECT user_id FROM users_admin_coordinator WHERE email = ? AND user_type = 'admin'");
    $stmt->execute([$admin_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("Admin not found in users table with email: " . $admin_email);
    }
    
    $user_id = $user['user_id'];
    
    // Insert message using the user_id from the users table
    $stmt = $conn->prepare("INSERT INTO messages_admin_coordinator (conversation_id, sender_id, sender_type, content, created_at) VALUES (?, ?, 'admin', ?, NOW())");
    $stmt->execute([$conversation_id, $user_id, $content]);
    $message_id = $conn->lastInsertId();
    
    // Get the created message with proper datetime formatting
    $stmt = $conn->prepare(
        "SELECT m.*, 
        DATE_FORMAT(m.created_at, '%Y-%m-%d %H:%i:%s') as formatted_created_at,
        u.first_name, u.last_name
        FROM messages_admin_coordinator m
        LEFT JOIN users_admin_coordinator u ON m.sender_id = u.user_id
        WHERE m.id = ?"
    );
    $stmt->execute([$message_id]);
    $message_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Try to update conversation's updated_at if the column exists
    try {
        $stmt = $conn->prepare("UPDATE conversations_admin_coordinator SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$conversation_id]);
    } catch (PDOException $e) {
        // Silently ignore if updated_at column doesn't exist
        if (strpos($e->getMessage(), 'updated_at') === false) {
            throw $e; // Re-throw if it's a different error
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Ensure we have the required data for the response
    if (empty($message_data)) {
        throw new Exception('Failed to retrieve sent message data');
    }
    
    // Prepare response data
    $response_data = [
        'id' => $message_id,
        'conversation_id' => $conversation_id,
        'sender_id' => $admin_id,
        'sender_type' => 'admin',
        'content' => $content,
        'created_at' => $message_data['formatted_created_at'],
        'sender_name' => trim($message_data['first_name'] . ' ' . $message_data['last_name'])
    ];
    
    // Send real-time notification
    if (defined('PUSHER_APP_KEY') && !empty(PUSHER_APP_KEY)) {
        try {
            require_once __DIR__ . '/../vendor/autoload.php';
            
            $pusher = new Pusher\Pusher(
                PUSHER_APP_KEY,
                PUSHER_APP_SECRET,
                PUSHER_APP_ID,
                [
                    'cluster' => PUSHER_APP_CLUSTER,
                    'useTLS' => true,
                    'encrypted' => true
                ]
            );
            
            // Get the coordinator's name for the notification
            $stmt = $conn->prepare("SELECT first_name, last_name FROM coordinators_account WHERE id = ?");
            $stmt->execute([$coordinator_id]);
            $coordinators = $stmt->fetch(PDO::FETCH_ASSOC);
            $coordinators_name = $coordinators ? trim($coordinators['first_name'] . ' ' . $coordinators['last_name']) : 'Coordinators';
            
            // Trigger event to the student's private channel
            $pusher->trigger(
                'private-coordinators-' . $coordinator_id,
                'new_message',
                [
                    'id' => $message_id,
                    'conversation_id' => $conversation_id,
                    'sender_id' => $admin_id,
                    'sender_type' => 'admin',
                    'content' => $content,
                    'created_at' => $message_data['formatted_created_at'],
                    'sender_name' => trim($message_data['first_name'] . ' ' . $message_data['last_name']),
                    'coordinators_id' => $coordinators_id,
                    'coordinators_name' => $coordinators_name
                ]
            );
            
            // Also trigger to supervisor's channel for sync across devices
            $pusher->trigger(
                'private-admin-' . $admin_id,
                'new_message',
                [
                    'id' => $message_id,
                    'conversation_id' => $conversation_id,
                    'sender_id' => $admin_id,
                    'sender_type' => 'admin',
                    'content' => $content,
                    'created_at' => $message_data['formatted_created_at'],
                    'sender_name' => 'You',
                    'coordinators_id' => $coordinators_id,
                    'coordinators_name' => $coordinators_name
                ]
            );
            
        } catch (Exception $e) {
            // Log the error but don't fail the request
            error_log('Pusher Error: ' . $e->getMessage());
            
            // Log the full exception for debugging
            error_log('Pusher Exception: ' . $e);
            
            // Also log the Pusher configuration (without sensitive data)
            error_log('Pusher Config - Key: ' . (defined('PUSHER_APP_KEY') ? 'Set' : 'Not Set') . 
                     ', Cluster: ' . (defined('PUSHER_APP_CLUSTER') ? PUSHER_APP_CLUSTER : 'Not Set'));
        }
    } else {
        error_log('Pusher not configured. PUSHER_APP_KEY: ' . (defined('PUSHER_APP_KEY') ? 'Set' : 'Not Set'));
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Message sent successfully',
        'data' => $response_data
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database error',
        'debug' => $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'An error occurred',
        'debug' => $e->getMessage()
    ]);
}
?>
