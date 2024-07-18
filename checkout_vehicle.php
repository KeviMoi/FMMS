<?php
include_once 'db_config/db_conn.php';
require_once 'logger.php';
require_once 'notification.php';

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
    $query = "SELECT vehicle_id, (SELECT assigned_driver_id FROM vehicles WHERE vehicle_id = maintenance_schedule.vehicle_id) AS driver_id 
              FROM maintenance_schedule 
              WHERE schedule_id = $schedule_id";
    $result = mysqli_query($conn, $query);
    if (!$result || mysqli_num_rows($result) === 0) {
        die('Schedule not found.');
    }
    $row = mysqli_fetch_assoc($result);
    $vehicle_id = $row['vehicle_id'];
    $driver_id = $row['driver_id'];

    // Update the maintenance schedule status
    $update_schedule = "UPDATE maintenance_schedule SET status = 'completed' WHERE schedule_id = $schedule_id";
    if (!mysqli_query($conn, $update_schedule)) {
        $error_message = 'Failed to update schedule.';
        echo $error_message;
        logActivity($error_message, 'ERROR', $_SESSION['username'] ?? 'Unknown');
        die();
    }

    // Update the vehicle status
    $update_vehicle = "UPDATE vehicles SET status = 'active' WHERE vehicle_id = $vehicle_id";
    if (!mysqli_query($conn, $update_vehicle)) {
        $error_message = 'Failed to update vehicle.';
        echo $error_message;
        logActivity($error_message, 'ERROR', $_SESSION['username'] ?? 'Unknown');
        die();
    }

    // Insert into service_history
    $insert_history = "INSERT INTO service_history (vehicle_id, task_id, service_date, mechanic_id, service_details, odometer_reading)
                       VALUES ($vehicle_id, $task_id, '$current_date', $mechanic_id, '$service_details', $odometer_reading)";
    if (!mysqli_query($conn, $insert_history)) {
        $error_message = 'Failed to insert into service history.';
        echo $error_message;
        logActivity($error_message, 'ERROR', $_SESSION['username'] ?? 'Unknown');
        die();
    }

    $success_message = "Vehicle with schedule ID $schedule_id checked out successfully.";
    echo $success_message;
    logActivity($success_message, 'SUCCESS', $_SESSION['username'] ?? 'Unknown');
    
    // Send notification to the driver
    if ($driver_id) {
        notify($driver_id, $success_message);
    } else {
        logActivity("No driver assigned for the vehicle with schedule ID $schedule_id.", 'WARNING', $_SESSION['username'] ?? 'Unknown');
    }
} else {
    echo "Invalid request.";
    logActivity("Invalid request method used for vehicle checkout.", 'ERROR', $_SESSION['username'] ?? 'Unknown');
}

mysqli_close($conn);
?>
