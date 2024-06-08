<?php
session_start();

// Include the logger function
include 'logger.php';

// Log the logout activity if the user is logged in
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    logActivity("User logged out successfully.", 'SUCCESS', $username);
} else {
    logActivity("Unknown user logged out.", 'INFO');
}

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: index.php");
exit;
?>
