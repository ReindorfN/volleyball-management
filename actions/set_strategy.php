<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

if (!isset($_POST['match']) || !isset($_POST['strategy'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$coach_id = $_SESSION['user_id'];
$match_id = $_POST['match'];
$strategy = $_POST['strategy'];

try {
    // Verify coach has permission for this match
    $verify_query = "SELECT COUNT(*) as count
                    FROM v_ball_matches m
                    JOIN v_ball_teams t ON m.team1_id = t.team_id OR m.team2_id = t.team_id
                    WHERE m.match_id = ? AND t.coach_id = ?";
    
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $match_id, $coach_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] == 0) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized match access']);
        exit;
    }
    
    // Insert strategy
    $insert_query = "INSERT INTO v_ball_match_strategies (match_id, coach_id, strategy_text)
                    VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iis", $match_id, $coach_id, $strategy);
    $stmt->execute();
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close(); 