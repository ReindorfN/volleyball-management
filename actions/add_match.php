<?php
// Import database connection
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

session_start();

// Check if the user is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $team1Id = intval($_POST['team1_id']);
    $team2Id = intval($_POST['team2_id']);
    $matchDate = trim($_POST['match_date']);
    $venue = trim($_POST['venue']);

    // Validate input
    if ($team1Id === $team2Id) {
        echo "Error: Team 1 and Team 2 cannot be the same.";
        exit();
    }
    if (empty($team1Id) || empty($team2Id) || empty($matchDate) || empty($venue)) {
        echo "All fields are required.";
        exit();
    }

    try {
        // Insert match into the database
        $query = "INSERT INTO v_ball_matches (team1_id, team2_id, match_date, venue) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iiss", $team1Id, $team2Id, $matchDate, $venue);
        $stmt->execute();

        // Redirect back to the organizer dashboard with success message
        header("Location: ../views/organizer_view.php?message=Match+added+successfully");
        exit();
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage();
    }
}

$conn->close();
?>
