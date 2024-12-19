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
    // Get total players
    $playersQuery = "SELECT COUNT(*) as total_players 
                     FROM v_ball_players p 
                     JOIN v_ball_teams t ON p.team_id = t.team_id 
                     WHERE t.coach_id = ?";
    $stmt = $conn->prepare($playersQuery);
    $stmt->bind_param("i", $coachId);
    $stmt->execute();
    $totalPlayers = $stmt->get_result()->fetch_assoc()['total_players'];

    // Get upcoming matches
    $matchesQuery = "SELECT COUNT(*) as upcoming_matches 
                    FROM v_ball_matches m 
                    JOIN v_ball_teams t ON (m.team1_id = t.team_id OR m.team2_id = t.team_id) 
                    WHERE t.coach_id = ? AND m.match_date > NOW()";
    $stmt = $conn->prepare($matchesQuery);
    $stmt->bind_param("i", $coachId);
    $stmt->execute();
    $upcomingMatches = $stmt->get_result()->fetch_assoc()['upcoming_matches'];

    // Get active teams
    $teamsQuery = "SELECT COUNT(*) as active_teams 
                   FROM v_ball_teams 
                   WHERE coach_id = ?";
    $stmt = $conn->prepare($teamsQuery);
    $stmt->bind_param("i", $coachId);
    $stmt->execute();
    $activeTeams = $stmt->get_result()->fetch_assoc()['active_teams'];

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_players' => $totalPlayers,
            'upcoming_matches' => $upcomingMatches,
            'active_teams' => $activeTeams,
            'win_rate' => calculateWinRate($conn, $coachId)
        ]
    ]);

} catch (Exception $e) {
    error_log("Coach Stats Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch statistics',
        'debug' => $e->getMessage()
    ]);
}

function calculateWinRate($conn, $coachId) {
    $query = "SELECT 
                COUNT(*) as total_matches,
                SUM(CASE 
                    WHEN (m.team1_id = t.team_id AND m.team1_score > m.team2_score) OR
                         (m.team2_id = t.team_id AND m.team2_score > m.team1_score)
                    THEN 1 ELSE 0 END) as wins
              FROM v_ball_matches m
              JOIN v_ball_teams t ON (m.team1_id = t.team_id OR m.team2_id = t.team_id)
              WHERE t.coach_id = ? AND m.match_date < NOW()";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $coachId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['total_matches'] > 0 
        ? round(($result['wins'] / $result['total_matches']) * 100) 
        : 0;
}

$conn->close(); 