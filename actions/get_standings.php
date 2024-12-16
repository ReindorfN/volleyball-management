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
                t.team_id,
                t.team_name,
                COUNT(m.match_id) as matches_played,
                SUM(CASE 
                    WHEN (m.team1_id = t.team_id AND m.score_team1 > m.score_team2) OR
                         (m.team2_id = t.team_id AND m.score_team2 > m.score_team1) 
                    THEN 1 ELSE 0 END) as matches_won,
                SUM(CASE 
                    WHEN (m.team1_id = t.team_id AND m.score_team1 < m.score_team2) OR
                         (m.team2_id = t.team_id AND m.score_team2 < m.score_team1) 
                    THEN 1 ELSE 0 END) as matches_lost
              FROM v_ball_teams t
              LEFT JOIN v_ball_matches m ON (t.team_id = m.team1_id OR t.team_id = m.team2_id)
                   AND m.match_status = 'completed'
              GROUP BY t.team_id, t.team_name
              ORDER BY matches_won DESC, matches_lost ASC";

    $result = $conn->query($query);
    $standings = [];
    $rank = 1;

    while ($row = $result->fetch_assoc()) {
        $row['rank'] = $rank++;
        $row['points'] = $row['matches_won'] * 2; // 2 points per win
        $standings[] = $row;
    }

    echo json_encode([
        'success' => true,
        'standings' => $standings
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch standings'
    ]);
}

$conn->close(); 