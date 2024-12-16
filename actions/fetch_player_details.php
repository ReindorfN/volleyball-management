<?php
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

if(isset($_GET['player_id'])) {
    $player_id = $_GET['player_id'];
    
    $query = "SELECT p.*, u.first_name, u.last_name 
              FROM v_ball_players p
              JOIN v_ball_users u ON p.user_id = u.user_id
              WHERE p.player_id = ?";
    
    if($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $player_id);
        
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $player = $result->fetch_assoc();
            
            if($player) {
                echo json_encode($player);
            } else {
                echo json_encode(['error' => 'Player not found']);
            }
        } else {
            echo json_encode(['error' => 'Error executing query: ' . $conn->error]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Error preparing query: ' . $conn->error]);
    }
} else {
    echo json_encode(['error' => 'Player ID not provided']);
}

$conn->close();
?>
