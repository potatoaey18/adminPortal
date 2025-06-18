<?php
include '../connection/config.php';
require_once '../config/pusher.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set error logging
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error_log');


session_start();
if (!isset($_SESSION['auth_user']['admin_id'])) {
    echo "<script>window.location.href='index.php'</script>";
    exit();
}

// Make sure Pusher constants are defined
if (!defined('PUSHER_APP_KEY') || !defined('PUSHER_APP_CLUSTER')) {
    $configPath = __DIR__ . '/../config/pusher.php';
    if (file_exists($configPath)) {
        include_once $configPath;
    } else {
        if (!defined('PUSHER_APP_KEY')) define('PUSHER_APP_KEY', '893140161a0139aad0a7');
        if (!defined('PUSHER_APP_CLUSTER')) define('PUSHER_APP_CLUSTER', 'ap1');
        if (!defined('PUSHER_APP_ID')) define('PUSHER_APP_ID', '2008054');
        if (!defined('PUSHER_APP_SECRET')) define('PUSHER_APP_SECRET', '778e3ac864d99c9dbf98');
    }
}

// Make sure the Pusher library is included
$pusherAutoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($pusherAutoloadPath)) {
    require_once $pusherAutoloadPath;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OJT Web Portal: Messages</title>
    <link rel="shortcut icon" href="images/pupLogo.png">
    
    <!-- Pusher Configuration -->
    <script>
    const PUSHER_APP_KEY = '<?php echo defined('PUSHER_APP_KEY') ? PUSHER_APP_KEY : ''; ?>';
    const PUSHER_APP_CLUSTER = '<?php echo defined('PUSHER_APP_CLUSTER') ? PUSHER_APP_CLUSTER : ''; ?>';
    const PUSHER_APP_ID = '<?php echo defined('PUSHER_APP_ID') ? PUSHER_APP_ID : ''; ?>';
    </script>
    
    <!-- Common -->
    <link href="css/lib/font-awesome.min.css" rel="stylesheet">
    <link href="css/lib/themify-icons.css" rel="stylesheet">
    <link href="css/lib/menubar/sidebar.css" rel="stylesheet">
    <link href="css/lib/bootstrap.min.css" rel="stylesheet">
    <link href="css/lib/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/lib/sweetalert/sweetalert.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        :root {
            --primary-color: #8B0000;
            --secondary-color: #f8f9fa;
            --border-color: #e9ecef;
        }

        body {
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .content-wrap {
            width: 100%;
            margin: 0 auto;
            position: relative;
            padding-left: 19.5rem;
            padding-top: 7rem;
        }

        .profile-container {
            background-color: #fff;
            position: fixed;
            top: 7rem;
            left: 19.5rem;
            right: 0;
            bottom: 0;
            padding: 1rem;
            overflow-y: hidden !important;
            overflow-x: hidden !important;
        }

        .messaging-container {
            display: flex;
            height: calc(100vh - 7rem);
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            position: relative;
            margin-top: -17px;
            width: 101%
        }

        .contacts-panel {
            width: 437.5px;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            background-color: #fff;
            height: 100%;
            flex-shrink: 0;
        }

        .contacts-header {
            padding: 45px 35px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .circle-avatar {
            width: 160px;
            height: 160px;
            background-color: #f0f0f0;
            border-radius: 50%;
            margin: 20px auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid rgba(217, 217, 217, 0.3);
        }

        .circle-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .supervisor-name {
            text-align: center;
            font-size: 24px;
            font-weight: 500;
            margin: 10px 0 20px;
            color: #333;
        }

        .search-bar {
            position: relative;
            margin: 15px 0;
            padding-top: 20px;
        }
        
        .search-icon {
            position: absolute;
            right: 15px;
            color: #777;
            font-size: 16px;
            pointer-events: none;
            transition: color 0.3s ease;
            padding-top: 16px;
            padding-right: 45px;
        }
        
        .search-bar input[type="text"] {
            width: 80%;
            padding: 8px 15px 8px 15px;
            text-indent: 5px;
            border: 1px solid #444444;
            border-radius: 5px;
            background-color: rgba(217, 217, 217, 0.3);
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
            height: 50px;
            margin-left: 30px;
            background-color: rgba(217, 217, 217, 0.3);
        }

        .contacts-list {
            flex: 1;
            overflow-y: auto;
            padding-top: 30px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .contact-status {
            font-size: 11px;
            font-weight: 500;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 6px;
            text-transform: capitalize;
        }
        
        .contact-status.online {
            background-color: #e6f7ee;
            color: #28a745;
        }
        
        .contact-status.offline {
            background-color: #f1f1f1;
            color: #6c757d;
        }

        .contact-item:hover, .contact-item.active {
            background-color: #f8f9fa;
        }

        .contact-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-weight: bold;
        }

        .contact-info {
            flex: 1;
        }

        .contact-name {
            font-weight: 600;
            margin-bottom: 2px;
            display: flex;
            justify-content: space-between;
        }

        .contact-preview {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }

        .preview-text {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #6c757d;
            font-size: 0.85em;
        }

        .chat-container {
            display: flex;
            height: calc(100vh - 60px);
            background-color: #f8f9fa;
            position: relative;
        }

        .chat-panel {
            display: flex;
            flex-direction: column;
            position: relative;
            height: 100%;
            background-color: #d9d9d9;
            width: calc(100% - 280px);
            flex-shrink: 1;
            transition: all 0.3s ease;
            transform: translateX(0);
        }
        
        .chat-panel.has-conversation {
            width: 60%;
            max-width: 800px;
            flex-shrink: 0;
        }

        .chat-area {
            width: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            background-color: #f0f2f5;
            border-right: 1px solid #dee2e6;
            flex-shrink: 0;
        }
        
        #chatContent {
            display: none;
            flex-direction: column;
            height: 100%;
            position: relative;
            overflow: hidden;
            padding-bottom: 0;
        }

        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #444444;
            display: flex;
            align-items: center;
        }

        .chat-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-weight: bold;
        }

        .chat-info h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .chat-info p {
            margin: 2px 0 0;
            font-size: 13px;
            color: #6c757d;
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background-color: #d9d9d9;
            display: flex;
            flex-direction: column;
            position: relative;
            max-height: calc(100% - 80px);
        }

        .message {
            margin-bottom: 15px;
            max-width: 70%;
            position: relative;
            display: flex;
        }

        .message.received {
            margin-right: auto;
            justify-content: flex-start;
            text-align: left;
        }

        .message.sent {
            margin-left: auto;
            justify-content: flex-end;
            text-align: right;
        }

        .message-content {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 18px;
            background-color: #444444;
            color: #ffffff;
            font-size: 14px;
            line-height: 1.4;
            max-width: 100%;
            word-wrap: break-word;
            text-align: left;
        }
        
        .message-content p {
            color: #ffffff;
            margin: 0;
        }

        .message.sent .message-content {
            background-color: #444444;
            color: white;
            border-radius: 18px 18px 0 18px;
            margin-left: auto;
        }

        .message-input-container {
            padding: 10px 15px;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message-input-wrapper {
            display: flex;
            align-items: center;
            background: rgba(68, 68, 68, 0.44);
            border-radius: 25px;
            padding: 0 10px 0 15px;
            border: 1px solid #e0e0e0;
            flex: 1;
            height: 50px;
            box-sizing: border-box;
            position: relative;
        }

        .input-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 5px;
            flex-shrink: 0;
        }

        .action-button {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
            font-size: 16px;
            padding: 0;
            flex-shrink: 0;
        }

        .action-button:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: #8B0000;
        }

        #messageInput {
            border: none;
            background: transparent;
            padding: 0 10px;
            width: 100%;
            outline: none;
            color: #333;
            flex: 1;
            height: 100%;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
        }

        #messageInput::placeholder {
            color: rgba(255, 255, 255, 1);
        }

        .send-button-container {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background-color: rgba(0, 0, 0, 0.3);
            height: 100%;
            padding: 0 12px 0 16px;
            margin: 0;
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;      
        }

        .send-button {
            border: none;
            color: #444444;
            background-color: rgba(0, 0, 0, 0.0);
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .shared-files-panel {
            flex: 1;
            min-width: 300px;
            max-width: 40%;
            background-color: #fff;
            border-left: 1px solid var(--border-color);
            display: none;
            flex-direction: column;
            height: 100%;
            overflow-y: auto;
            transition: transform 0.3s ease, opacity 0.3s ease;
            transform: translateX(100%);
            opacity: 0;
        }
        
        .shared-files-panel.active {
            transform: translateX(0);
            opacity: 1;
        }

        .chat-panel.active + .shared-files-panel {
            display: flex;
        }

        .files-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .files-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .file-actions .btn {
            padding: 4px 8px;
            font-size: 12px;
        }

        .files-summary {
            display: flex;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            gap: 15px;
        }

        .summary-item {
            flex: 1;
            text-align: center;
            padding: 12px 0;
            background-color: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .summary-item:hover {
            background-color: #e9ecef;
        }

        .summary-item i {
            display: block;
            font-size: 20px;
            margin-bottom: 5px;
            color: #6c757d;
        }

        .summary-item .count {
            display: block;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .summary-item .label {
            font-size: 11px;
            color: #6c757d;
        }

        .file-categories {
            flex: 1;
            overflow-y: auto;
            padding: 15px 0;
        }

        .category {
            margin-bottom: 15px;
        }

        .category-header {
            display: flex;
            align-items: center;
            padding: 8px 20px;
            cursor: pointer;
            font-weight: 500;
            color: #333;
        }

        .category-header .badge {
            margin-left: auto;
            background-color: #e9ecef;
            color: #6c757d;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .category-items {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .category.active .category-items {
            max-height: 500px;
        }

        .file-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
        }

        .file-icon {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            background-color: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 14px;
            color: #6c757d;
        }

        .file-info {
            flex: 1;
            min-width: 0;
        }

        .file-name {
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2px;
        }

        .file-meta {
            font-size: 11px;
            color: #adb5bd;
        }

        .message-time {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 2px;
            text-align: right;
            display: block;
            line-height: 1;
        }

        @media (max-width: 1200px) {
            .files-panel {
                display: none;
            }
        }

        @media (max-width: 992px) {
            .contacts-panel {
                width: 100%;
            }
            .chat-panel {
                display: none;
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: white;
                z-index: 10;
            }
            .chat-panel.active {
                display: flex;
            }
            .back-to-contacts {
                display: block !important;
                margin-right: 10px;
                font-size: 20px;
                cursor: pointer;
            }
        }

        .back-to-contacts {
            display: none;
            margin-right: 15px;
            color: #6c757d;
            cursor: pointer;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #dee2e2e6;
        }

        .empty-state h4 {
            margin-bottom: 10px;
            color: #343a40;
        }

        .empty-state p {
            max-width: 300px;
            margin: 0 auto 20px;
        }
        
        .empty-state i.ti-comment-alt {
            color: #444444;
            font-size: 48px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php require_once 'templates/admin_navbar.php'; ?>

    <div class="content-wrap">
        <div class="profile-container">
            <div class="messaging-container">
                <!-- Contacts Panel -->
                <div class="contacts-panel">
                <?php
                    $admin_id = $_SESSION['auth_user']['admin_id'] ?? 0;
                    $admin_name = 'Student';
                    $profile_picture = 'default-avatar.png';
                    
                    if ($admin_id) {
                        $stmt = $conn->prepare("SELECT first_name, middle_name, last_name, admin_profile_picture FROM admin_account WHERE id = :id");
                        $stmt->execute([':id' => $admin_id]);
                        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($admin) {
                            $admin_name = htmlspecialchars($admin['first_name'] . ' ' . ($admin['middle_name'] ? $admin['middle_name'] . ' ' : '') . $admin['last_name']);
                            if (!empty($admin['admin_profile_picture'])) {
                                $profile_picture = $admin['admin_profile_picture'];
                            }
                        }
                    }
                    ?>
                    <div class="contacts-header">
                        <h5>Messages</h5>
                        <div class="circle-avatar">
                            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" onerror="this.onerror=null; this.src='default-avatar.png';">
                        </div>
                        <div class="supervisor-name">
                            <?php echo $admin_name; ?>
                        </div>
                        <div class="search-bar">
                            <input type="text" id="searchContacts" placeholder="Search">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                    </div>
                    <div class="contacts-list">
                        <?php
                        $admin_id = $_SESSION['auth_user']['admin_id'] ?? 0;
                        try {
                            $stmt = $conn->prepare("SELECT * FROM supervisor ORDER BY first_name, last_name");
                            $stmt->execute();
                            $supervisors = $stmt->fetchAll();
                            
                            foreach ($supervisors as &$supervisor) {
                                $stmt = $conn->prepare("
                                    SELECT c.id 
                                    FROM conversations_admin_hte c
                                    JOIN conversation_participants cp1 ON c.id = cp1.conversation_id
                                    JOIN conversation_participants cp2 ON c.id = cp2.conversation_id
                                    WHERE cp1.user_id = ? AND cp2.user_id = ?
                                    LIMIT 1
                                ");
                                $stmt->execute([$admin_id, $supervisor['id']]);
                                $conversation = $stmt->fetch();
                                
                                if ($conversation) {
                                    $stmt = $conn->prepare("
                                        SELECT m.content, m.created_at 
                                        FROM messages_admin_hte m 
                                        WHERE m.conversation_id = ? 
                                        ORDER BY m.created_at DESC 
                                        LIMIT 1
                                    ");
                                    $stmt->execute([$conversation['id']]);
                                    $lastMessage = $stmt->fetch();
                                    
                                    $supervisor['last_message'] = $lastMessage ? $lastMessage['content'] : '';
                                    $supervisor['last_message_time'] = $lastMessage ? $lastMessage['created_at'] : null;
                                } else {
                                    $supervisor['last_message'] = '';
                                    $supervisor['last_message_time'] = null;
                                }
                            }
                            
                            usort($supervisors, function($a, $b) {
                                if ($a['last_message_time'] === $b['last_message_time']) {
                                    return strcmp($a['first_name'] . $a['last_name'], $b['first_name'] . $b['last_name']);
                                }
                                if ($a['last_message_time'] === null) return 1;
                                if ($b['last_message_time'] === null) return -1;
                                return strtotime($b['last_message_time']) - strtotime($a['last_message_time']);
                            });
                        } catch (PDOException $e) {
                            error_log("Database error: " . $e->getMessage());
                            $supervisors = [];
                        }

                        if (count($supervisors) > 0) {
                            foreach ($supervisors as $supervisor) {
                                $initials = strtoupper(substr($supervisor['first_name'], 0, 1) . substr($supervisor['last_name'], 0, 1));
                                $fullName = htmlspecialchars($supervisor['first_name'] . ' ' . $supervisor['last_name']);
                                $status = $supervisor['online_offlineStatus'] === 'Online' ? 'online' : 'offline';
                                $lastMessage = '';
                                if (!empty($supervisor['last_message'])) {
                                    $lastMessage = htmlspecialchars($supervisor['last_message']);
                                    if (strlen($lastMessage) > 30) {
                                        $lastMessage = substr($lastMessage, 0, 27) . '...';
                                    }
                                }
                                
                                echo "<div class='contact-item' data-admin-id='{$supervisor['id']}'>";
                                echo "<div class='contact-avatar' style='background-color: #8B0000;'>{$initials}</div>";
                                echo "<div class='contact-info'>";
                                echo "<div class='contact-name'>{$fullName} <span class='contact-status {$status}'>".ucfirst($status)."</span></div>";
                                echo "<div class='contact-preview'>";
                                echo "<span class='preview-text' title='".htmlspecialchars($supervisor['last_message'] ?? '')."'>{$lastMessage}</span>";
                                echo "</div></div></div>";
                            }
                        } else {
                            echo '<div class="no-contacts">No supervisors found</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Chat Panel -->
                <div class="chat-panel">
                    <div class="empty-state text-center" id="emptyState">
                        <i class="ti-comment-alt"></i>
                        <h5>Select a conversation to start messaging</h5>
                    </div>
                    
                    <div id="chatContent" style="display: none;">
                        <div class="chat-header">
                            <div class="chat-avatar" id="chatAvatar">JD</div>
                            <div class="chat-info">
                                <h5 id="chatStudentName">John Doe</h5>
                                <p id="chatStudentInfo">BS Computer Engineering - 3A</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; height: calc(100% - 70px);">
                            <div class="messages-container">
                                <div class="messages" id="messagesList"></div>
                            </div>
                            
                            <div class="message-input-container">
                                <div class="input-actions">
                                    <button type="button" class="action-button" id="attachFile" title="Attach file">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <button type="button" class="action-button" id="recordAudio" title="Record voice message">
                                        <i class="fas fa-microphone"></i>
                                    </button>
                                </div>
                                <div class="message-input-wrapper">
                                    <input type="hidden" id="currentStudentId">
                                    <input type="text" id="messageInput" placeholder="Type a message..." disabled>
                                    <button id="sendMessage" class="send-button" disabled>
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Shared Files Panel -->
                <div class="shared-files-panel">
                    <div class="files-header">
                        <h4>Shared Files</h4>
                        <div class="file-actions">
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="files-summary">
                        <div class="summary-item">
                            <i class="fas fa-file-alt"></i>
                            <span class="count">0</span>
                            <span class="label">Documents</span>
                        </div>
                        <div class="summary-item">
                            <i class="fas fa-link"></i>
                            <span class="count">0</span>
                            <span class="label">Links</span>
                        </div>
                    </div>
                    
                    <div class="file-categories">
                        <div class="category">
                            <div class="category-header">
                                <i class="fas fa-file-pdf"></i>
                                <span>Documents</span>
                                <span class="badge">0</span>
                            </div>
                            <div class="category-items"></div>
                        </div>
                        
                        <div class="category">
                            <div class="category-header">
                                <i class="fas fa-image"></i>
                                <span>Photos</span>
                                <span class="badge">0</span>
                            </div>
                            <div class="category-items"></div>
                        </div>
                        
                        <div class="category">
                            <div class="category-header">
                                <i class="fas fa-video"></i>
                                <span>Videos</span>
                                <span class="badge">0</span>
                            </div>
                            <div class="category-items"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Common Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/lib/jquery.nanoscroller.min.js"></script>
    <script src="js/lib/menubar/sidebar.js"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script src="js/lib/sweetalert/sweetalert.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
    $(document).ready(function() {
        let currentAdminId = null; // Global variable for current student ID

        // Helper function to decode HTML entities
        function decodeHtmlEntities(html) {
            const txt = document.createElement('textarea');
            txt.innerHTML = html;
            return txt.value;
        }

        // Function to save messages to localStorage
        function saveMessages(adminId, messages) {
            localStorage.setItem(`messages_${adminId}`, JSON.stringify(messages));
        }

        // Function to load messages from localStorage
        function loadSavedMessages(adminId) {
            const saved = localStorage.getItem(`messages_${adminId}`);
            return saved ? JSON.parse(saved) : [];
        }

        // Function to format time
        function formatTime(dateString) {
            return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        // Function to sort contacts by last message timestamp
        function sortContactsByLastMessage() {
            const $contactsList = $('.contacts-list');
            const $contacts = $contactsList.find('.contact-item').detach();
            const previews = JSON.parse(localStorage.getItem('contactPreviews') || '{}');
            
            $contacts.sort((a, b) => {
                const aId = $(a).data('admin-id');
                const bId = $(b).data('admin-id');
                const aTime = previews[aId]?.timestamp || 0;
                const bTime = previews[bId]?.timestamp || 0;
                return bTime - aTime;
            });
            
            $contactsList.append($contacts);
        }

        // Function to update contact preview
        function updateContactPreview(adminId, message, isNewMessage = true, previewTime = null) {
            console.log('Updating contact preview for admin:', adminId, 'Message:', message, 'isNewMessage:', isNewMessage, 'previewTime:', previewTime);
            
            const contactItem = document.querySelector(`.contact-item[data-admin-id="${adminId}"]`);
            if (!contactItem) {
                console.error('Contact item not found for admin ID:', adminId);
                return null;
            }
            
            const now = new Date();
            const timeString = previewTime || now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const previews = JSON.parse(localStorage.getItem('contactPreviews') || '{}');
            
            // Decode HTML entities for the preview
            const decodedMessage = decodeHtmlEntities(message);
            let preview = decodedMessage;
            if (preview.length > 30) {
                preview = preview.substring(0, 27) + '...';
            }
            
            const previewData = {
                preview: preview,
                fullMessage: message,
                time: timeString,
                timestamp: now.getTime()
            };
            
            previews[adminId] = previewData;
            localStorage.setItem('contactPreviews', JSON.stringify(previews));
            
            const previewElement = contactItem.querySelector('.contact-preview');
            const timeElement = contactItem.querySelector('.contact-time');
            
            if (previewElement) {
                previewElement.textContent = preview;
                previewElement.setAttribute('title', message);
            }
            
            if (timeElement) {
                timeElement.textContent = previewData.time;
                timeElement.style.display = 'none';
                timeElement.offsetHeight;
                timeElement.style.display = '';
            }
            
            if (isNewMessage) {
                setTimeout(() => {
                    sortContactsByLastMessage();
                }, 0);
            }
            
            return previewData;
        }

        // Function to scroll messages to bottom
        function scrollToBottom() {
            const messagesList = document.getElementById('messagesList');
            if (messagesList) {
                messagesList.scrollTop = messagesList.scrollHeight;
            }
        }

        // Function to render messages
        function renderMessages(messages) {
            const messagesList = $('#messagesList');
            messagesList.empty();
            
            if (messages.length === 0) {
                messagesList.html(`
                    <div class="text-center p-4">
                        <p>Start a new conversation with this person</p>
                        <small class="text-muted">Messages will appear here</small>
                    </div>
                `);
                return;
            }
            
            messages.forEach(msg => {
                const isMe = msg.sender === 'me';
                // Decode HTML entities before displaying
                const messageText = decodeHtmlEntities(msg.text);
                const messageHtml = `
                    <div class="message ${isMe ? 'sent' : 'received'}" data-message-id="${msg.id}">
                        <div class="message-content">
                            <div class="message-text">${messageText}</div>
                            <span class="message-time">${msg.time}</span>
                        </div>
                    </div>`;
                messagesList.append(messageHtml);
            });
            
            if (messages.length > 0 && currentStudentId) {
                const lastMessage = messages[messages.length - 1];
                // Decode HTML entities for preview as well
                const decodedMessage = decodeHtmlEntities(lastMessage.text);
                updateContactPreview(currentStudentId, decodedMessage, false);
            }
            
            setTimeout(scrollToBottom, 50);
        }

        // Function to load messages for a supervisor
        function loadSupervisorMessages(adminId) {
            if (!adminId) return;
            
            console.log('Loading messages for admin ID:', adminId);
            currentStudentId = adminId;
            $('#currentStudentId').val(adminId);
            
            $('.contact-item').removeClass('active');
            $(`.contact-item[data-admin-id="${adminId}"]`).addClass('active');
            
            const contactItem = $(`.contact-item[data-admin-id="${adminId}"]`);
            const adminName = contactItem.find('.contact-name').text().trim().split(' ')[0] + ' ' + contactItem.find('.contact-name').text().trim().split(' ')[1];
            $('#chatStudentName').text(adminName || 'Student');
            const initials = adminName.split(' ').map(n => n[0]).join('').toUpperCase();
            $('#chatAvatar').text(initials);
            
            $('#emptyState').hide();
            $('#chatContent').show();
            
            const messagesList = $('#messagesList');
            // Show empty state message immediately
            messagesList.html(`
                <div class="text-center p-4">
                    <p>Start a new conversation with this person</p>
                    <small class="text-muted">Messages will appear here</small>
                </div>
            `);
            
            const savedMessages = loadSavedMessages(adminId);
            if (savedMessages && savedMessages.length > 0) {
                renderMessages(savedMessages);
            } else {
                // Clear the empty state if there are no saved messages
                messagesList.empty();
            }
            
            $.ajax({
                url: 'get_message_hte.php',
                type: 'GET',
                data: { supervisor_id: adminId },
                dataType: 'json',
                success: function(response) {
                    console.log('Messages loaded:', response);
                    if (response.status === 'success' && Array.isArray(response.messages)) {
                        const formattedMessages = response.messages.map(msg => ({
                            id: msg.id,
                            conversation_id: msg.conversation_id,
                            sender: msg.sender_type === 'admin' ? 'me' : 'them',
                            text: msg.content,
                            time: formatTime(msg.created_at),
                            is_read: msg.is_read,
                            created_at: msg.created_at,
                            sender_name: `${msg.first_name || ''} ${msg.last_name || ''}`.trim() || 'Unknown Sender'
                        }));
                        
                        saveMessages(adminId, formattedMessages);
                        if (!savedMessages || savedMessages.length !== formattedMessages.length) {
                            renderMessages(formattedMessages);
                        }
                        
                        markMessagesAsRead(adminId);
                    } else {
                        console.error('Invalid response format or no messages:', response);
                        if (!savedMessages || savedMessages.length === 0) {
                            messagesList.html('<div class="text-center p-4 text-muted">No messages found. Start a new conversation!</div>');
                        } else {
                            renderMessages(savedMessages);
                        }
                    }
                    
                    $('#messageInput').prop('disabled', false).attr('placeholder', 'Type your message...');
                    $('#sendMessage').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading messages:', status, error);
                    if (!savedMessages || savedMessages.length === 0) {
                        messagesList.html('<div class="text-center p-4 text-danger">Failed to load messages. Please check your connection.</div>');
                    } else {
                        showToast('Warning', 'Could not load new messages. Showing cached messages.', 'warning');
                    }
                    $('#messageInput').prop('disabled', false).attr('placeholder', 'Type your message...');
                    $('#sendMessage').prop('disabled', false);
                }
            });
        }

        // Function to mark messages as read
        function markMessagesAsRead(adminId) {
            if (!adminId) return;
            
            $('.message[data-admin-id="' + adminId + '"]').removeClass('unread').addClass('read');
            updateUnreadCount(adminId, false);
            
            const unreadMessages = [];
            $('.message[data-admin-id="' + adminId + '"].unread').each(function() {
                var messageId = $(this).data('message-id');
                if (messageId) {
                    unreadMessages.push(messageId);
                }
            });
            
            if (unreadMessages.length > 0) {
                $.ajax({
                    url: 'mark_as_read.php',
                    type: 'POST',
                    data: {
                        message_ids: unreadMessages,
                        admin_id: adminId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            console.log('Messages marked as read');
                            unreadMessages.forEach(function(id) {
                                $(`.message[data-message-id="${id}"]`).removeClass('unread').addClass('read');
                            });
                            updateUnreadCount(adminId, false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error marking messages as read:', error);
                        showToast('Error', 'Failed to update read status', 'error');
                    }
                });
            }
        }

        // Function to update unread count
        function updateUnreadCount(adminId, increment = false) {
            const contactItem = $(`.contact-item[data-admin-id="${adminId}"]`);
            if (contactItem.length) {
                let unreadBadge = contactItem.find('.unread-badge');
                let unreadCount = parseInt(unreadBadge.text() || '0');
                
                if (increment) {
                    unreadCount++;
                } else {
                    unreadCount = 0;
                }
                
                if (unreadCount > 0) {
                    if (unreadBadge.length) {
                        unreadBadge.text(unreadCount);
                    } else {
                        contactItem.find('.contact-name').append(
                            `<span class="badge bg-danger rounded-pill unread-badge">${unreadCount}</span>`
                        );
                    }
                } else if (unreadBadge.length) {
                    unreadBadge.remove();
                }
            }
        }

        // Function to send message
        function sendMessage() {
            console.log('sendMessage called');
            const messageInput = $('#messageInput');
            const message = messageInput.val().trim();
            console.log('Message to send:', message, ', to admin:', currentStudentId);
            
            if (!message || !currentStudentId) {
                console.log('Message or admin ID is missing');
                return;
            }
            
            messageInput.prop('disabled', true);
            $('#sendMessage').prop('disabled', true);
            
            const tempId = 'temp_' + Date.now();
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const newMessage = {
                id: tempId,
                sender: 'me',
                text: message,
                time: time,
                status: 'sending'
            };
            
            const existingMessages = loadSavedMessages(currentStudentId);
            existingMessages.push(newMessage);
            saveMessages(currentStudentId, existingMessages);
            renderMessages(existingMessages);
            updateContactPreview(currentStudentId, message, true);
            messageInput.val('').focus();
            scrollToBottom();
            
            $.ajax({
                url: 'send_message_hte.php',
                type: 'POST',
                data: {
                    supervisor_id: currentStudentId,
                    content: message
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Server response:', response);
                    messageInput.prop('disabled', false);
                    $('#sendMessage').prop('disabled', false);
                    
                    if (response.status === 'success' && response.data) {
                        const messages = loadSavedMessages(currentStudentId);
                        const messageIndex = messages.findIndex(m => m.id === tempId);
                        if (messageIndex !== -1) {
                            messages[messageIndex] = {
                                ...messages[messageIndex],
                                id: response.data.message_id,
                                status: 'sent',
                                created_at: response.data.created_at
                            };
                            saveMessages(currentStudentId, messages);
                            renderMessages(messages);
                            showToast('Success', 'Message sent successfully', 'success');
                        }
                    } else {
                        showToast('Error', 'Failed to send message: ' + (response.message || 'Unknown error'), 'error');
                        const messages = loadSavedMessages(currentStudentId);
                        const messageIndex = messages.findIndex(m => m.id === tempId);
                        if (messageIndex !== -1) {
                            messages[messageIndex].status = 'failed';
                            saveMessages(currentStudentId, messages);
                            renderMessages(messages);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error sending message:', error);
                    messageInput.prop('disabled', false);
                    $('#sendMessage').prop('disabled', false);
                    const messages = loadSavedMessages(currentStudentId);
                    const messageIndex = messages.findIndex(m => m.id === tempId);
                    if (messageIndex !== -1) {
                        messages[messageIndex].status = 'failed';
                        saveMessages(currentStudentId, messages);
                        renderMessages(messages);
                    }
                    showToast('Error', 'Failed to send message. Please try again.', 'error');
                }
            });
        }

        // Function to show toast notification
        function showToast(title, message, type = 'info') {
    if (typeof Toastify !== 'undefined') {
        Toastify({
            text: `<strong>${title}</strong><br>${message}`,
            duration: 5000,
            gravity: 'top',
            position: 'right',
            style: {
                background: type === 'error' ? '#dc3545' :
                            type === 'success' ? '#28a745' :
                            type === 'warning' ? '#ffc107' : '#17a2b8'
            },
            stopOnFocus: true,
            className: 'custom-toast',
            escapeMarkup: true
        }).showToast();
    } else {
        console.log(`Toast: ${title} - ${message}`);
    }
}
        // Function to load contact previews
        function loadContactPreviews() {
            const previews = JSON.parse(localStorage.getItem('contactPreviews') || '{}');
            let hasUpdates = false;
            const now = new Date();
            const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            $('.contact-item').each(function() {
                const adminId = $(this).data('admin-id');
                const previewData = previews[adminId];
                
                if (previewData) {
                    $(this).find('.contact-preview')
                        .text(previewData.preview.trim())
                        .attr('title', previewData.fullMessage);
                    $(this).find('.contact-time').text(previewData.time);
                    hasUpdates = true;
                } else {
                    const initialMessage = 'No messages yet';
                    const newPreview = {
                        preview: initialMessage,
                        fullMessage: initialMessage,
                        time: timeString,
                        timestamp: now.getTime()
                    };
                    previews[adminId] = newPreview;
                    $(this).find('.contact-preview').text(initialMessage);
                    $(this).find('.contact-time').text(timeString);
                    hasUpdates = true;
                }
            });
            
            if (hasUpdates) {
                localStorage.setItem('contactPreviews', JSON.stringify(previews));
                sortContactsByLastMessage();
            }
        }

        // Function to select conversation
        function selectConversation(adminId, adminName) {
            currentAdminId = adminId;
            $('#currentAdminId').val(adminId);
            
            $('.contact-item').removeClass('active');
            $(`[data-admin-id="${adminId}"]`).addClass('active');
            
            const contactItem = $(`[data-admin-id="${adminId}"]`);
            const status = contactItem.find('.contact-status').text().trim();
            const program = 'BS Computer Engineering'; // Default program
            
            $('#chatAvatar').text(adminName.split(' ').map(n => n[0]).join('').toUpperCase());
            $('#chatStudentName').text(adminName);
            $('#chatStudentInfo').text(`${program} • ${status}`);
            
            $('.chat-panel').addClass('has-conversation');
            $('.shared-files-panel').show();
            setTimeout(() => $('.shared-files-panel').addClass('active'), 10);
            $('#emptyState').hide();
            $('#chatContent').show();
            
            $('#messageInput, #sendMessage').prop('disabled', false);
            loadSupervisorMessages(adminId);
            updateFileCounts();
            
            if (window.innerWidth <= 768) {
                $('.contacts-panel').hide();
                $('.chat-panel').addClass('active');
            }
        }

        // Function to update file counts
        function updateFileCounts() {
            const fileCounts = {
                documents: 10,
                photos: 8,
                videos: 3,
                links: 2
            };
            
            $('.summary-item:first .count').text(fileCounts.documents + fileCounts.photos + fileCounts.videos);
            $('.summary-item:last .count').text(fileCounts.links);
            
            $('.category:first .badge').text(fileCounts.documents);
            $('.category:nth-child(2) .badge').text(fileCounts.photos);
            $('.category:last .badge').text(fileCounts.videos);
            
            const sampleFiles = {
                documents: [
                    { name: 'Project_Requirements.pdf', size: '2.4 MB', date: 'May 15, 2025' },
                    { name: 'Meeting_Notes.docx', size: '1.1 MB', date: 'May 10, 2025' }
                ],
                photos: [
                    { name: 'Screenshot_2025.png', size: '1.5 MB', date: 'May 20, 2025' },
                    { name: 'Profile_Picture.jpg', size: '2.1 MB', date: 'May 18, 2025' }
                ],
                videos: [
                    { name: 'Tutorial.mp4', size: '15.7 MB', date: 'May 5, 2024' }
                ]
            };
            
            renderFiles('documents', sampleFiles.documents);
            renderFiles('photos', sampleFiles.photos);
            renderFiles('videos', sampleFiles.videos);
        }

        // Function to render files
        function renderFiles(category, files) {
            const $container = $(`.category:has(.fa-${category === 'documents' ? 'file-pdf' : category === 'photos' ? 'image' : 'video'}) .category-items`);
            $container.empty();
            
            if (files.length === 0) {
                $container.append('<div class="text-muted small p-2">No files found</div>');
                return;
            }
            
            files.forEach(file => {
                const iconClass = getFileIconClass(file.name);
                const $fileItem = $(`
                    <div class="file-item">
                        <div class="file-icon">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="file-info">
                            <div class="file-name" title="${file.name}">${file.name}</div>
                            <div class="file-meta">${file.size} • ${file.date}</div>
                        </div>
                    </div>
                `);
                $container.append($fileItem);
            });
        }

        // Function to get file icon class
        function getFileIconClass(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const docExts = ['doc', 'docx', 'txt', 'rtf'];
            const pdfExts = ['pdf'];
            const imgExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            const videoExts = ['mp4', 'mov', 'avi', 'wmv'];
            
            if (docExts.includes(ext)) return 'fas fa-file-word text-primary';
            if (pdfExts.includes(ext)) return 'fas fa-file-pdf text-danger';
            if (imgExts.includes(ext)) return 'fas fa-file-image text-success';
            if (videoExts.includes(ext)) return 'fas fa-file-video text-warning';
            return 'fas fa-file';
        }

        // Initialize Pusher
        function initializePusher() {
            if (typeof Pusher === 'undefined') {
                console.error('Pusher library not loaded');
                showToast('Error', 'Real-time messaging is unavailable. Please refresh the page.', 'error');
                return null;
            }
            
            try {
                const appKey = '<?php echo defined('PUSHER_APP_KEY') ? PUSHER_APP_KEY : ''; ?>';
                const cluster = '<?php echo defined('PUSHER_APP_CLUSTER') ? PUSHER_APP_CLUSTER : ''; ?>';
                const adminId = '<?php echo isset($_SESSION['auth_user']['admin_id']) ? $_SESSION['auth_user']['admin_id'] : '0'; ?>';
                console.log('Admin ID from session:', '<?php echo $_SESSION['auth_user']['admin_id'] ?? 'not set'; ?>');
                
                if (!appKey || !cluster || !adminId || adminId === '0') {
                    console.error('Pusher initialization failed - missing credentials', { appKey, cluster, adminId });
                    showToast('Error', 'Unable to connect to real-time messaging due to configuration issues.', 'error');
                    return null;
                }
                
                const pusher = new Pusher(appKey, {
                    cluster: cluster,
                    forceTLS: true,
                    authEndpoint: '../connection/pusher_auth.php',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': '<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>'
                        }
                    }
                });
                
                const channel = pusher.subscribe('private-admin-' + adminId);
                
                channel.bind('new_message', function(data) {
                    console.log('New message received:', data);
                    if (data.sender_type === 'supervisor') {
                        const messageId = data.id || 'msg_' + Date.now();
                        const newMessage = {
                            id: messageId,
                            sender: 'them',
                            text: data.content,
                            time: data.created_at ? formatTime(data.created_at) : new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                            created_at: data.created_at || new Date().toISOString(),
                            is_read: false
                        };
                        
                        const senderId = data.sender_id || data.admin_id;
                        if (!currentAdminId && senderId) {
                            currentAdminId = senderId;
                            $('#currentAdminId').val(senderId);
                        }
                        
                        const existingMessages = loadSavedMessages(senderId || currentStudentId);
                        const messageExists = existingMessages.some(msg => msg.id === messageId || (msg.text === data.content && msg.sender === 'them'));
                        
                        if (!messageExists) {
                            existingMessages.push(newMessage);
                            saveMessages(senderId || currentStudentId, existingMessages);
                            
                            if (senderId !== currentStudentId) {
                                showToast('New Message', `New message from admin ${senderId}`, 'info');
                                updateUnreadCount(senderId, true);
                            }
                            
                            // Update contact preview with new message and time
                            const previewTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            updateContactPreview(senderId || currentStudentId, data.content, false, previewTime);
                            
                            if (!currentStudentId || senderId === currentStudentId) {
                                renderMessages(existingMessages);
                                if (senderId === currentStudentId) {
                                    markMessagesAsRead(senderId);
                                }
                                scrollToBottom();
                            }
                        }
                    }
                });
                
                pusher.connection.bind('connected', function() {
                    console.log('Pusher connected successfully');
                });
                
                pusher.connection.bind('error', function(err) {
                    console.error('Pusher connection error:', err);
                    showToast('Error', 'Real-time messaging connection error.', 'error');
                });
                
                channel.bind('pusher:subscription_succeeded', function() {
                    console.log('Successfully subscribed to channel: private-admin-' + adminId);
                    showToast('Success', 'Real-time messaging connected', 'success');
                });
                
                channel.bind('pusher:subscription_error', function(status, response) {
                    console.error('Pusher subscription error:', {
                        status: status,
                        response: response || 'No response received',
                        channel: 'private-admin-' + adminId
                    });
                    showToast('Error', `Failed to establish real-time messaging connection (Status: ${status}).`, 'error');
                });
                
                console.log('Pusher initialized for channel: private-admin-' + adminId);
                return pusher;
            } catch (error) {
                console.error('Error initializing Pusher:', error);
                showToast('Error', 'Failed to initialize real-time messaging.', 'error');
                return null;
            }
        }

        // Event handlers
        $(document).on('click', '.contact-item', function() {
            const adminId = $(this).data('admin-id');
            const adminName = $(this).find('.contact-name').text().trim();
            if (adminId) {
                selectConversation(adminId, adminName);
            }
        });

        $('#sendMessage').on('click', function() {
            sendMessage();
        });

        $('#messageInput').on('keypress', function(e) {
    if (e.which === 13 && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

        $('.back-to-contacts').on('click', function() {
            $('.shared-files-panel').removeClass('active');
            setTimeout(() => {
                $('.shared-files-panel').hide();
                $('.chat-panel').removeClass('has-conversation');
            }, 300);
            $('#emptyState').show();
            $('#chatContent').hide();
            $('.contact-item.active').removeClass('active');
            currentStudentId = null;
            $('#currentStudentId').val('');
            $('#messageInput, #sendMessage').prop('disabled', true);
            $('#messagesList').empty();
            if (window.innerWidth <= 768) {
                $('.contacts-panel').show();
                $('.chat-panel').removeClass('active');
            }
        });

        $('.category-header').on('click', function() {
            const $category = $(this).parent('.category');
            $category.toggleClass('active');
            $('.category.active').not($category).removeClass('active');
        });

        $('.file-item').on('click', function() {
            const fileName = $(this).find('.file-name').text();
            showToast('Info', `Opening file: ${fileName}`, 'info');
            // TODO: Add actual file opening/download logic
        });

        // Search functionality
        $('#searchContacts').on('input', function() {
            const searchTerm = $(this).val().trim().toLowerCase();
            if (searchTerm === '') {
                $('.contact-item').show();
                return;
            }
            $('.contact-item').each(function() {
                const name = $(this).find('.contact-name').text().toLowerCase();
                const email = $(this).data('email') ? $(this).data('email').toLowerCase() : '';
                $(this).toggle(name.includes(searchTerm) || email.includes(searchTerm));
            });
        });

        // Initialize Pusher on page load
        initializePusher();

        // Load contact previews
        loadContactPreviews();
    });
    </script>
</body>
</html>