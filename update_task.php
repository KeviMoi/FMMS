<?php
include_once 'db_config/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $task_id = intval($_POST['task_id']);
  $changes = json_decode($_POST['changes'], true);

  if (isset($changes['task_name'])) {
    $task_name = trim($changes['task_name']);
    if (strlen($task_name) < 2 || strlen($task_name) > 255) {
      http_response_code(400);
      echo 'Invalid Task Name';
      exit;
    }
  }

  if (isset($changes['estimated_time'])) {
    $estimated_time = intval(trim($changes['estimated_time']));
    if ($estimated_time <= 0 || !is_int($estimated_time)) {
      http_response_code(400);
      echo 'Invalid Estimated Time';
      exit;
    }
  }

  if (isset($changes['additional_details'])) {
    $additional_details = trim($changes['additional_details']);
    if (strlen($additional_details) > 1000) {
      http_response_code(400);
      echo 'Invalid Additional Details';
      exit;
    }
  }

  $query = "UPDATE maintenance_tasks SET ";
  $params = [];
  $types = "";

  foreach ($changes as $key => $value) {
    $query .= "$key = ?, ";
    $params[] = $value;
    $types .= is_int($value) ? "i" : "s";
  }

  $query = rtrim($query, ", ");
  $query .= " WHERE task_id = ?";
  $params[] = $task_id;
  $types .= "i";

  $stmt = mysqli_prepare($conn, $query);
  mysqli_stmt_bind_param($stmt, $types, ...$params);

  if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    echo 'Task Updated Successfully';
  } else {
    http_response_code(500);
    echo 'Failed to Update Task';
  }

  mysqli_stmt_close($stmt);
  mysqli_close($conn);
}
?>
