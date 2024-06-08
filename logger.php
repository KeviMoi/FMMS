<?php
function logActivity($message, $status, $user = null) {
    // Define the log file path
    $logFile = __DIR__ . '/log_file/activity.log';

    // Get the current timestamp
    $timestamp = date('Y-m-d H:i:s');

    // Get the IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    // Format the log message
    $logMessage = "[$timestamp] [$status] [$ipAddress]";
    if ($user) {
        $logMessage .= " [User: $user]";
    }
    $logMessage .= " $message" . PHP_EOL;

    // Write the log message to the file
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
?>
