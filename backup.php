
<?php // book_maintenance.php
session_start();
require_once 'db_config/db_conn.php';
require_once 'notification.php';
require_once 'logger.php';

if (!isset($_SESSION['vehicle_id'])) {
    die("Vehicle ID not set in session.");
}

$vehicle_id = $_SESSION['vehicle_id'];

$date = $_POST['date'];
$task = $_POST['maintenance_task'];
$additional_info = $_POST['additional_info'];
$service_center_id = $_POST['service_center_id'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];

// Fetch the task_id
$task_id_query = "SELECT task_id FROM maintenance_tasks WHERE task_name = ?";
$stmt = $conn->prepare($task_id_query);
$stmt->bind_param('s', $task);
$stmt->execute();
$result = $stmt->get_result();
$task_id = $result->fetch_assoc()['task_id'];

// Query to book maintenance
$query = "INSERT INTO maintenance_schedule (vehicle_id, task_id, service_center_id, schedule_date, schedule_start_time, schedule_end_time, additional_info, status)
          VALUES (?, ?, ?, ?, ?, ?, ?, 'Scheduled')";

$stmt = $conn->prepare($query);
$stmt->bind_param('iiissss', $vehicle_id, $task_id, $service_center_id, $date, $start_time, $end_time, $additional_info);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $notification = "Maintenance for  your vehicle  has been successfully booked. Task: $task. Date: $date. Time: $start_time - $end_time.";
    notify($_SESSION['user_id'], $notification);
    
    $message = "Maintenance schedule booked successfully for vehicle ID: $vehicle_id, task: $task, date: $date.";
    logActivity($message, 'SUCCESS', $_SESSION['username'] ?? 'Unknown');
} else {
    echo "Failed to book maintenance schedule.";
    $message = "Failed to book maintenance schedule for vehicle ID: $vehicle_id, task: $task, date: $date.";
    logActivity($message, 'ERROR', $_SESSION['username'] ?? 'Unknown');
}
