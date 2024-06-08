<?php
session_start();
// Include the database connection file
include_once 'db_config/db_conn.php';
include_once 'logger.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);

    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "User deleted successfully";
        // Log successful deletion
        logActivity("User with ID: $user_id deleted successfully", "SUCCESS", $_SESSION['username']);
    } else {
        echo "Error deleting user";
        // Log failure to delete user
        logActivity("Failed to delete user with ID: $user_id", "ERROR", $_SESSION['username']);
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
