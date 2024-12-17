<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $coachId = $_SESSION['user_id'];
    
    // Get coach and team information
    $query = "SELECT 
                u.first_name,
                u.last_name,
                u.email,
                t.team_name,
                t.team_id,
                (SELECT COUNT(*) FROM v_ball_players WHERE team_id = t.team_id) as player_count,
                (SELECT COUNT(*) FROM v_ball_matches 
                 WHERE (team1_id = t.team_id OR team2_id = t.team_id)
                 AND match_status = 'completed') as matches_played,
                (SELECT COUNT(*) FROM v_ball_matches 
                 WHERE (team1_id = t.team_id OR team2_id = t.team_id)
                 AND match_status = 'scheduled'
                 AND match_date >= CURDATE()) as upcoming_matches
              FROM v_ball_users u
              LEFT JOIN v_ball_teams t ON t.coach_id = u.user_id
              WHERE u.user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $coachId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();

    if (!$profile) {
        throw new Exception("Profile not found");
    }

    echo json_encode([
        'success' => true,
        'profile' => $profile
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close(); 