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
                m.match_id,
                m.match_date,
                m.venue,
                m.match_status,
                t1.team_name as team1_name,
                t2.team_name as team2_name,
                m.team1_score,
                m.team2_score,
                m.notes
              FROM v_ball_matches m
              JOIN v_ball_teams t1 ON m.team1_id = t1.team_id
              JOIN v_ball_teams t2 ON m.team2_id = t2.team_id
              WHERE t1.coach_id = ? OR t2.coach_id = ?
              ORDER BY m.match_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $coachId, $coachId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $matches = [];
    while ($row = $result->fetch_assoc()) {
        $matches[] = [
            'match_id' => $row['match_id'],
            'date' => $row['match_date'],
            'venue' => $row['venue'],
            'status' => $row['match_status'],
            'team1_name' => $row['team1_name'],
            'team2_name' => $row['team2_name'],
            'team1_score' => $row['team1_score'],
            'team2_score' => $row['team2_score'],
            'notes' => $row['notes']
        ];
    }

    echo json_encode([
        'success' => true,
        'matches' => $matches
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch matches'
    ]);
}

$conn->close(); 