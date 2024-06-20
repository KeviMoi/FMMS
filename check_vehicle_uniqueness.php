<?php
include_once 'db_config/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $field = $_POST['field'];
    $value = mysqli_real_escape_string($conn, $_POST['value']);
    $vehicle_id = $_POST['vehicle_id'];

    if ($field === 'assigned_driver_id' && !empty($value)) {
        $query = "SELECT COUNT(*) AS count FROM vehicles WHERE assigned_driver_id = '$value' AND vehicle_id != '$vehicle_id'";
    } else {
        $query = "SELECT COUNT(*) AS count FROM vehicles WHERE $field = '$value' AND vehicle_id != '$vehicle_id'";
    }

    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    $is_unique = $row['count'] == 0;
    echo json_encode(['unique' => $is_unique]);
}
?>
