<?php
// Include the database connection file
include_once 'db_config/db_conn.php';

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
        } else {
            echo "Error updating user";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "No changes detected or invalid user ID.";
    }
}

mysqli_close($conn);
?>
