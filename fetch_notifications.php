<?php
session_start();
require_once 'db_config/db_conn.php';

$user_id = $_SESSION['user_id'];

// Check if user_id is set
if (!$user_id) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY notification_date DESC";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare statement']);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

if (empty($notifications)) {
    echo json_encode(['message' => 'No notifications found']);
} else {
    echo json_encode($notifications);
}

$stmt->close();
$conn->close();
?>
