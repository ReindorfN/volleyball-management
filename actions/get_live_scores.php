<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $query = "SELECT 
                m.match_id,
                t1.team_name as team1_name,
                t2.team_name as team2_name,
                m.score_team1,
                m.score_team2,
                m.match_date,
                m.venue,
                m.match_status
              FROM v_ball_matches m
              JOIN v_ball_teams t1 ON m.team1_id = t1.team_id
              JOIN v_ball_teams t2 ON m.team2_id = t2.team_id
              WHERE m.match_status = 'ongoing'
              ORDER BY m.match_date DESC";

    $result = $conn->query($query);
    $matches = [];

    while ($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }

    echo json_encode([
        'success' => true,
        'matches' => $matches
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch live scores'
    ]);
}

$conn->close(); 