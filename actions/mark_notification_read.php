<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'];
    $notificationId = $data['notification_id'];

    // First, check if a record exists
    $checkQuery = "SELECT * FROM v_ball_notification_recipients 
                  WHERE notification_id = ? AND user_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $notificationId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // If no record exists, insert one
        $insertQuery = "INSERT INTO v_ball_notification_recipients 
                       (notification_id, user_id, read_status, read_at) 
                       VALUES (?, ?, 1, NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ii", $notificationId, $userId);
        $stmt->execute();
    } else {
        // If record exists, update it
        $updateQuery = "UPDATE v_ball_notification_recipients 
                       SET read_status = 1, read_at = NOW() 
                       WHERE notification_id = ? AND user_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ii", $notificationId, $userId);
        $stmt->execute();
    }

    echo json_encode([
        'success' => true
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close(); 