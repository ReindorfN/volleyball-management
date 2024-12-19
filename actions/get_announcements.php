<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$coach_id = $_SESSION['user_id'];

try {
    $type = isset($_GET['type']) ? $_GET['type'] : 'all';
    
    $query = "SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.notification_type,
                n.created_at,
                t.team_name,
                CONCAT(u.first_name, ' ', u.last_name) as sender_name
              FROM v_ball_notifications n
              LEFT JOIN v_ball_teams t ON n.team_id = t.team_id
              JOIN v_ball_users u ON n.sender_id = u.user_id
              WHERE 1=1";
    
    // Add type filter
    if ($type === 'team') {
        $query .= " AND n.notification_type = 'TEAM_SPECIFIC' AND t.coach_id = ?";
    } elseif ($type === 'general') {
        $query .= " AND n.notification_type = 'GENERAL'";
    } else {
        $query .= " AND (n.notification_type = 'GENERAL' OR (n.notification_type = 'TEAM_SPECIFIC' AND t.coach_id = ?))";
    }
    
    $query .= " ORDER BY n.created_at DESC";
    
    $stmt = $conn->prepare($query);
    if ($type === 'team' || $type === 'all') {
        $stmt->bind_param("i", $coach_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    
    echo json_encode(['success' => true, 'announcements' => $announcements]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close(); 