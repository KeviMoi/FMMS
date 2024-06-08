<?php
session_start();

// Include the database connection file
include_once 'db_config/db_conn.php';
include_once 'logger.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $changes = json_decode($_POST['changes'], true);

    if (!empty($changes) && $user_id) {
        $update_fields = [];
        $update_values = [];

        foreach ($changes as $field => $value) {
            $update_fields[] = "$field = ?";
            $update_values[] = mysqli_real_escape_string($conn, $value);
        }

        // Dynamically create the SQL query
        $query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);

        // Dynamically create the parameter types
        $types = str_repeat('s', count($update_values)) . 'i';
        $update_values[] = $user_id;

        // Bind the parameters
        mysqli_stmt_bind_param($stmt, $types, ...$update_values);

        if (mysqli_stmt_execute($stmt)) {
            echo "User updated successfully";
            logActivity("User's details with ID: $user_id updated successfully.", "SUCCESS", $_SESSION['username']);
        } else {
            echo "Error updating user";
            logActivity("Failed to update user's details with ID: $user_id.", "ERROR", $_SESSION['username']);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "No changes detected or invalid user ID.";
        logActivity("No changes detected or invalid user ID for user with ID: $user_id.", "ERROR", $_SESSION['username']);
    }
}

mysqli_close($conn);
