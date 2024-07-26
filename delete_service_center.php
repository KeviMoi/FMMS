<?php
include_once 'db_config/db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_center_id = $_POST['service_center_id'];

    $query = "DELETE FROM service_centers WHERE service_center_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $service_center_id);

    if ($stmt->execute()) {
        echo "Service center deleted successfully.";
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
