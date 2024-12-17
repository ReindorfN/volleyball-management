<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $conn->begin_transaction();

        // Create notification
        $query = "INSERT INTO v_ball_notifications 
                 (sender_id, notification_type, title, message, team_id) 
                 VALUES (?, 'GENERAL', ?, ?, NULL)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", 
            $_SESSION['user_id'],
            $data['title'],
            $data['message']
        );
        
        $stmt->execute();
        $notificationId = $conn->insert_id;

        // Add all users as recipients
        $recipientQuery = "INSERT INTO v_ball_notification_recipients (notification_id, user_id)
                          SELECT ?, user_id FROM v_ball_users";
        $stmt = $conn->prepare($recipientQuery);
        $stmt->bind_param("i", $notificationId);
        $stmt->execute();

        require_once 'log_activity.php';
        logActivity($conn, $_SESSION['user_id'], 'ANNOUNCEMENT_CREATED', 
                   "Created general announcement: {$data['title']}");

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

$conn->close(); 