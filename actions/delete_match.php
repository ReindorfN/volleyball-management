<?php
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

$response = array();

if(isset($_POST['match_id'])) {
    $match_id = $_POST['match_id'];
    
    // Prepare the delete query
    $query = "DELETE FROM v_ball_matches WHERE match_id = ?";
    
    if($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $match_id);
        
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
    $response['error'] = "Match ID not provided";
}

echo json_encode($response);
$conn->close();
?>
