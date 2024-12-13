<?php
// Import database connection
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

// Start session
session_start();

// For debugging and error display purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle POST request to add a player
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $firstName = trim($_POST['fname']);
    $lastName = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $position = trim($_POST['position']);
    $jerseyNumber = intval($_POST['jersey_number']);
    $teamId = intval($_POST['team_id']);
    $createdBy = $_SESSION['user_id']; // Coach's user ID

    try {
        // Insert into v_ball_users
        $query = "INSERT INTO v_ball_users (first_name, last_name, email, password_hash, role, created_by) 
                  VALUES (?, ?, ?, ?, 'player', ?)";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssssi", $firstName, $lastName, $email, $password, $createdBy);
        $stmt->execute();

        // Get the user ID of the inserted player
        $playerId = $stmt->insert_id;

        // Insert into v_ball_players
        $query = "INSERT INTO v_ball_players (user_id, team_id, position, jersey_number) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iisi", $playerId, $teamId, $position, $jerseyNumber);
        $stmt->execute();

        // Redirect to the coach dashboard with success message
        header("Location: ../views/coach_view.php?message=Player+added+successfully");
        exit();
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage();
    }
}

$conn->close();
?>
