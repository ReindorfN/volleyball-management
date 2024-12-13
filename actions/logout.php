<?php
// Start the session
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the landing page
header("Location: ../views/login.html"); // Adjust the path to your landing page
exit();
?>

