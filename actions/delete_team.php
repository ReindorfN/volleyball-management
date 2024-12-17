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
    $teamId = $data['teamId'] ?? null;

    if (!$teamId) {
        echo json_encode(['success' => false, 'error' => 'Team ID is required']);
        exit;
    }

    try {
        $conn->begin_transaction();

        // Check if team exists
        $checkQuery = "SELECT team_name FROM v_ball_teams WHERE team_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $teamId);
        $stmt->execute();
        $result = $stmt->get_result();
        $team = $result->fetch_assoc();

        if (!$team) {
            throw new Exception("Team not found");
        }

        // Delete related records first
        // Delete from matches
        $query = "DELETE FROM v_ball_matches WHERE team1_id = ? OR team2_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $teamId, $teamId);
        $stmt->execute();

        // Delete from fan_follows
        $query = "DELETE FROM v_ball_fan_follows WHERE team_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $teamId);
        $stmt->execute();

        // Delete from announcements
        $query = "DELETE FROM v_ball_announcements WHERE team_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $teamId);
        $stmt->execute();

        // Update players to remove team association
        $query = "UPDATE v_ball_players SET team_id = NULL WHERE team_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $teamId);
        $stmt->execute();

        // Finally, delete the team
        $query = "DELETE FROM v_ball_teams WHERE team_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $teamId);
        $stmt->execute();

        // Log the activity
        require_once 'log_activity.php';
        logActivity($conn, $_SESSION['user_id'], 'TEAM_DELETION', 
                   "Deleted team: {$team['team_name']}");

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