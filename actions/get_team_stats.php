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
    $query = "SELECT 
                CONCAT(u.first_name, ' ', u.last_name) as player_name,
                t.team_name,
                COUNT(DISTINCT s.match_id) as matches_played,
                SUM(s.points) as total_points,
                SUM(s.assists) as total_assists,
                SUM(s.blocks) as total_blocks,
                SUM(s.digs) as total_digs,
                SUM(s.serves) as total_serves
              FROM v_ball_players p
              JOIN v_ball_users u ON p.user_id = u.user_id
              JOIN v_ball_teams t ON p.team_id = t.team_id
              LEFT JOIN v_ball_statistics s ON p.player_id = s.player_id
              WHERE t.coach_id = ?
              GROUP BY p.player_id, u.first_name, u.last_name, t.team_name
              ORDER BY t.team_name, total_points DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $coachId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $statistics = [];
    while ($row = $result->fetch_assoc()) {
        $statistics[] = [
            'player_name' => $row['player_name'],
            'team_name' => $row['team_name'],
            'matches_played' => $row['matches_played'],
            'points' => $row['total_points'],
            'assists' => $row['total_assists'],
            'blocks' => $row['total_blocks'],
            'digs' => $row['total_digs'],
            'serves' => $row['total_serves']
        ];
    }

    echo json_encode([
        'success' => true,
        'statistics' => $statistics
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch statistics'
    ]);
}

$conn->close(); 