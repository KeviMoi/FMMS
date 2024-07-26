<?php
include_once 'db_config/db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_center_name = $_POST['service_center_name'];
    $task_id = $_POST['task_id'];

    $query = "INSERT INTO service_centers (service_center_name, task_id, date_created) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $service_center_name, $task_id);

    if ($stmt->execute()) {
        echo "Service center added successfully.";
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
