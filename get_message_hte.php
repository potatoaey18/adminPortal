<?php
// Start session and set error reporting
session_start();

// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Log the start of the request
error_log("=== New Request ===");
error_log("GET: " . print_r($_GET, true));
error_log("SESSION: " . print_r($_SESSION, true));

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN');
header('Access-Control-Allow-Credentials: true');

// Function to format time as 'time ago'
function timeAgo($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = floor($diff->d / 7);
    $days = $diff->d % 7;

    $string = [
        'y' => ['value' => $diff->y, 'label' => 'year'],
        'm' => ['value' => $diff->m, 'label' => 'month'],
        'w' => ['value' => $weeks, 'label' => 'week'],
        'd' => ['value' => $days, 'label' => 'day'],
        'h' => ['value' => $diff->h, 'label' => 'hour'],
        'i' => ['value' => $diff->i, 'label' => 'minute'],
        's' => ['value' => $diff->s, 'label' => 'second']
    ];
    
    $result = [];
    foreach ($string as $item) {
        if ($item['value'] > 0) {
            $result[] = $item['value'] . ' ' . $item['label'] . ($item['value'] > 1 ? 's' : '');
            if (!$full) break; // If not full format, only show the first non-zero value
        }
    }

    return !empty($result) ? implode(', ', $result) . ' ago' : 'just now';
}

// Function to send JSON response and exit
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

// Handle errors and exceptions
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno] $errstr in $errfile on line $errline");
    sendJsonResponse([
        'status' => 'error',
        'message' => 'A server error occurred',
        'error' => [
            'code' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]
    ], 500);
});

set_exception_handler(function($e) {
    error_log("Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    sendJsonResponse([
        'status' => 'error',
        'message' => 'An unexpected error occurred',
        'error' => [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ], 500);
});

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database configuration
require_once __DIR__ . '/../connection/config.php';

// Check database connection
if (!isset($conn)) {
    error_log("Database connection failed: No connection");
    sendJsonResponse([
        'status' => 'error',
        'message' => 'Database connection failed',
        'error' => 'No database connection'
    ], 500);
}

try {
    // Test the connection
    $conn->query('SELECT 1');
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    sendJsonResponse([
        'status' => 'error',
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ], 500);
}

error_log("Database connection successful");

// Check if user is logged in as admin
if (!isset($_SESSION['auth_user']['admin_id'])) {
    error_log('Unauthorized in get_messages.php: Session=' . print_r($_SESSION, true));
    sendJsonResponse([
        'status' => 'error',
        'message' => 'Unauthorized: Admin not authenticated',
        'session' => $_SESSION['auth_user'] ?? 'not_set',
        'debug' => [
            'session_id' => session_id(),
            'session_data' => $_SESSION,
            'request_headers' => getallheaders()
        ]
    ], 401);
}

$adminId = $_SESSION['auth_user']['admin_id'];
$userType = 'admin';

// Verify CSRF token for non-GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    if (empty($csrf_token) || $csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
        sendJsonResponse([
            'status' => 'error', 
            'message' => 'Invalid CSRF token',
            'debug' => [
                'session_token' => $_SESSION['csrf_token'] ?? 'not_set',
                'received_token' => $csrf_token,
                'session_id' => session_id()
            ]
        ], 403);
    }
}

