<?php
function logActivity($conn, $userId, $activityType, $description) {
    $query = "INSERT INTO v_ball_activity_log 
              (user_id, activity_type, description) 
              VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $userId, $activityType, $description);
    
    try {
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        return false;
    }
} 