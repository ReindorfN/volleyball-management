<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['userId'] ?? null;

    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'User ID is required']);
        exit;
    }

    try {
        $conn->begin_transaction();

        // Check if user exists and is not an admin
        $checkQuery = "SELECT role FROM v_ball_users WHERE user_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            throw new Exception("User not found");
        }

        if ($user['role'] === 'admin') {
            throw new Exception("Cannot delete admin users");
        }

        // Delete related records first
        // Delete from players table if exists
        $query = "DELETE FROM v_ball_players WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // Delete from fan_follows if exists
        $query = "DELETE FROM v_ball_fan_follows WHERE fan_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // Delete from notification_recipients if exists
        $query = "DELETE FROM v_ball_notification_recipients WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // Update teams to remove coach if exists
        $query = "UPDATE v_ball_teams SET coach_id = NULL WHERE coach_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // Finally, delete the user
        $query = "DELETE FROM v_ball_users WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // Log the activity
        require_once 'log_activity.php';
        logActivity($conn, $_SESSION['user_id'], 'USER_DELETION', 
                   "Deleted user ID: $userId");

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

$conn->close(); 