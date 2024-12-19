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
                p.player_id,
                u.first_name,
                u.last_name,
                p.position,
                p.jersey_number,
                t.team_name
              FROM v_ball_players p
              JOIN v_ball_users u ON p.user_id = u.user_id
              JOIN v_ball_teams t ON p.team_id = t.team_id
              WHERE t.coach_id = ?
              ORDER BY t.team_name, u.last_name, u.first_name";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $coachId);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $players = [];
    
    while ($row = $result->fetch_assoc()) {
        $players[] = [
            'player_id' => $row['player_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'position' => $row['position'],
            'jersey_number' => $row['jersey_number'],
            'team' => $row['team_name']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'players' => $players
    ]);

} catch (Exception $e) {
    error_log("Team Players Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch players',
        'debug' => $e->getMessage()
    ]);
}

$conn->close(); 