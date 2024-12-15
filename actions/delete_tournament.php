<?php
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

$response = array();

if(isset($_POST['tournament_id'])) {
    $tournament_id = $_POST['tournament_id'];
    
    $query = "DELETE FROM v_ball_tournaments WHERE tournament_id = ?";
    
    if($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $tournament_id);
        
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
    $response['error'] = "Tournament ID not provided";
}

echo json_encode($response);
$conn->close();
?>
