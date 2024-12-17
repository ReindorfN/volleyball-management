<?php
session_start();
require_once '../db/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $requiredFields = ['firstName', 'lastName', 'email', 'password', 'role'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode(['success' => false, 'error' => "Missing required field: $field"]);
            exit;
        }
    }

    // Validate role (can't create players directly)
    if ($data['role'] === 'player') {
        echo json_encode(['success' => false, 'error' => "Players must be added by coaches"]);
        exit;
    }

    try {
        $conn->begin_transaction();

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $query = "INSERT INTO v_ball_users 
                 (first_name, last_name, email, password_hash, role, created_by) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", 
            $data['firstName'],
            $data['lastName'],
            $data['email'],
            $hashedPassword,
            $data['role'],
            $_SESSION['user_id']
        );
        
        $stmt->execute();
        $userId = $conn->insert_id;

        require_once 'log_activity.php';
        logActivity($conn, $_SESSION['user_id'], 'USER_CREATION', 
                   "Added new {$data['role']}: {$data['firstName']} {$data['lastName']}");

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => "Successfully added new {$data['role']}",
            'userId' => $userId
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

$conn->close(); 