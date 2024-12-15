<?php
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

$response = array();

if(isset($_POST['tournament_id']) && isset($_POST['tournament_name']) 
   && isset($_POST['start_date']) && isset($_POST['end_date'])) {
    
    $tournament_id = $_POST['tournament_id'];
    $tournament_name = $_POST['tournament_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    $query = "UPDATE v_ball_tournaments 
              SET tournament_name = ?, start_date = ?, end_date = ? 
              WHERE tournament_id = ?";
    
    if($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sssi", $tournament_name, $start_date, $end_date, $tournament_id);
        
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
