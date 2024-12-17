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
                p.player_id,
                u.first_name,
                u.last_name,
                t.team_name,
                p.position,
                p.jersey_number
              FROM v_ball_players p
              JOIN v_ball_users u ON p.user_id = u.user_id
              LEFT JOIN v_ball_teams t ON p.team_id = t.team_id
              ORDER BY t.team_name, u.last_name, u.first_name";

    $result = $conn->query($query);
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
        'error' => 'Failed to fetch players'
    ]);
}

$conn->close(); 