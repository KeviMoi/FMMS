<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'db_config/db_conn.php';

$assignment_id = $_POST['assignment_id'];  // Corrected the key
$changes = json_decode($_POST['changes'], true);

if (isset($changes['mechanic_id'])) {
    $mechanic_id = $changes['mechanic_id'];

    // Check if the mechanic is already assigned
    $check_query = "SELECT COUNT(*) as count FROM service_center_mechanics WHERE mechanic_id = '$mechanic_id' AND service_center_mechanic_id != '$assignment_id'";
    $check_result = mysqli_query($conn, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);

    if ($check_row['count'] > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Mechanic is already assigned to another service center.']);
        exit;
    }
}

$set_clauses = [];
foreach ($changes as $column => $value) {
    $set_clauses[] = "$column = '$value'";
}
$set_clause = implode(', ', $set_clauses);

$update_query = "UPDATE service_center_mechanics SET $set_clause WHERE service_center_mechanic_id = '$assignment_id'";
if (mysqli_query($conn, $update_query)) {
    echo json_encode(['status' => 'success', 'message' => 'Assignment updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update assignment.']);
}
?>
