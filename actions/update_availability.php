<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $conn->begin_transaction();

        // Update player availability
        $query = "INSERT INTO v_ball_player_availability 
                 (player_id, match_id, is_available, reason) 
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 is_available = VALUES(is_available),
                 reason = VALUES(reason)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiis", 
            $_SESSION['user_id'],
            $data['match_id'],
            $data['is_available'],
            $data['reason'] ?? null
        );
        
        $stmt->execute();

        // Notify coach through notification system
        $notifyQuery = "INSERT INTO v_ball_notifications 
                       (sender_id, notification_type, title, message, team_id)
                       SELECT ?, 'MATCH_AVAILABILITY', 
                              CONCAT('Player Availability Update - Match #', ?),
                              CONCAT(?, ' for upcoming match'),
                              p.team_id
                       FROM v_ball_players p
                       WHERE p.user_id = ?";
        
        $availabilityStatus = $data['is_available'] ? 'Available' : 'Not Available';
        $stmt = $conn->prepare($notifyQuery);
        $stmt->bind_param("iisi", 
            $_SESSION['user_id'],
            $data['match_id'],
            $availabilityStatus,
            $_SESSION['user_id']
        );
        
        $stmt->execute();

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

$conn->close(); 