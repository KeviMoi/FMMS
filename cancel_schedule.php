<?php
include_once 'db_config/db_conn.php';
include_once 'logger.php';

if (isset($_POST['schedule_id'])) {
    $schedule_id = $_POST['schedule_id'];

    // Query to cancel the schedule
    $query = "UPDATE maintenance_schedule SET status = 'Cancelled' WHERE schedule_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $schedule_id);
        if ($stmt->execute()) {
            $message = "Maintenance schedule with ID: $schedule_id cancelled successfully.";
            logActivity($message, 'SUCCESS', $_SESSION['username'] ?? 'Unknown');
            header("Location: driver_dashboard.php");
            exit(); // Ensure script stops executing after redirect
        } else {
            $message = "Error cancelling appointment for schedule ID: $schedule_id. " . $stmt->error;
            logActivity($message, 'ERROR', $_SESSION['username'] ?? 'Unknown');
            echo "Error cancelling appointment: " . $stmt->error;
        }
    } else {
        $message = "Error preparing statement for schedule ID: $schedule_id.";
        logActivity($message, 'ERROR', $_SESSION['username'] ?? 'Unknown');
        echo "Error preparing statement: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Schedule ID not provided.";
}
$conn->close();
?>
