<?php
include_once 'db_config/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $task_id = intval($_POST['task_id']);

  $query = "DELETE FROM maintenance_tasks WHERE task_id = ?";
  $stmt = mysqli_prepare($conn, $query);
  mysqli_stmt_bind_param($stmt, 'i', $task_id);

  if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    echo 'Task Deleted Successfully';
  } else {
    http_response_code(500);
    echo 'Failed to Delete Task';
  }

  mysqli_stmt_close($stmt);
  mysqli_close($conn);
}
?>
