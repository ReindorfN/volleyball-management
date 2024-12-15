<?php
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

$response = array();

if(isset($_GET['match_id'])) {
    $match_id = $_GET['match_id'];
    
    $query = "SELECT m.*, t1.team_name as team1_name, t2.team_name as team2_name 
              FROM v_ball_matches m
              LEFT JOIN v_ball_teams t1 ON m.team1_id = t1.team_id
              LEFT JOIN v_ball_teams t2 ON m.team2_id = t2.team_id
              WHERE m.match_id = ?";
    
    if($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $match_id);
        
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $match = $result->fetch_assoc();
            
            if($match) {
                echo json_encode($match);
            } else {
                echo json_encode(['error' => 'Match not found']);
            }
        } else {
            echo json_encode(['error' => 'Error executing query: ' . $conn->error]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Error preparing query: ' . $conn->error]);
    }
} else {
    echo json_encode(['error' => 'Match ID not provided']);
}

$conn->close();
?>
