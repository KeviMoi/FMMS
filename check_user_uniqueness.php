<?php
include_once 'db_config/db_conn.php';

$field = $_POST['field'];
$value = $_POST['value'];
$user_id = $_POST['user_id'];

$response = ['unique' => true];

if ($field == 'username') {
    $query = "SELECT COUNT(*) AS count FROM users WHERE username = '$value' AND user_id != $user_id";
} else if ($field == 'email') {
    $query = "SELECT COUNT(*) AS count FROM users WHERE email = '$value' AND user_id != $user_id";
}

$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

if ($row['count'] > 0) {
    $response['unique'] = false;
}

echo json_encode($response);
?>
