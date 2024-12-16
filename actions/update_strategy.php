<?php
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

$response = array();

if(isset($_POST['match_id']) && isset($_POST['strategy']) && isset($_POST['coach_id'])) {
    $match_id = $_POST['match_id'];
    $strategy = $_POST['strategy'];
    $coach_id = $_POST['coach_id'];
    
    // Check if strategy exists
    $checkQuery = "SELECT strategy_id FROM v_ball_match_strategies 
                  WHERE match_id = ? AND coach_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $match_id, $coach_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        // Update existing strategy
        $query = "UPDATE v_ball_match_strategies 
                 SET strategy_text = ? 
                 WHERE match_id = ? AND coach_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $strategy, $match_id, $coach_id);
    } else {
        // Insert new strategy
        $query = "INSERT INTO v_ball_match_strategies 
                 (match_id, coach_id, strategy_text) 
                 VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $match_id, $coach_id, $strategy);
    }
    
    if($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Strategy updated successfully";
    } else {
        $response['success'] = false;
        $response['error'] = "Error executing query: " . $conn->error;
    }
    
    $stmt->close();
} else {
    $response['success'] = false;
    $response['error'] = "Missing required fields";
}

echo json_encode($response);
$conn->close();
?>
