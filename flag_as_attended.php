<?php
// Include the database connection file
include_once 'db_config/db_conn.php';

// Start the session
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];

    // Update the status to 'Attended'
    $query = "UPDATE breakdown_requests SET status = 'Attended' WHERE request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $request_id);

    if ($stmt->execute()) {
        echo "Request flagged as attended successfully.";
    } else {
        echo "Failed to flag the request.";
    }

    $stmt->close();
    $conn->close();
}
?>

