<?php
session_start();

include_once 'db_config/db_conn.php';
include_once 'logger.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehicle_id = $_POST['vehicle_id'];

    $query = "DELETE FROM vehicles WHERE vehicle_id = '$vehicle_id'";

    if (mysqli_query($conn, $query)) {
        echo "Vehicle deleted successfully.";
        logActivity("Vehicle with vehicle ID: $vehicle_id deleted successfully", "SUCCESS", $_SESSION['username']);
    } else {
        echo "Error: " . mysqli_error($conn);
        logActivity("Failed to delete vehicle with vehicle ID: $vehicle_id", "ERROR", $_SESSION['username']);
    }
}
?>
