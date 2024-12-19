<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$coachId = $_SESSION['user_id'];

try {
    $query = "SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.created_at,
                n.notification_type,
                nr.read_status
              FROM v_ball_notifications n
              LEFT JOIN v_ball_notification_recipients nr 
                ON n.notification_id = nr.notification_id 
                AND nr.user_id = ?
              WHERE n.notification_type = 'GENERAL'
              OR n.notification_id IN (
                  SELECT notification_id 
                  FROM v_ball_notification_recipients 
                  WHERE user_id = ?
              )
              ORDER BY n.created_at DESC
              LIMIT 50";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $coachId, $coachId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    $unreadCount = 0;
    
    while ($row = $result->fetch_assoc()) {
        if (!$row['read_status']) {
            $unreadCount++;
        }
        
        $notifications[] = [
            'id' => $row['notification_id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'created_at' => $row['created_at'],
            'type' => $row['notification_type'],
            'read' => (bool)$row['read_status']
        ];
    }

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unreadCount' => $unreadCount
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch notifications'
    ]);
}

$conn->close(); 