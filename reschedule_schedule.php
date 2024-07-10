<?php
require_once 'db_config/db_conn.php';

$schedule_id = $_POST['schedule_id'];
$service_center_id = $_POST['service_center_id'];
$schedule_date = $_POST['schedule_date'];
$schedule_start_time = $_POST['schedule_start_time'];
$schedule_end_time = $_POST['schedule_end_time'];

$query = "UPDATE maintenance_schedule SET service_center_id = ?, schedule_date = ?, schedule_start_time = ?, schedule_end_time = ? WHERE schedule_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('isssi', $service_center_id, $schedule_date, $schedule_start_time, $schedule_end_time, $schedule_id);

if ($stmt->execute()) {
    echo "Maintenance task rescheduled successfully.";
} else {
    echo "Error rescheduling maintenance task: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
