<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['player_id']) || !isset($data['team_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$coach_id = $_SESSION['user_id'];

try {
    // Verify coach has permission for this team
    $verify_query = "SELECT COUNT(*) as count 
                    FROM v_ball_teams 
                    WHERE team_id = ? AND coach_id = ?";
    
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $data['team_id'], $coach_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] == 0) {
        throw new Exception('Unauthorized team access');
    }

    // Update player record to remove team association
    $update_query = "UPDATE v_ball_players 
                    SET team_id = NULL 
                    WHERE player_id = ? AND team_id = ?";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $data['player_id'], $data['team_id']);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Player not found or already removed');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close(); 