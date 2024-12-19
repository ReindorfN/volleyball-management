<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    // Get current date in MySQL format
    $currentDate = date('Y-m-d H:i:s');

    // Fetch matches with team names and scores
    $query = "SELECT 
                m.match_id,
                m.match_date,
                m.venue,
                m.score_team1,
                m.score_team2,
                m.is_featured,
                t1.team_name as team1_name,
                t2.team_name as team2_name
              FROM v_ball_matches m
              JOIN v_ball_teams t1 ON m.team1_id = t1.team_id
              JOIN v_ball_teams t2 ON m.team2_id = t2.team_id
              WHERE m.match_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY)
              ORDER BY m.match_date ASC";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $matches = [];
    while ($row = $result->fetch_assoc()) {
        // Format match date
        $row['match_date'] = date('Y-m-d H:i:s', strtotime($row['match_date']));
        
        // Convert scores to integers or null
        $row['score_team1'] = $row['score_team1'] !== null ? (int)$row['score_team1'] : null;
        $row['score_team2'] = $row['score_team2'] !== null ? (int)$row['score_team2'] : null;
        
        // Convert is_featured to boolean
        $row['is_featured'] = (bool)$row['is_featured'];
        
        $matches[] = $row;
    }

    echo json_encode([
        'success' => true,
        'matches' => $matches,
        'current_time' => $currentDate
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch matches: ' . $e->getMessage()
    ]);
}

$conn->close();