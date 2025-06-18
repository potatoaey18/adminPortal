<?php
include '../connection/config.php';
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
$student_id = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

// Validate input
if (empty($student_id) || empty($content)) {
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
    // First, check if we need to modify the foreign key constraint
    $stmt = $conn->query("
        SELECT 
            TABLE_NAME, 
            COLUMN_NAME, 
            CONSTRAINT_NAME, 
            REFERENCED_TABLE_NAME, 
            REFERENCED_COLUMN_NAME 
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            TABLE_SCHEMA = 'ojtportal' 
            AND TABLE_NAME = 'messages_admin_student' 
            AND CONSTRAINT_NAME = 'messages_admin_student_ibfk_2'
    ");
    $constraint = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($constraint && $constraint['REFERENCED_TABLE_NAME'] === 'users') {
        // Drop the existing foreign key constraint
        $conn->exec("ALTER TABLE messages_admin_student DROP FOREIGN KEY messages_admin_student_ibfk_2");
        
        // Create a new foreign key constraint that references users_admin_student
        $conn->exec("
            ALTER TABLE messages_admin_student 
            ADD CONSTRAINT fk_messages_admin_student_sender 
            FOREIGN KEY (sender_id) 
            REFERENCES users_admin_student(user_id) 
            ON DELETE CASCADE
        ");
    }
    
    // Find or create conversation
    $stmt = $conn->prepare("SELECT id FROM conversations_admin_student WHERE student_id = ? AND admin_id = ?");
    $stmt->execute([$student_id, $admin_id]);
    $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$conversation) {
        $stmt = $conn->prepare("INSERT INTO conversations_admin_student (student_id, admin_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$student_id, $admin_id]);
        $conversation_id = $conn->lastInsertId();
    } else {
        $conversation_id = $conversation['id'];
    }
    
    // First, get the supervisor's email from the supervisor table using supervisor_id
    $stmt = $conn->prepare("SELECT admin_email FROM admin_account WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin || empty($admin['admin_email'])) {
        throw new Exception("Admin email not found in database");
    }
    
    $admin_email = $admin['admin_email'];
    
    // Get the admin's user_id from the users_admin_student table using their email
    $stmt = $conn->prepare("SELECT user_id FROM users_admin_student WHERE email = ? AND user_type = 'admin'");
    $stmt->execute([$admin_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // If admin not found, try to create the admin user in users_admin_student
        $stmt = $conn->prepare("INSERT INTO users_admin_student (user_type, user_id, email, first_name, last_name, is_online, created_at) 
                              SELECT 'admin', id, admin_email, first_name, last_name, 1, NOW() 
                              FROM admin_account 
                              WHERE admin_email = ?");
        $stmt->execute([$admin_email]);
        
        // Get the newly created user
        $stmt = $conn->prepare("SELECT user_id FROM users_admin_student WHERE email = ? AND user_type = 'admin'");
        $stmt->execute([$admin_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception("Failed to create admin user in users_admin_student with email: " . $admin_email);
        }
    }
    
    $user_id = $user['user_id'];
    
    // Insert message using the user_id from the users table
    $stmt = $conn->prepare("INSERT INTO messages_admin_student (conversation_id, sender_id, sender_type, content, created_at) VALUES (?, ?, 'admin', ?, NOW())");
    $stmt->execute([$conversation_id, $user_id, $content]);
    $message_id = $conn->lastInsertId();
    
    // Get the created message with proper datetime formatting
    $stmt = $conn->prepare(
        "SELECT m.*, 
        DATE_FORMAT(m.created_at, '%Y-%m-%d %H:%i:%s') as formatted_created_at,
        u.first_name, u.last_name
        FROM messages_admin_student m
        LEFT JOIN users_admin_student u ON m.sender_id = u.user_id
        WHERE m.id = ?"
    );
    $stmt->execute([$message_id]);
    $message_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Try to update conversation's updated_at if the column exists
    try {
        $stmt = $conn->prepare("UPDATE conversations_admin_student SET updated_at = NOW() WHERE id = ?");
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
            
            // Get student name for the notification
            $stmt = $conn->prepare("SELECT first_name, last_name FROM students_data WHERE id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            $student_name = $student ? trim($student['first_name'] . ' ' . $student['last_name']) : 'Student';
            
            // Trigger event to the student's private channel
            $pusher->trigger(
                'private-student-' . $student_id,
                'new_message',
                [
                    'id' => $message_id,
                    'conversation_id' => $conversation_id,
                    'sender_id' => $admin_id,
                    'sender_type' => 'admin',
                    'content' => $content,
                    'created_at' => $message_data['formatted_created_at'],
                    'sender_name' => trim($message_data['first_name'] . ' ' . $message_data['last_name']),
                    'student_id' => $student_id,
                    'student_name' => $student_name
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
                    'student_id' => $student_id,
                    'student_name' => $student_name
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
