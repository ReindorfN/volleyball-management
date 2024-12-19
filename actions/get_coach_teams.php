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
    // Get teams coached by this coach
    $query = "SELECT t.team_id, t.team_name 
              FROM v_ball_teams t 
              WHERE t.coach_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $coach_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $teams = [];
    while ($team = $result->fetch_assoc()) {
        // Get players for each team
        $player_query = "SELECT 
                            p.player_id,
                            CONCAT(u.first_name, ' ', u.last_name) as name,
                            p.position,
                            p.jersey_number
                        FROM v_ball_players p
                        JOIN v_ball_users u ON p.user_id = u.user_id
                        WHERE p.team_id = ?";
        
        $player_stmt = $conn->prepare($player_query);
        $player_stmt->bind_param("i", $team['team_id']);
        $player_stmt->execute();
        $player_result = $player_stmt->get_result();
        
        $players = [];
        while ($player = $player_result->fetch_assoc()) {
            $players[] = $player;
        }
        
        $teams[] = [
            'team_id' => $team['team_id'],
            'team_name' => $team['team_name'],
            'players' => $players
        ];
    }
    
    echo json_encode(['success' => true, 'teams' => $teams]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close(); 