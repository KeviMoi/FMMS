<?php
// Start the session
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['status'])) {
    // If the session variables are not set, redirect to the login page
    header("Location: index.php");
    exit;
}

// Check if the user's status is active
if ($_SESSION['status'] !== 'active') {
    // If the user's status is not active, show an error message and stop further processing
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Account Inactive</title>
    </head>
    <body>
        <p>Your account is not active. Please contact the administrator.</p>
    </body>
    </html>';
    session_destroy();
    exit;
}

$role = $_SESSION['role'];
$passwordChange = $_SESSION['password_change'];

if ($passwordChange == false && $role == "manager") {
    // Trigger JavaScript to load the change password modal for manager
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Change Password</title>
        <style>
            body {
                background-image: url("assets/images/bg.jpg");
                background-size: cover;
                background-repeat: no-repeat;
                height: 100vh;
                margin: 0;
                font-family: Arial, sans-serif;
            }
            #modalPlaceholder {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100%;
            }
        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    </head>
    <body>
        <div id="modalPlaceholder"></div>
        <script>
            $(document).ready(function() {
                console.log("Loading change password modal...");
                $.get("change_password.php", function(data) {
                    $("#modalPlaceholder").html(data);
                    $("#modalDialog").show();
                });
            });
        </script>
    </body>
    </html>';
} else {
    // Redirect to the respective dashboard based on the user's role
    switch ($role) {
        case "driver":
            header("Location: driver_dashboard.php");
            exit;
        case "mechanic":
            header("Location: mechanic_dashboard.php");
            exit;
        case "manager":
            header("Location: fleet_manager_dashboard.php");
            exit;
        default:
            // Redirect to a generic dashboard if the role is unknown
            header("Location: generic_dashboard.php");
            exit;
    }
}
?>
