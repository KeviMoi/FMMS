<?php
include_once 'db_config/db_conn.php';
session_start();

if (!isset($_POST['schedule_id'])) {
    die('Schedule ID not provided.');
}

$schedule_id = $_POST['schedule_id'];

// Update the schedule status to 'in progress' and the vehicle status to 'in service'
$update_schedule_query = "UPDATE maintenance_schedule SET status = 'in progress' WHERE schedule_id = $schedule_id";
$update_vehicle_query = "UPDATE vehicles SET status = 'in service' WHERE vehicle_id = (SELECT vehicle_id FROM maintenance_schedule WHERE schedule_id = $schedule_id)";

if (mysqli_query($conn, $update_schedule_query) && mysqli_query($conn, $update_vehicle_query)) {
    echo 'Success';
} else {
    echo 'Error: ' . mysqli_error($conn);
}

mysqli_close($conn);
?>
