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
    // Get total teams
    $teamsQuery = "SELECT COUNT(*) as total FROM v_ball_teams";
    $teamsResult = $conn->query($teamsQuery);
    $teamsCount = $teamsResult->fetch_assoc()['total'];

    // Get active players
    $playersQuery = "SELECT COUNT(*) as total FROM v_ball_players";
    $playersResult = $conn->query($playersQuery);
    $playersCount = $playersResult->fetch_assoc()['total'];

    // Get upcoming matches
    $matchesQuery = "SELECT COUNT(*) as total FROM v_ball_matches 
                    WHERE match_date >= CURDATE()";
    $matchesResult = $conn->query($matchesQuery);
    $matchesCount = $matchesResult->fetch_assoc()['total'];

    // Get total users
    $usersQuery = "SELECT COUNT(*) as total FROM v_ball_users";
    $usersResult = $conn->query($usersQuery);
    $usersCount = $usersResult->fetch_assoc()['total'];

    echo json_encode([
        'success' => true,
        'stats' => [
            'teams' => $teamsCount,
            'players' => $playersCount,
            'matches' => $matchesCount,
            'users' => $usersCount
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch dashboard statistics'
    ]);
}

$conn->close(); 