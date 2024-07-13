<?php
require_once 'db_config/db_conn.php';

function notify($user_id, $message)
{
    global $conn;

    $notification_message = mysqli_real_escape_string($conn, $message);

    $query = "INSERT INTO notifications (user_id, notification_message) 
              VALUES ('$user_id', '$notification_message')";

    if (!mysqli_query($conn, $query)) {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}