try {
    // Get supervisor ID from query parameters
    $supervisorId = isset($_GET['supervisor_id']) ? intval($_GET['supervisor_id']) : 0;
    
    if (!$supervisorId) {
        throw new Exception('Supervisor ID is required');
    }
    
    $adminId = $_SESSION['auth_user']['admin_id'];
    
    // Log the IDs for debugging
    error_log("Admin ID: " . $adminId . ", Supervisor ID: " . $supervisorId);
    
    // Verify the supervisor exists and is assigned to this admin
    $query = "SELECT id FROM supervisor WHERE id = :supervisor_id";
    error_log("Executing query: $query with supervisor_id: $supervisorId");
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':supervisor_id', $supervisorId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Supervisor with ID $supervisorId not found");
        }
        
        error_log("Supervisor $supervisorId found");
        
        // Start transaction
        $conn->beginTransaction();
    } catch (PDOException $e) {
        throw new Exception("Database error: " . $e->getMessage());
    }
    
    try {
        // Check if conversation exists, if not create one
        $stmt = $conn->prepare("SELECT id FROM conversations_admin_hte WHERE supervisor_id = :supervisor_id AND admin_id = :admin_id");
        $stmt->bindParam(':supervisor_id', $supervisorId, PDO::PARAM_INT);
        $stmt->bindParam(':admin_id', $adminId, PDO::PARAM_INT);
        $stmt->execute();
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $conversationId = null;
        
        if (!$conversation) {
            // Create a new conversation
            $stmt = $conn->prepare("INSERT INTO conversations_admin_hte (supervisor_id, admin_id) VALUES (:supervisor_id, :admin_id)");
            $stmt->bindParam(':supervisor_id', $supervisorId, PDO::PARAM_INT);
            $stmt->bindParam(':admin_id', $adminId, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create conversation');
            }
            
            $conversationId = $conn->lastInsertId();
        } else {
            $conversationId = $conversation['id'];
        }
        
        // Get messages for this conversation with sender names
        $query = "SELECT 
                    m.id, 
                    m.conversation_id, 
                    m.sender_id, 
                    m.sender_type, 
                    m.content, 
                    m.is_read, 
                    m.created_at as message_created_at,
                    CASE 
                        WHEN m.sender_type = 'supervisor' AND su.id IS NOT NULL THEN CONCAT(su.first_name, ' ', su.last_name)
                        WHEN m.sender_type = 'admin' AND ad.id IS NOT NULL THEN CONCAT(ad.first_name, ' ', ad.last_name)
                        ELSE 'Unknown Sender'
                    END as sender_name,
                    su.supervisor_email as supervisor_email,
                    ad.admin_email as admin_email,
                    m.sender_type as message_sender_type
                FROM messages_admin_hte m
                LEFT JOIN supervisor su ON su.id = m.sender_id AND m.sender_type = 'supervisor'
                LEFT JOIN admin_account ad ON ad.id = m.sender_id AND m.sender_type = 'admin'
                WHERE m.conversation_id = :conversation_id
                ORDER BY m.created_at ASC";
                
        error_log("Executing query: " . $query . " with conversation_id: " . $conversationId);
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
        $stmt->execute();
        
        $messages = [];
        $unreadMessageIds = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $message = [
                'id' => (int)$row['id'],
                'conversation_id' => (int)$row['conversation_id'],
                'sender_id' => (int)$row['sender_id'],
                'sender_type' => $row['sender_type'],
                'content' => $row['content'],
                'is_read' => (bool)$row['is_read'],
                'created_at' => $row['message_created_at'],
                'sender_name' => $row['sender_name'],
                'email' => $row['sender_type'] === 'supervisor' ? ($row['supervisors_email'] ?? null) : ($row['admin_email'] ?? null),
                'time_ago' => timeAgo($row['message_created_at'])
            ];
            
            // No updated_at column in the messages table
            
            $messages[] = $message;
            
            // Track unread messages from supervisor
            if ($row['message_sender_type'] === 'supervisor' && !$row['is_read']) {
                $unreadMessageIds[] = (int)$row['id'];
            }
        }
        
        // Mark messages as read if they're from the admin
        if (!empty($unreadMessageIds)) {
            $placeholders = rtrim(str_repeat('?,', count($unreadMessageIds)), ',');
            $updateQuery = "UPDATE messages_admin_hte SET is_read = 1 WHERE id IN ($placeholders)";
            $updateStmt = $conn->prepare($updateQuery);
            
            // Bind parameters
            foreach ($unreadMessageIds as $k => $id) {
                $updateStmt->bindValue(($k+1), $id, PDO::PARAM_INT);
            }
            
            $updateStmt->execute();
        }
        
        // If we get here, everything was successful
        $conn->commit();
        
        // Return the messages in the format expected by the frontend
        $formattedMessages = [];
        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'id' => $msg['id'],
                'conversation_id' => $msg['conversation_id'],
                'sender_id' => $msg['sender_id'],
                'sender_type' => $msg['sender_type'],
                'content' => $msg['content'],
                'is_read' => $msg['is_read'],
                'created_at' => $msg['created_at'],
                'first_name' => explode(' ', $msg['sender_name'])[0] ?? '',
                'last_name' => explode(' ', $msg['sender_name'])[1] ?? '',
                'sender_name' => $msg['sender_name']
            ];
        }
        
        sendJsonResponse([
            'status' => 'success',
            'messages' => $formattedMessages,
            'conversation_id' => $conversationId,
            'supervisor_id' => $supervisorId,
            'admin_id' => $adminId,
            'total_messages' => count($formattedMessages),
            'unread_count' => count($unreadMessageIds)
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e; // This will be caught by the outer exception handler
    }
    
} catch (Exception $e) {
    // This will be caught by the exception handler at the top of the file
    throw $e;
}
?>
