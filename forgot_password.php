<?php
require("mail_script.php");
include 'db_config/db_conn.php';
include 'logger.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];
$response = [];

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $stmt = $conn->prepare("SELECT user_id, username, full_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $username, $full_name);
    $stmt->fetch();
    $stmt->close();

    if ($user_id) {
        // Generate a new strong password
        $newPassword = generateStrongPassword();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password and set password_change to false
        $stmt = $conn->prepare("UPDATE users SET password = ?, password_change = false WHERE user_id = ?");
        $stmt->bind_param("si", $hashedPassword, $user_id);

        if ($stmt->execute()) {
            // Send email with new password
            $message = "Dear $full_name,<br>";
            $message .= "Your password has been reset.<br>";
            $message .= "Username: $username<br>";
            $message .= "New Password: $newPassword<br>";
            $message .= "Please change your password after logging in.<br>";
            $message .= "Best regards,<br>";
            $message .= "Makvo Limited Team";

            if (sendMail($email, "Password Reset", $message)) {
                $response = ["success" => true, "message" => "A new password has been sent to your email."];
                logActivity("Password reset for user $username", "SUCCESS", $username);
            } else {
                $response = ["success" => false, "message" => "Failed to send email. Please try again later."];
                logActivity("Failed to send password reset email for user $username", "ERROR", $username);
            }
        } else {
            $response = ["success" => false, "message" => "Failed to reset password. Please try again later."];
            logActivity("Failed to reset password for user $username", "ERROR", $username);
        }

        $stmt->close();
    } else {
        $response = ["success" => false, "message" => "No account found with that email address."];
        logActivity("Attempted password reset for non-existent email $email", "FAILURE");
    }
} else {
    $response = ["success" => false, "message" => "Please enter a valid email address."];
    logActivity("Invalid email format entered for password reset", "FAILURE");
}

$conn->close();
echo json_encode($response);

function generateStrongPassword($length = 8) {
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers = '0123456789';
    $specialChars = '!@#$%^&*()-_+=';
    $password = '';
    $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    $password .= $numbers[rand(0, strlen($numbers) - 1)];
    $password .= $specialChars[rand(0, strlen($specialChars) - 1)];
    for ($i = strlen($password); $i < $length; $i++) {
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $specialChars[rand(0, strlen($specialChars) - 1)];
    }
    return substr(str_shuffle($password), 0, $length);
}
?>
