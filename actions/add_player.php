<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Validate required fields
$required_fields = ['team', 'first_name', 'last_name', 'email', 'position', 'jersey_number', 'password'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
        exit;
    }
}

$database = new Database();
$conn = $database->getConnection();
$coach_id = $_SESSION['user_id'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Verify coach has permission for this team
    $verify_query = "SELECT COUNT(*) as count 
                    FROM v_ball_teams 
                    WHERE team_id = ? AND coach_id = ?";
    
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $_POST['team'], $coach_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] == 0) {
        throw new Exception('Unauthorized team access');
    }

    // Check if jersey number is already taken in the team
    $jersey_check = "SELECT COUNT(*) as count 
                    FROM v_ball_players 
                    WHERE team_id = ? AND jersey_number = ?";
    
    $stmt = $conn->prepare($jersey_check);
    $stmt->bind_param("ii", $_POST['team'], $_POST['jersey_number']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        throw new Exception('Jersey number already taken in this team');
    }

    // Check if email already exists
    $email_check = "SELECT COUNT(*) as count FROM v_ball_users WHERE email = ?";
    $stmt = $conn->prepare($email_check);
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        throw new Exception('Email already registered');
    }

    // Create user account with provided password
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_query = "INSERT INTO v_ball_users (first_name, last_name, email, password_hash, role, created_by) 
                   VALUES (?, ?, ?, ?, 'player', ?)";
    
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("ssssi", 
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $password_hash,
        $coach_id
    );
    $stmt->execute();
    $user_id = $stmt->insert_id;

    // Add player record
    $player_query = "INSERT INTO v_ball_players (user_id, team_id, position, jersey_number) 
                     VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($player_query);
    $stmt->bind_param("iisi", 
        $user_id,
        $_POST['team'],
        $_POST['position'],
        $_POST['jersey_number']
    );
    $stmt->execute();

    // Log activity
    $activity_query = "INSERT INTO v_ball_activity_log (user_id, activity_type, description) 
                      VALUES (?, 'PLAYER_CREATION', ?)";
    
    $description = "Added new player: " . $_POST['first_name'] . " " . $_POST['last_name'];
    $stmt = $conn->prepare($activity_query);
    $stmt->bind_param("is", $coach_id, $description);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Player added successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
