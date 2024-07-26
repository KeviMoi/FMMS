<?php
include_once 'db_config/db_conn.php';

$service_center_id = $_POST['service_center_id'];
$mechanic_id = $_POST['mechanic_id'];

// Check if the mechanic is already assigned
$check_query = "SELECT COUNT(*) as count FROM service_center_mechanics WHERE mechanic_id = '$mechanic_id'";
$check_result = mysqli_query($conn, $check_query);
$check_row = mysqli_fetch_assoc($check_result);

if ($check_row['count'] > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Mechanic is already assigned to another service center.']);
} else {
    $insert_query = "INSERT INTO service_center_mechanics (service_center_id, mechanic_id, date_assigned) VALUES ('$service_center_id', '$mechanic_id', NOW())";
    if (mysqli_query($conn, $insert_query)) {
        echo json_encode(['status' => 'success', 'message' => 'Assignment added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add assignment.']);
    }
}
?>
