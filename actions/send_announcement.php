<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

if (!isset($_POST['title']) || !isset($_POST['message']) || !isset($_POST['announcement_type'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$coach_id = $_SESSION['user_id'];

try {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $type = $_POST['announcement_type'] === 'team' ? 'TEAM_SPECIFIC' : 'GENERAL';
    $team_id = ($type === 'TEAM_SPECIFIC' && isset($_POST['team'])) ? $_POST['team'] : null;

    // Insert notification
    $query = "INSERT INTO v_ball_notifications (sender_id, notification_type, title, message, team_id)
              VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssi", $coach_id, $type, $title, $message, $team_id);
    $stmt->execute();
    $notification_id = $stmt->insert_id;

    // Add recipients
    if ($type === 'TEAM_SPECIFIC') {
        $recipient_query = "INSERT INTO v_ball_notification_recipients (notification_id, user_id)
                          SELECT ?, p.user_id
                          FROM v_ball_players p
                          WHERE p.team_id = ?";
        $stmt = $conn->prepare($recipient_query);
        $stmt->bind_param("ii", $notification_id, $team_id);
    } else {
        $recipient_query = "INSERT INTO v_ball_notification_recipients (notification_id, user_id)
                          SELECT ?, user_id FROM v_ball_users";
        $stmt = $conn->prepare($recipient_query);
        $stmt->bind_param("i", $notification_id);
    }
    $stmt->execute();
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close(); 