<?php
session_start();

include_once 'db_config/db_conn.php';
require_once 'logger.php';
require_once 'notification.php';

if (!isset($_POST['schedule_id'])) {
    die('Schedule ID not provided.');
}

$schedule_id = $_POST['schedule_id'];

// Update the schedule status to 'in progress' and the vehicle status to 'in service'
$update_schedule_query = "UPDATE maintenance_schedule SET status = 'in progress' WHERE schedule_id = $schedule_id";
$update_vehicle_query = "UPDATE vehicles SET status = 'in service' WHERE vehicle_id = (SELECT vehicle_id FROM maintenance_schedule WHERE schedule_id = $schedule_id)";

if (mysqli_query($conn, $update_schedule_query) && mysqli_query($conn, $update_vehicle_query)) {
    echo 'Success';

    // Fetch the driver ID for the vehicle
    $driver_query = "
        SELECT v.assigned_driver_id 
        FROM vehicles v 
        INNER JOIN maintenance_schedule ms 
        ON v.vehicle_id = ms.vehicle_id 
        WHERE ms.schedule_id = $schedule_id";
    
    $driver_result = mysqli_query($conn, $driver_query);
    
    if ($driver_result && mysqli_num_rows($driver_result) > 0) {
        $driver_row = mysqli_fetch_assoc($driver_result);
        $driver_id = $driver_row['assigned_driver_id'];
        
        if ($driver_id) {
            $message = "Maintenance task with schedule ID: $schedule_id started successfully.";
            logActivity($message, 'SUCCESS', $_SESSION['username'] ?? 'Unknown');
            
            // Send notification to the driver
            notify($driver_id, $message);
        } else {
            $message = "No driver assigned for the vehicle associated with schedule ID: $schedule_id.";
            logActivity($message, 'WARNING', $_SESSION['username'] ?? 'Unknown');
        }
    } else {
        $message = "Unable to fetch the driver ID for the vehicle associated with schedule ID: $schedule_id.";
        logActivity($message, 'ERROR', $_SESSION['username'] ?? 'Unknown');
    }

} else {
    $error_message = 'Error: ' . mysqli_error($conn);
    echo $error_message;

    $log_message = "Error starting maintenance task with schedule ID: $schedule_id. Error: " . mysqli_error($conn);
    logActivity($log_message, 'ERROR', $_SESSION['username'] ?? 'Unknown');
}

mysqli_close($conn);
?>
