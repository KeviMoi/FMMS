<?php
// Start the session
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection file
include 'db_config/db_conn.php';

// Initialize an error message variable
$error_message = '';
$success_message = '';

try {
    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve form data
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Execute the SQL query to fetch user details for the given username
        $sql = "SELECT user_id, password, role, full_name, password_change, status FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Failed to prepare SQL statement.');
        }
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a user with the given username exists
        if ($result->num_rows > 0) {
            // Fetch the result row
            $row = $result->fetch_assoc();
            $hashedPassword = $row["password"];
            $role = $row["role"];
            $user_id = $row["user_id"];
            $full_name = $row["full_name"];
            $passwordChange = $row["password_change"];
            $status = $row["status"];

            // Verify the password
            if (password_verify($password, $hashedPassword)) {
                if ($status === 'active') {
                    // User is active
                    // Store the user_id, username, full_name, password change status, and status in the session
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['password_change'] = $passwordChange;
                    $_SESSION['role'] = $role;
                    $_SESSION['status'] = $status;

                    // Redirect to a script that will handle the post-login logic
                    header("Location: post_login.php");
                    exit;
                } else {
                    // User is not active
                    $error_message = "Your account is not active. Please contact the administrator.";
                }
            } else {
                // Password is incorrect
                $error_message = "Incorrect username or password.";
            }
        } else {
            // User with the given username does not exist
            $error_message = "Incorrect username or password.";
        }

        // Free the result set
        $stmt->close();
        $result->free();
    }
} catch (Exception $e) {
    // Catch any exceptions and set the error message
    $error_message = 'An error occurred: ' . $e->getMessage();
} finally {
    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Makvo Limited | FMMS</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/index_styles.css" />
    <link rel="stylesheet" href="assets/css/message_box.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
    <div class="wrapper">
        <nav class="nav">
            <div class="nav-logo">
                <img src="assets/images/white-logo.png" alt="Logo" width="65" height="65" />
                <p>Makvo Limited | FMMS</p>
            </div>
        </nav>
        <div class="form-box">
            <div class="login-container" id="login">
                <header>Login</header>
                <div id="message-container">
                    <?php if (!empty($error_message)) : ?>
                        <div class="message-box error"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success_message)) : ?>
                        <div class="message-box success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="input-box">
                        <input type="text" class="input-field" placeholder="Username" name="username" required />
                        <i class="bx bx-user"></i>
                    </div>
                    <div class="input-box">
                        <input type="password" class="input-field" placeholder="Password" name="password" required />
                        <i class="bx bx-lock-alt"></i>
                    </div>
                    <div class="input-box">
                        <input type="submit" class="submit" value="Sign In" />
                    </div>
                </form>
                <div class="forgot-password">
                    <label><a href="#">Forgot password?</a></label>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const messageContainer = document.getElementById("message-container");
            if (messageContainer.innerText.trim().length > 0) {
                setTimeout(() => {
                    messageContainer.innerHTML = '';
                }, 5000);
            }
        });
    </script>
</body>
</html>
