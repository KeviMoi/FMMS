<!--  PHP Code  -->
<?php require("mail_script.php"); ?>
<?php include 'db_config/db_conn.php'; ?>

<?php
session_start();

include 'logger.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Retrieve form data
    $name = trim($_POST["name"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $role = trim($_POST["role"]);
    $phone_number = trim($_POST["phone_number"]);
    $dob = trim($_POST["dob"]);

    // Validation functions
    function validateName($name)
    {
        return preg_match("/^[a-zA-Z' -]{2,100}$/", $name);
    }

    function validateUsername($username)
    {
        return preg_match("/^[a-zA-Z0-9_-]{2,20}$/", $username);
    }

    function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    function validatePhoneNumber($phone_number)
    {
        return preg_match("/^\+?[0-9]{10,15}$/", $phone_number);
    }

    function validateDOB($dob)
    {
        $dobTimestamp = strtotime($dob);
        $age = (time() - $dobTimestamp) / (365 * 24 * 60 * 60);
        return $age >= 18 && $age <= 100;
    }

    // Validate form data
    if (!validateName($name)) {
        $errors[] = "Please enter a valid full name.";
    }

    if (!validateUsername($username)) {
        $errors[] = "Username should a maximum of 20 characters and contain may alphanumeric characters, underscores, and hyphens.";
    }

    if (!validateEmail($email)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!validatePhoneNumber($phone_number)) {
        $errors[] = "Please enter a valid phone number.";
    }

    if (!validateDOB($dob)) {
        $errors[] = "Please enter a valid date of birth. User must be at least 18 years old";
    }

    if (!in_array($role, ['driver', 'mechanic', 'manager'])) {
        $errors[] = "Please select a valid role: Manager, Driver, or Mechanic.";
    }

    // Check for uniqueness of username
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($username_count);
    $stmt->fetch();
    if ($username_count > 0) {
        $errors[] = "Sorry, the username you entered is already taken. Please choose a different one.";
    }
    $stmt->close();

    // Check for uniqueness of email
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($email_count);
    $stmt->fetch();
    if ($email_count > 0) {
        $errors[] = "Sorry, the email address you entered is already registered. Please use a different email address.";
    }
    $stmt->close();

    // If there are no errors, proceed with inserting the user
    if (empty($errors)) {
        try {
            // Generate a strong password
            $generatedPassword = generateStrongPassword();

            // Hash the password
            $hashedPassword = password_hash($generatedPassword, PASSWORD_DEFAULT);

            // SQL statement
            $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, role, phone_number, dob, password, password_change) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $password_change = false;
            $stmt->bind_param("sssssssi", $username, $name, $email, $role, $phone_number, $dob, $hashedPassword, $password_change);

            // Execute the SQL statement
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $stmt->error);
            }

            // Send email after successful database insertion
            $message = "Dear $name,<br>";
            $message .= "Thank you for registering with us!<br>";
            $message .= "Your account credentials are as follows:<br>";
            $message .= "Username: $username<br>";
            $message .= "Password: $generatedPassword<br>";
            $message .= "Please keep this information secure and do not share it with anyone.<br>";
            $message .= "Best regards,<br>";
            $message .= "Makvo Limited Team";

            if (!sendMail($email, "Account Details", $message)) {
                throw new Exception("Failed to send email.");
            }

            // Success message
            $message = "User successfully added: Username - $username, Email - $email";
            logActivity($message, "SUCCESS", $_SESSION['username']);
            echo "<div class='message-box success'>User Successfully Added</div>";
        } catch (Exception $e) {
            // Error message
            $message = "Error adding user: " . $e->getMessage();
            logActivity($message, "ERROR", $_SESSION['username']);
            echo "<div class='message-box error'>Error: " . $e->getMessage() . "</div>";
        } finally {
            // Close the statement and connection
            if (isset($stmt)) {
                $stmt->close();
            }
            $conn->close();
        }
    } else {
        // Display all validation errors
        foreach ($errors as $error) {
            echo "<div class='message-box error'>$error</div>";
        }
        $message = "User addition failed due to validation errors";
        logActivity($message, "ERROR", $_SESSION['username']);
    }

    // Redirect to dashboard (optional)
    //header('Location: fleet_manager_dashboard.php');
    exit();
}

