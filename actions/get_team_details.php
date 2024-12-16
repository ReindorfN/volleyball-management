<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['team_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    // Get team details
    $teamQuery = "SELECT t.*, u.first_name, u.last_name 
                  FROM v_ball_teams t
                  JOIN v_ball_users u ON t.coach_id = u.user_id
                  WHERE t.team_id = ?";
    
    $stmt = $conn->prepare($teamQuery);
    $stmt->bind_param("i", $_GET['team_id']);
    $stmt->execute();
    $teamResult = $stmt->get_result();
    $team = $teamResult->fetch_assoc();

    // Get team players
    $playersQuery = "SELECT p.*, u.first_name, u.last_name 
                     FROM v_ball_players p
                     JOIN v_ball_users u ON p.user_id = u.user_id
                     WHERE p.team_id = ?";
    
    $stmt = $conn->prepare($playersQuery);
    $stmt->bind_param("i", $_GET['team_id']);
    $stmt->execute();
    $playersResult = $stmt->get_result();
    $players = [];
    
    while ($player = $playersResult->fetch_assoc()) {
        $players[] = [
            'name' => $player['first_name'] . ' ' . $player['last_name'],
            'position' => $player['position'],
            'jersey_number' => $player['jersey_number']
        ];
    }

    // Get recent matches
    $matchesQuery = "SELECT 
                        m.*,
                        t1.team_name as team1_name,
                        t2.team_name as team2_name
                     FROM v_ball_matches m
                     JOIN v_ball_teams t1 ON m.team1_id = t1.team_id
                     JOIN v_ball_teams t2 ON m.team2_id = t2.team_id
                     WHERE (m.team1_id = ? OR m.team2_id = ?)
                     AND m.match_status = 'completed'
                     ORDER BY m.match_date DESC
                     LIMIT 5";
    
    $stmt = $conn->prepare($matchesQuery);
    $stmt->bind_param("ii", $_GET['team_id'], $_GET['team_id']);
    $stmt->execute();
    $matchesResult = $stmt->get_result();
    $matches = [];
    
    while ($match = $matchesResult->fetch_assoc()) {
        $matches[] = [
            'date' => $match['match_date'],
            'opponent' => ($match['team1_id'] == $_GET['team_id']) 
                          ? $match['team2_name'] 
                          : $match['team1_name'],
            'score' => $match['score_team1'] . ' - ' . $match['score_team2']
        ];
    }

    echo json_encode([
        'success' => true,
        'team' => [
            'name' => $team['team_name'],
            'coach' => $team['first_name'] . ' ' . $team['last_name'],
            'players' => $players,
            'recent_matches' => $matches
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch team details'
    ]);
}

$conn->close(); 