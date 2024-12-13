<?php
// Import database connection
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

session_start();

// Check if the user is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../views/login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $tournamentName = trim($_POST['tournament_name']);
    $startDate = trim($_POST['start_date']);
    $endDate = trim($_POST['end_date']);

    // Validate input
    if (empty($tournamentName) || empty($startDate) || empty($endDate)) {
        echo "All fields are required.";
        exit();
    }

    try {
        // Insert tournament into the database
        $query = "INSERT INTO v_ball_tournaments (tournament_name, start_date, end_date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sss", $tournamentName, $startDate, $endDate);
        $stmt->execute();

        // Redirect back to the organizer dashboard with success message
        header("Location: ../views/organizer_view.php?message=Tournament+added+successfully");
        exit();
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage();
    }
}

$conn->close();
?>
