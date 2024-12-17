<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'];
    
    // Validate input
    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
        throw new Exception('Required fields cannot be empty');
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Start transaction
    $conn->begin_transaction();

    // Check if email is already in use by another user
    $emailQuery = "SELECT user_id FROM v_ball_users WHERE email = ? AND user_id != ?";
    $stmt = $conn->prepare($emailQuery);
    $stmt->bind_param("si", $data['email'], $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Email is already in use');
    }

    // Update user information
    if (!empty($data['new_password'])) {
        // Update with new password
        $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
        $query = "UPDATE v_ball_users SET 
                  first_name = ?, 
                  last_name = ?, 
                  email = ?,
                  password = ?
                  WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", 
            $data['first_name'], 
            $data['last_name'], 
            $data['email'],
            $hashedPassword,
            $userId
        );
    } else {
        // Update without password
        $query = "UPDATE v_ball_users SET 
                  first_name = ?, 
                  last_name = ?, 
                  email = ?
                  WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", 
            $data['first_name'], 
            $data['last_name'], 
            $data['email'],
            $userId
        );
    }

    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($conn->connect_errno) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close(); 