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
    
    try {
        $conn->begin_transaction();

        // Check for unique team name
        $checkQuery = "SELECT team_id FROM v_ball_teams WHERE team_name = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $data['teamName']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Team name already exists");
        }

        // Check if coach is already assigned
        $checkCoachQuery = "SELECT team_id FROM v_ball_teams WHERE coach_id = ?";
        $stmt = $conn->prepare($checkCoachQuery);
        $stmt->bind_param("i", $data['coachId']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Selected coach is already assigned to a team");
        }

        // Insert new team
        $query = "INSERT INTO v_ball_teams (team_name, coach_id) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $data['teamName'], $data['coachId']);
        $stmt->execute();

        require_once 'log_activity.php';
        logActivity($conn, $_SESSION['user_id'], 'TEAM_CREATION', 
                   "Added new team: {$data['teamName']}");

        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Team added successfully'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

$conn->close(); 