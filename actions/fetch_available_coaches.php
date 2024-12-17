<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    // Fetch coaches who aren't assigned to any team
    $query = "SELECT u.user_id, u.first_name, u.last_name 
              FROM v_ball_users u 
              LEFT JOIN v_ball_teams t ON u.user_id = t.coach_id
              WHERE u.role = 'coach' AND t.team_id IS NULL";
    
    $result = $conn->query($query);
    $coaches = [];

    while ($row = $result->fetch_assoc()) {
        $coaches[] = [
            'id' => $row['user_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name']
        ];
    }

    echo json_encode([
        'success' => true,
        'coaches' => $coaches
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch coaches'
    ]);
}

$conn->close(); 