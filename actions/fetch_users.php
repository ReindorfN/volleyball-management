<?php
// Database connection
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

// For debugging and error display purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Query to fetch user data
    $query = "SELECT first_name, last_name, username, email, role, team_name
              FROM v_ball_users 
              LEFT JOIN v_ball_teams ON v_ball_users.user_id = v_ball_teams.coach_id";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->execute();

    // Fetch result set if mysqlnd is enabled
    $result = $stmt->get_result();
    if ($result === false) {
        die("Error getting result set: " . $conn->error);
    }

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    // Return user data as JSON
    echo json_encode($users);

} catch (Exception $e) {
    echo json_encode(["error" => "Failed to fetch users: " . $e->getMessage()]);
}

$conn->close();
?>
