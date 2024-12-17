<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $query = "SELECT 
                t.team_id,
                t.team_name,
                CONCAT(u.first_name, ' ', u.last_name) as coach_name,
                (SELECT COUNT(*) FROM v_ball_players p WHERE p.team_id = t.team_id) as player_count
              FROM v_ball_teams t
              LEFT JOIN v_ball_users u ON t.coach_id = u.user_id
              ORDER BY t.team_name";

    $result = $conn->query($query);
    $teams = [];

    while ($row = $result->fetch_assoc()) {
        $teams[] = $row;
    }

    echo json_encode([
        'success' => true,
        'teams' => $teams
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch teams'
    ]);
}

$conn->close(); 