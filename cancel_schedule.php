<?php
include_once 'db_config/db_conn.php';

if (isset($_GET['schedule_id'])) {
    $schedule_id = $_GET['schedule_id'];

    // Query to cancel the schedule
    $query = "UPDATE maintenance_schedule SET status = 'cancelled' WHERE schedule_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $schedule_id);
        if ($stmt->execute()) {
            header("Location: driver_dashboard.php");
        } else {
            echo "Error cancelling appointment";
        }
    } else {
        echo "Error preparing statement";
    }

    $stmt->close();
}
?>
