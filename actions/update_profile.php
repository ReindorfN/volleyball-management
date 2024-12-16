<?php
session_start();
require_once '../db/db_connection.php';

$database = new Database();
$conn = $database->getConnection();

$response = array();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['success'] = false;
    $response['error'] = 'Not authenticated';
    echo json_encode($response);
    exit;
}

// Validate and sanitize input
$userId = $_SESSION['user_id'];
$firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
$lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$jerseyNumber = filter_input(INPUT_POST, 'jersey_number', FILTER_VALIDATE_INT);
$position = filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING);
$newPassword = $_POST['new_password'] ?? null;

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['success'] = false;
    $response['error'] = 'Invalid email format';
    echo json_encode($response);
    exit;
}

// Validate position
$validPositions = ['spiker', 'blocker', 'setter', 'libero', 'server'];
if (!in_array($position, $validPositions)) {
    $response['success'] = false;
    $response['error'] = 'Invalid position';
    echo json_encode($response);
    exit;
}

try {
    $conn->begin_transaction();

    // Handle profile picture upload
    $profilePicPath = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        $file = $_FILES['profile_pic'];
        
        // Validate file type and size
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG and GIF allowed.');
        }
        if ($file['size'] > $maxSize) {
            throw new Exception('File too large. Maximum size is 5MB.');
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_') . '.' . $ext;
        $uploadDir = '../uploads/profiles/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadPath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $profilePicPath = 'uploads/profiles/' . $filename;
            
            // Delete old profile picture if exists
            $stmt = $conn->prepare("SELECT profile_pic FROM v_ball_users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($oldPic = $result->fetch_assoc()) {
                if ($oldPic['profile_pic'] && file_exists('../' . $oldPic['profile_pic'])) {
                    unlink('../' . $oldPic['profile_pic']);
                }
            }
        }
    }

    // Update user information
    $updateUserQuery = "UPDATE v_ball_users SET 
                       first_name = ?, 
                       last_name = ?, 
                       email = ?";
    $params = [$firstName, $lastName, $email];
    $types = "sss";

    if ($profilePicPath) {
        $updateUserQuery .= ", profile_pic = ?";
        $params[] = $profilePicPath;
        $types .= "s";
    }

    if ($newPassword) {
        $updateUserQuery .= ", password_hash = ?";
        $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
        $types .= "s";
    }

    $updateUserQuery .= " WHERE user_id = ?";
    $params[] = $userId;
    $types .= "i";

    $stmt = $conn->prepare($updateUserQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    // Update player information
    $updatePlayerQuery = "UPDATE v_ball_players SET 
                         position = ?, 
                         jersey_number = ? 
                         WHERE user_id = ?";
    $stmt = $conn->prepare($updatePlayerQuery);
    $stmt->bind_param("sii", $position, $jerseyNumber, $userId);
    $stmt->execute();

    $conn->commit();

    $response['success'] = true;
    $response['message'] = 'Profile updated successfully';
    if ($profilePicPath) {
        $response['profile_pic'] = $profilePicPath;
    }

} catch (Exception $e) {
    $conn->rollback();
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
