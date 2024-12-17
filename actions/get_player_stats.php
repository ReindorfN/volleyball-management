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
    // Get coach's team ID first
    $coachId = $_SESSION['user_id'];
    $teamQuery = "SELECT team_id FROM v_ball_teams WHERE coach_id = ?";
    $stmt = $conn->prepare($teamQuery);
    $stmt->bind_param("i", $coachId);
    $stmt->execute();
    $teamResult = $stmt->get_result();
    $team = $teamResult->fetch_assoc();

    if (!$team) {
        throw new Exception("No team found for this coach");
    }

    // Get player statistics
    $query = "SELECT 
                CONCAT(u.first_name, ' ', u.last_name) as player_name,
                p.position,
                p.jersey_number,
                COUNT(DISTINCT s.match_id) as matches_played,
                ROUND(AVG(s.points_scored), 1) as avg_points,
                ROUND(AVG(s.assists), 1) as avg_assists,
                ROUND(AVG(s.blocks), 1) as avg_blocks,
                ROUND(AVG(s.digs), 1) as avg_digs
              FROM v_ball_players p
              JOIN v_ball_users u ON p.user_id = u.user_id
              LEFT JOIN v_ball_statistics s ON p.player_id = s.player_id
              WHERE p.team_id = ?
              GROUP BY p.player_id, u.first_name, u.last_name, p.position, p.jersey_number";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $team['team_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $players = [];
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }

    echo json_encode([
        'success' => true,
        'players' => $players
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close(); 