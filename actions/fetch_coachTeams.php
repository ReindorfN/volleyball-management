<?php
// Import database connection
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

session_start();

header('Content-Type: application/json');

// Check if the user is logged in and is a coach
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $coachId = $_SESSION['user_id'];

    // Fetch teams assigned to this coach
    $query = "SELECT team_id, team_name FROM v_ball_teams WHERE coach_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $coachId);
    $stmt->execute();
    $result = $stmt->get_result();

    $teams = [];
    while ($row = $result->fetch_assoc()) {
        $teams[] = $row;
    }

    // Return the teams as a JSON response
    echo json_encode($teams);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
