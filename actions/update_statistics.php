<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

if (!isset($_POST['player_id']) || !isset($_POST['spikes']) || 
    !isset($_POST['blocks']) || !isset($_POST['serves']) || 
    !isset($_POST['errors'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$coach_id = $_SESSION['user_id'];

try {
    // Verify coach has permission for this player
    $verify_query = "SELECT COUNT(*) as count 
                    FROM v_ball_players p
                    JOIN v_ball_teams t ON p.team_id = t.team_id
                    WHERE p.player_id = ? AND t.coach_id = ?";
    
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $_POST['player_id'], $coach_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] == 0) {
        throw new Exception('Unauthorized player access');
    }

    // Insert or update statistics
    $stats_query = "INSERT INTO v_ball_statistics 
                    (player_id, spikes, blocks, serves, errors) 
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    spikes = VALUES(spikes),
                    blocks = VALUES(blocks),
                    serves = VALUES(serves),
                    errors = VALUES(errors)";
    
    $stmt = $conn->prepare($stats_query);
    $stmt->bind_param("iiiii", 
        $_POST['player_id'],
        $_POST['spikes'],
        $_POST['blocks'],
        $_POST['serves'],
        $_POST['errors']
    );
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close(); 