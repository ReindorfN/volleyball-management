<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['team_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    // Check if already following
    $checkQuery = "SELECT * FROM v_ball_fan_follows 
                  WHERE fan_id = ? AND team_id = ?";
    
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $_SESSION['user_id'], $data['team_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Unfollow
        $query = "DELETE FROM v_ball_fan_follows 
                  WHERE fan_id = ? AND team_id = ?";
    } else {
        // Follow
        $query = "INSERT INTO v_ball_fan_follows (fan_id, team_id) 
                  VALUES (?, ?)";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $_SESSION['user_id'], $data['team_id']);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'following' => ($result->num_rows === 0)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update follow status'
    ]);
}

$conn->close(); 