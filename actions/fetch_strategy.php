<?php
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

if(isset($_GET['match_id']) && isset($_GET['coach_id'])) {
    $match_id = $_GET['match_id'];
    $coach_id = $_GET['coach_id'];
    
    $query = "SELECT strategy_text FROM v_ball_match_strategies 
              WHERE match_id = ? AND coach_id = ?";
    
    if($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ii", $match_id, $coach_id);
        
        if($stmt->execute()) {
            $result = $stmt->get_result();
            $strategy = $result->fetch_assoc();
            
            if($strategy) {
                echo json_encode([
                    'success' => true, 
                    'strategy' => $strategy['strategy_text']
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'strategy' => ''
                ]);
            }
        } else {
            echo json_encode([
                'success' => false, 
                'error' => 'Error executing query'
            ]);
        }
        
        $stmt->close();
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Error preparing query'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Match ID or Coach ID not provided'
    ]);
}

$conn->close();
?>
