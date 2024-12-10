<?php
// Import Database connection
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

// For debugging and error display purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = trim($_POST['fname']);
        $lastName = trim($_POST['lname']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'fan'; // Default role for public signups


        // Insert the user query
        $stmt = $conn->prepare("INSERT INTO v_ball_users (first_name, last_name, password_hash, email, role) 
                VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed:" . $conn->error);
        }

        $stmt->bind_param("sssss", $firstName, $lastName, $password, $email, $role);

        // Upon execution of query, redirect to another page
        if ($stmt->execute()) {
            // Redirect to login
            header("Location: ../views/login.html");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close(); // Close query
    }
}
catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// Close database connection
$conn->close();
?>
