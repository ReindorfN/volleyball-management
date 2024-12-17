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
                m.match_id,
                m.match_date,
                m.venue,
                m.score_team1,
                m.score_team2,
                m.match_status,
                t1.team_name as team1_name,
                t2.team_name as team2_name,
                (SELECT COUNT(*) FROM v_ball_match_strategies 
                 WHERE match_id = m.match_id) as strategy_count,
                (SELECT COUNT(DISTINCT player_id) FROM v_ball_statistics 
                 WHERE match_id = m.match_id) as player_count
              FROM v_ball_matches m
              JOIN v_ball_teams t1 ON m.team1_id = t1.team_id
              JOIN v_ball_teams t2 ON m.team2_id = t2.team_id
              ORDER BY 
                CASE 
                    WHEN m.match_status = 'ongoing' THEN 1
                    WHEN m.match_status = 'scheduled' AND m.match_date >= CURDATE() THEN 2
                    WHEN m.match_status = 'scheduled' THEN 3
                    ELSE 4
                END,
                m.match_date DESC";

    $result = $conn->query($query);
    $matches = [];

    while ($row = $result->fetch_assoc()) {
        // Format the match date
        $row['match_date'] = date('Y-m-d', strtotime($row['match_date']));
        
        // Ensure scores are numeric
        $row['score_team1'] = intval($row['score_team1']);
        $row['score_team2'] = intval($row['score_team2']);
        
        // Add additional metadata
        $row['has_strategies'] = $row['strategy_count'] > 0;
        $row['players_participated'] = $row['player_count'];
        
        // Remove count fields from final output
        unset($row['strategy_count']);
        unset($row['player_count']);
        
        $matches[] = $row;
    }

    echo json_encode([
        'success' => true,
        'matches' => $matches
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch matches: ' . $e->getMessage()
    ]);
}

$conn->close(); 