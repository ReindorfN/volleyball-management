<?php
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

$response = array();

if(isset($_POST['player_id']) && isset($_POST['position']) 
   && isset($_POST['jersey_number']) && isset($_POST['team_id'])) {
    
    $player_id = $_POST['player_id'];
    $position = $_POST['position'];
    $jersey_number = $_POST['jersey_number'];
    $team_id = $_POST['team_id'];
    
    $query = "UPDATE v_ball_players 
              SET position = ?, jersey_number = ?, team_id = ? 
              WHERE player_id = ?";
    
    if($stmt = $conn->prepare($query)) {
        $stmt->bind_param("siii", $position, $jersey_number, $team_id, $player_id);
        
        if($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['error'] = "Error executing query: " . $conn->error;
        }
        
        $stmt->close();
    } else {
        $response['success'] = false;
        $response['error'] = "Error preparing query: " . $conn->error;
    }
} else {
    $response['success'] = false;
    $response['error'] = "Missing required fields";
}

echo json_encode($response);
$conn->close();
?>
