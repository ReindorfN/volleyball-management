<?php
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

$response = array();

if(isset($_POST['match_id']) && isset($_POST['team1_id']) && isset($_POST['team2_id']) 
   && isset($_POST['match_date']) && isset($_POST['venue'])) {
    
    $match_id = $_POST['match_id'];
    $team1_id = $_POST['team1_id'];
    $team2_id = $_POST['team2_id'];
    $match_date = $_POST['match_date'];
    $venue = $_POST['venue'];
    
    $query = "UPDATE v_ball_matches SET team1_id = ?, team2_id = ?, match_date = ?, venue = ? 
             WHERE match_id = ?";
    
    if($stmt = $conn->prepare($query)) {
        $stmt->bind_param("iissi", $team1_id, $team2_id, $match_date, $venue, $match_id);
        
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
