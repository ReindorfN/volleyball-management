<?php
require "../db/db_connection.php";

session_start();
header('Content-Type: application/json');

// Ensure the user is logged in and is a player
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$playerId = $_SESSION['user_id'];
$database = new Database();
$conn = $database->getConnection();

try {
    // Fetch announcements for the player's team
    $query = "
        SELECT a.announcement_text, a.created_at
        FROM v_ball_announcements AS a
        JOIN v_ball_players AS p ON a.team_id = p.team_id
        WHERE p.player_id = ?
        ORDER BY a.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $playerId);
    $stmt->execute();
    $result = $stmt->get_result();

    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }

    echo json_encode($announcements);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
