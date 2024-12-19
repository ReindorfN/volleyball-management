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
                m.match_id,
                m.match_date,
                m.venue,
                m.match_status,
                t1.team_name as team1,
                t2.team_name as team2
              FROM v_ball_matches m
              JOIN v_ball_teams t1 ON m.team1_id = t1.team_id
              JOIN v_ball_teams t2 ON m.team2_id = t2.team_id
              WHERE t1.coach_id = ? OR t2.coach_id = ?
              ORDER BY m.match_date ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $coach_id, $coach_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $matches = [];
    while ($match = $result->fetch_assoc()) {
        $matches[] = $match;
    }
    
    echo json_encode(['success' => true, 'matches' => $matches]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close(); 