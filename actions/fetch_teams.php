<?php
// Import database connection
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

header('Content-Type: application/json');

try {
    $query = "SELECT team_id, team_name FROM v_ball_teams";
    $result = $conn->query($query);

    $teams = [];
    while ($row = $result->fetch_assoc()) {
        $teams[] = $row;
    }

    echo json_encode($teams);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
