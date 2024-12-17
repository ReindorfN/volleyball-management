<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $query = "SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.created_at,
                CONCAT(u.first_name, ' ', u.last_name) as sender_name,
                (SELECT COUNT(*) FROM v_ball_notification_recipients WHERE notification_id = n.notification_id) as recipient_count
              FROM v_ball_notifications n
              JOIN v_ball_users u ON n.sender_id = u.user_id
              ORDER BY n.created_at DESC";

    $result = $conn->query($query);
    $announcements = [];

    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }

    echo json_encode([
        'success' => true,
        'announcements' => $announcements
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch announcements'
    ]);
}

$conn->close(); 