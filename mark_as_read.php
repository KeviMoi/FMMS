<?php
require_once 'db_config/db_conn.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['notification_id'])) {
    $notification_id = $data['notification_id'];

    $query = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Failed to prepare statement"]);
        exit;
    }

    $stmt->bind_param("i", $notification_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "No rows affected"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Notification ID not set"]);
}
?>
