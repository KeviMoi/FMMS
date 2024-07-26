<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'db_config/db_conn.php';

$assignment_id = $_POST['assignment_id'];

if (isset($assignment_id)) {
    $delete_query = "DELETE FROM service_center_mechanics WHERE service_center_mechanic_id = '$assignment_id'";

    if (mysqli_query($conn, $delete_query)) {
        echo json_encode(['status' => 'success', 'message' => 'Assignment deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete assignment.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid assignment ID.']);
}
?>
