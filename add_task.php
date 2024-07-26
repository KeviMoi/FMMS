<?php
include_once 'db_config/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $task_name = trim($_POST['task_name']);
  $estimated_time = intval(trim($_POST['estimated_time']));
  $additional_details = trim($_POST['additional_details']);

  // Validate inputs
  if (strlen($task_name) < 2 || strlen($task_name) > 255) {
    http_response_code(400);
    echo 'Invalid Task Name';
    exit;
  }
  if ($estimated_time <= 0 || !is_int($estimated_time)) {
    http_response_code(400);
    echo 'Invalid Estimated Time';
    exit;
  }
  if (strlen($additional_details) > 1000) {
    http_response_code(400);
    echo 'Invalid Additional Details';
    exit;
  }

  $query = "INSERT INTO maintenance_tasks (task_name, estimated_time, additional_details, date_created) VALUES (?, ?, ?, NOW())";
  $stmt = mysqli_prepare($conn, $query);
  mysqli_stmt_bind_param($stmt, 'sis', $task_name, $estimated_time, $additional_details);

  if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    echo 'Task Added Successfully';
  } else {
    http_response_code(500);
    echo 'Failed to Add Task';
  }
  mysqli_stmt_close($stmt);
  mysqli_close($conn);
}
?>
