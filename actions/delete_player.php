<?php
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

$response = array();

if(isset($_POST['player_id'])) {
    $player_id = $_POST['player_id'];
    
    // First delete related statistics
    $query1 = "DELETE FROM v_ball_statistics WHERE player_id = ?";
    $stmt1 = $conn->prepare($query1);
    $stmt1->bind_param("i", $player_id);
    $stmt1->execute();
    $stmt1->close();
    
    // Then delete the player
    $query2 = "DELETE FROM v_ball_players WHERE player_id = ?";
    
    if($stmt2 = $conn->prepare($query2)) {
        $stmt2->bind_param("i", $player_id);
        
        if($stmt2->execute()) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['error'] = "Error executing query: " . $conn->error;
        }
        
        $stmt2->close();
    } else {
        $response['success'] = false;
        $response['error'] = "Error preparing query: " . $conn->error;
    }
} else {
    $response['success'] = false;
    $response['error'] = "Player ID not provided";
}

echo json_encode($response);
$conn->close();
?> 