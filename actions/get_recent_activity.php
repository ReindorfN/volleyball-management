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
    $query = "SELECT 
                a.activity_id,
                a.activity_type,
                a.description,
                a.timestamp,
                u.first_name,
                u.last_name
              FROM v_ball_activity_log a
              LEFT JOIN v_ball_users u ON a.user_id = u.user_id
              ORDER BY a.timestamp DESC
              LIMIT 10";

    $result = $conn->query($query);
    $activities = [];

    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'id' => $row['activity_id'],
            'type' => $row['activity_type'],
            'description' => $row['description'],
            'timestamp' => $row['timestamp'],
            'user' => $row['first_name'] . ' ' . $row['last_name']
        ];
    }

    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch recent activities'
    ]);
}

$conn->close(); 