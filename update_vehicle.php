<?php
session_start();

include_once 'db_config/db_conn.php';
include_once 'logger.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehicle_id = $_POST['vehicle_id'];
    $license_plate = mysqli_real_escape_string($conn, $_POST['license_plate']);
    $make = mysqli_real_escape_string($conn, $_POST['make']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $vin = mysqli_real_escape_string($conn, $_POST['vin']);
    $mileage = mysqli_real_escape_string($conn, $_POST['mileage']);
    $fuel_type = mysqli_real_escape_string($conn, $_POST['fuel_type']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $assigned_driver_id = mysqli_real_escape_string($conn, $_POST['assigned_driver_id']);

    $query = "UPDATE vehicles 
              SET license_plate = '$license_plate', make = '$make', model = '$model', year = '$year', 
                  vin = '$vin', mileage = '$mileage', fuel_type = '$fuel_type', status = '$status', 
                  assigned_driver_id = '$assigned_driver_id'
              WHERE vehicle_id = '$vehicle_id'";

    if (mysqli_query($conn, $query)) {
        echo "Vehicle details updated successfully.";
        logActivity("Vehicle details with vehicle ID: $vehicle_id updated successfully.", "SUCCESS", $_SESSION['username']);
    } else {
        echo "Error: " . mysqli_error($conn);
        logActivity("Failed to update vehicle details with vehicle ID: $vehicle_id.", "ERROR", $_SESSION['username']);
    }
}
?>
