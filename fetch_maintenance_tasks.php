<?php
require 'db_config/db_conn.php';

$sql = "SELECT task_id, task_name FROM maintenance_tasks";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['task_name'] . "'>" . $row['task_name'] . "</option>";
    }
} else {
    echo "<option value=''>No tasks available</option>";
}

$conn->close();
?>
