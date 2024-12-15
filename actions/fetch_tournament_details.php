<?php
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

if(isset($_GET['tournament_id'])) {
    $tournament_id = $_GET['tournament_id'];
    
    $query = "SELECT * FROM v_ball_tournaments WHERE tournament_id = ?";
    
    if($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $tournament_id);
        
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $tournament = $result->fetch_assoc();
            
            if($tournament) {
                echo json_encode($tournament);
            } else {
                echo json_encode(['error' => 'Tournament not found']);
            }
        } else {
            echo json_encode(['error' => 'Error executing query: ' . $conn->error]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Error preparing query: ' . $conn->error]);
    }
} else {
    echo json_encode(['error' => 'Tournament ID not provided']);
}

$conn->close();
?>
