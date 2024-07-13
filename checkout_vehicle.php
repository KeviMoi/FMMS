<?php
include_once 'db_config/db_conn.php';

// Start the session
session_start();

if (!isset($_SESSION['user_id'])) {
    die('User ID not found in session.');
}

$mechanic_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_id = $_POST['schedule_id'];
    $task_id = $_POST['task_id'];
    $odometer_reading = $_POST['odometer_reading'];
    $service_details = $_POST['service_details'];
    $current_date = date('Y-m-d');

    // Validate inputs
    if (!is_numeric($odometer_reading) || empty($service_details)) {
        die('Invalid input.');
    }

    // Fetch necessary details
    $query = "SELECT vehicle_id FROM maintenance_schedule WHERE schedule_id = $schedule_id";
    $result = mysqli_query($conn, $query);
    if (!$result || mysqli_num_rows($result) === 0) {
        die('Schedule not found.');
    }
    $row = mysqli_fetch_assoc($result);
    $vehicle_id = $row['vehicle_id'];

    // Update the maintenance schedule status
    $update_schedule = "UPDATE maintenance_schedule SET status = 'completed' WHERE schedule_id = $schedule_id";
    if (!mysqli_query($conn, $update_schedule)) {
        die('Failed to update schedule.');
    }

    // Update the vehicle status
    $update_vehicle = "UPDATE vehicles SET status = 'active' WHERE vehicle_id = $vehicle_id";
    if (!mysqli_query($conn, $update_vehicle)) {
        die('Failed to update vehicle.');
    }

    // Insert into service_history
    $insert_history = "INSERT INTO service_history (vehicle_id, task_id, service_date, mechanic_id, service_details, odometer_reading)
                       VALUES ($vehicle_id, $task_id, '$current_date', $mechanic_id, '$service_details', $odometer_reading)";
    if (!mysqli_query($conn, $insert_history)) {
        die('Failed to insert into service history.');
    }

    echo "Vehicle checked out successfully.";
} else {
    echo "Invalid request.";
}
?>
