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
    $query = "SELECT 
                CONCAT(u.first_name, ' ', u.last_name) as player_name,
                t.team_name,
                COALESCE(SUM(s.spikes), 0) as spikes,
                COALESCE(SUM(s.blocks), 0) as blocks,
                COALESCE(SUM(s.serves), 0) as serves,
                COALESCE(SUM(s.errors), 0) as errors
              FROM v_ball_players p
              JOIN v_ball_users u ON p.user_id = u.user_id
              JOIN v_ball_teams t ON p.team_id = t.team_id
              LEFT JOIN v_ball_statistics s ON p.player_id = s.player_id
              WHERE t.coach_id = ?
              GROUP BY p.player_id, u.first_name, u.last_name, t.team_name
              ORDER BY t.team_name, player_name";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $coach_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $statistics = [];
    while ($stat = $result->fetch_assoc()) {
        $statistics[] = $stat;
    }
    
    echo json_encode(['success' => true, 'statistics' => $statistics]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close(); 