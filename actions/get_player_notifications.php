<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $userId = $_SESSION['user_id'];
    
    // Get system-wide notifications (sent by admin to all users)
    $notificationsQuery = "SELECT 
        n.notification_id,
        n.title,
        n.message,
        n.created_at,
        COALESCE(nr.read_status, 0) as read_status
    FROM v_ball_notifications n
    LEFT JOIN v_ball_notification_recipients nr 
        ON n.notification_id = nr.notification_id 
        AND nr.user_id = ?
    WHERE n.notification_type = 'GENERAL'
    ORDER BY n.created_at DESC
    LIMIT 50";
              
    $stmt = $conn->prepare($notificationsQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get unread count
    $unreadQuery = "SELECT COUNT(*) as unread_count
                    FROM v_ball_notifications n
                    LEFT JOIN v_ball_notification_recipients nr 
                        ON n.notification_id = nr.notification_id 
                        AND nr.user_id = ?
                    WHERE n.notification_type = 'GENERAL'
                    AND (nr.read_status = 0 OR nr.read_status IS NULL)";
                    
    $stmt = $conn->prepare($unreadQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $unreadCount = $stmt->get_result()->fetch_assoc()['unread_count'];

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unreadCount' => $unreadCount
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close(); 