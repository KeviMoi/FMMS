<?php
require_once 'db_config/db_conn.php';
require_once 'logger.php';

$schedule_id = $_POST['schedule_id'];
$service_center_id = $_POST['service_center_id'];
$task_name = $_POST['task']; // Fetch task name from POST request
$schedule_date = $_POST['schedule_date'];
$schedule_start_time = $_POST['schedule_start_time'];
$schedule_end_time = $_POST['schedule_end_time'];

// Query to get task_id based on task name
$task_query = "SELECT task_id FROM maintenance_tasks WHERE task_name = ?";
$task_stmt = $conn->prepare($task_query);
$task_stmt->bind_param('s', $task_name);
$task_stmt->execute();
$task_result = $task_stmt->get_result();

if ($task_result->num_rows > 0) {
    $task_row = $task_result->fetch_assoc();
    $task_id = $task_row['task_id'];

    // Update maintenance_schedule with the fetched task_id
    $query = "UPDATE maintenance_schedule SET service_center_id = ?, task_id = ?, schedule_date = ?, schedule_start_time = ?, schedule_end_time = ? WHERE schedule_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iisssi', $service_center_id, $task_id, $schedule_date, $schedule_start_time, $schedule_end_time, $schedule_id);

    if ($stmt->execute()) {
        echo "Maintenance task rescheduled successfully.";
        $message = "Maintenance task with schedule ID: $schedule_id rescheduled successfully.";
        logActivity($message, 'SUCCESS', $_SESSION['username'] ?? 'Unknown');
    } else {
        echo "Error rescheduling maintenance task: " . $stmt->error;
        $message = "Error rescheduling maintenance task with schedule ID: $schedule_id. Error: " . $stmt->error;
        logActivity($message, 'ERROR', $_SESSION['username'] ?? 'Unknown');
    }

    $stmt->close();
} else {
    echo "Error: Task not found.";
    $message = "Error: Task name '$task_name' not found.";
    logActivity($message, 'ERROR', $_SESSION['username'] ?? 'Unknown');
}

$task_stmt->close();
$conn->close();
?>
