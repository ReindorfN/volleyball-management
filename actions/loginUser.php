<?php
// Import Database connection
require "../db/db_connection.php";

$database = new Database();
$conn = $database->getConnection();

// For debugging and error display purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve inputs
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        echo "Email and password are required.";
        exit();
    }

    try {
        // Fetch user details
        $query = "SELECT user_id, email, password_hash, role FROM v_ball_users WHERE email = ?";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'fan':
                        header("Location: ../views/fan_view.html");
                        break;
                    case 'admin':
                        header("Location: ../views/admin_view.html");
                        break;
                    case 'player':
                        header("Location: ../views/player_view.html");
                        break;
                    case 'coach':
                        header("Location: ../views/coach_view.html");
                        break;
                    case 'organizer':
                        header("Location: ../views/organizer_view.html");
                        break;
                    default:
                        echo "Invalid role detected.";
                        break;
                }
                exit();
            } else {
                echo "Invalid email or password.";
            }
        } else {
            echo "Invalid email or password.";
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage();
    }
}

$conn->close();
?>
