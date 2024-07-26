<?php
include_once 'db_config/db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_center_id = $_POST['service_center_id'];
    $changes = json_decode($_POST['changes'], true);

    $set_clause = [];
    $params = [];
    $types = "";

    if (isset($changes['service_center_name'])) {
        $set_clause[] = "service_center_name = ?";
        $params[] = $changes['service_center_name'];
        $types .= "s";
    }
    if (isset($changes['task_id'])) {
        $set_clause[] = "task_id = ?";
        $params[] = $changes['task_id'];
        $types .= "i";
    }

    if (!empty($set_clause)) {
        $query = "UPDATE service_centers SET " . implode(", ", $set_clause) . " WHERE service_center_id = ?";
        $params[] = $service_center_id;
        $types .= "i";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            echo "Service center updated successfully.";
        } else {
            echo "Error: " . $query . "<br>" . $conn->error;
        }

        $stmt->close();
    }

    $conn->close();
}
?>