// Function to generate a Strong Password
function generateStrongPassword($length = 12)
{
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
<!--  PHP Code  -->

<!--  HTML Code  -->

<head>
    <link rel="stylesheet" href="assets/css/message_box.css" />
</head>

<div id="modalDialog" class="modal" style="display: none">
    <div class="modal-content animate-top">
        <div class="modal-header">
            <h5 class="modal-title">Create New User</h5>
            <button type="button" class="close close-icon">
                <span class="material-icons-sharp">close</span>
            </button>
        </div>
        <!--  Modal Body  -->
        <div class="modal_container">
            <div id="message-container"></div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="createUserForm">
                <div class="user-details">
                    <div class="input-box">
                        <span class="details">Full Name</span>
                        <input type="text" name="name" id="name" placeholder="Enter your name" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Username</span>
                        <input type="text" name="username" id="username" placeholder="Enter your username" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Email</span>
                        <input type="text" name="email" id="email" placeholder="Enter your email" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Phone Number</span>
                        <input type="text" name="phone_number" id="phone_number" placeholder="Enter your phone number" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Date of Birth</span>
                        <input type="date" name="dob" id="dob" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Role</span>
                        <select id="role" name="role" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="driver">Driver</option>
                            <option value="mechanic">Mechanic</option>
                            <option value="manager">Fleet Manager</option>
                        </select>
                    </div>
                </div>
                <div class="button">
                    <input type="submit" value="Create" />
                </div>
            </form>

        </div>
        <!--  End of Modal Body  -->
    </div>
</div>
<!--  HTML Code  -->

<!--  js  -->
<script>
    document.getElementById('createUserForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);

        fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('message-container').innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('message-container').innerHTML = '<div class="message-box error">An error occurred. Please try again later.</div>';
            });
    });
</script>
<!--  js  -->

<!--  Css  -->
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
    }

    body {
        display: flex;
        height: auto;
        justify-content: center;
        align-items: center;
        padding: 10px;
    }

    .modal_container {
        padding: 20px;
    }

    ::-webkit-input-placeholder {
        /* Chrome/Opera/Safari */
        color: #969494;
    }

    ::-moz-placeholder {
        /* Firefox 19+ */
        color: #969494;
    }

    :-ms-input-placeholder {
        /* IE 10+ */
        color: #969494;
    }

    :-moz-placeholder {
        /* Firefox 18- */
        color: #969494;
    }

    .animate-top {
        position: relative;
        animation: animatetop 0.4s;
    }

    @keyframes animatetop {
        from {
            top: -300px;
            opacity: 0;
        }

        to {
            top: 0;
            opacity: 1;
        }
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
        /* Semi-transparent background */
        backdrop-filter: blur(5px);
        /* Apply blur effect */
    }

    .modal-content {
        margin: 8% auto;
        border: 1px solid #888;
        max-width: 700px;
        width: auto;
        height: auto;
        background-color: #fff;
        border: 1px solid rgba(0, 0, 0, .2);
        border-radius: 10px;
        outline: 0;
    }


    .modal-header {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: start;
        -ms-flex-align: start;
        align-items: flex-start;
        -webkit-box-pack: justify;
        -ms-flex-pack: justify;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 0.3rem;
        border-top-right-radius: 0.3rem;
    }

    .modal-title {
        margin-bottom: 0;
        line-height: 1.5;
        margin-top: 0;
    }

    h5.modal-title {
        font-size: 1.25rem;
        color: #666;
    }

    .close {
        float: right;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: 0.5;
    }

    button.close {
        padding: 0;
        background-color: transparent;
        border: 0;
        /*-webkit-appearance: none;*/
    }

    .modal-header .close {
        padding: 1rem;
        margin: -1rem -1rem -1rem auto;
    }

    .close:not(:disabled):not(.disabled) {
        cursor: pointer;
    }

    .modal-body {
        flex: 1 1 auto;
        padding: 1rem;
    }

    .modal-body p {
        margin-top: 0;
        margin-bottom: 1rem;
    }

    .modal-footer {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        -webkit-box-pack: end;
        -ms-flex-pack: end;
        justify-content: flex-end;
        padding: 1rem;
        border-top: 1px solid #e9ecef;
    }

    .modal_container form .user-details {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        /*margin: 20px 0 12px 0;*/
    }

    form .user-details .input-box {
        margin-bottom: 15px;
        width: calc(100% / 2 - 20px);
    }

    .user-details .input-box .details {
        display: block;
        font-weight: 500;
        margin-bottom: 5px;
    }

    form .user-details .input-box input,
    .input-box select {
        height: 45px;
        width: 100%;
        outline: none;
        border-radius: 5px;
        border: 1px solid #ccc;
        padding-left: 15px;
        font-size: 16px;
        border-bottom-width: 2px;
        transition: all 0.3s ease;
    }


    .user-details .input-box input:focus,
    .input-box select:focus,
    .user-details .input-box input:valid,
    .input-box select:valid {
        border-color: #6C9BCF;
    }

    form .button {
        height: 45px;
        width: 90%;
        margin: 0 auto;
        padding: 2px;
    }

    form .button input {
        height: 100%;
        width: 100%;
        outline: none;
        color: #fff;
        border: none;
        font-size: 18px;
        font-weight: 500;
        border-radius: 5px;
        letter-spacing: 1px;
        background: #6C9BCF;
    }

    form .button input:hover {
        background: linear-gradient(-135deg, #71b7e6, #6C9BCF);
    }

    @media (max-width: 584px) {
        .modal_container {
            max-width: 100%;
        }

        form .user-details .input-box {
            margin-bottom: 15px;
            width: 100%;
        }

        form .gender-details .category {
            width: 100%;
        }

        .modal_container form .user-details {
            max-height: 300px;
            overflow-y: scroll;
        }

        .user-details::-webkit-scrollbar {
            width: 0;
        }
    }
</style>
<!--  End of Css  -->