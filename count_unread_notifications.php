<?php
session_start();
require_once 'db_config/db_conn.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Prepare the query to count unread notifications
$query = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare statement']);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    echo json_encode(['error' => 'Failed to execute query']);
    exit;
}

$row = $result->fetch_assoc();
$unread_count = $row['unread_count'];

echo json_encode(['unread_count' => $unread_count]);

$stmt->close();
$conn->close();
?>
