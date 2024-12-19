<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$coachId = $_SESSION['user_id'];

try {
    // Get coach profile information
    $query = "SELECT 
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                GROUP_CONCAT(t.team_name) as teams
              FROM v_ball_users u
              LEFT JOIN v_ball_teams t ON t.coach_id = u.user_id
              WHERE u.user_id = ?
              GROUP BY u.user_id";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $coachId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();

    if ($profile) {
        $profile['teams'] = $profile['teams'] ? explode(',', $profile['teams']) : [];
        echo json_encode([
            'success' => true,
            'profile' => $profile
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Profile not found'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch profile'
    ]);
}

$conn->close(); 