<!-- PHP Code -->
<?php require("mail_script.php"); ?>
<?php include 'db_config/db_conn.php'; ?>

<?php
session_start();

include 'logger.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Retrieve form data
    $breakdown_description = trim($_POST["breakdown_description"]);

    // Retrieve vehicle_id from session
    $vehicle_id = $_SESSION['vehicle_id'];

    // Validation function
    function validateDescription($description)
    {
        return strlen($description) >= 10;
    }

    // Validate form data
    if (!validateDescription($breakdown_description)) {
        $errors[] = "Please enter a valid breakdown description (at least 10 characters).";
    }

    // Check if GPS coordinates are available
    if (!isset($_POST['latitude']) || !isset($_POST['longitude'])) {
        $errors[] = "Unable to retrieve GPS coordinates.";
    } else {
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
    }

    // If there are no errors, proceed with inserting the breakdown request
    if (empty($errors)) {
        try {
            // SQL statement
            $stmt = $conn->prepare("INSERT INTO breakdown_requests (vehicle_id, breakdown_description, latitude, longitude, status) VALUES (?, ?, ?, ?, ?)");
            $status = 'Pending';
            $stmt->bind_param("issds", $vehicle_id, $breakdown_description, $latitude, $longitude, $status);

            // Execute the SQL statement
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $stmt->error);
            }

            // Success message
            $message = "Breakdown request successfully added.";
            logActivity($message, "SUCCESS", $_SESSION['username']);
            echo "<div class='message-box success'>Breakdown Request Successfully Added</div>";
        } catch (Exception $e) {
            // Error message
            $message = "Error adding breakdown request: " . $e->getMessage();
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
        $message = "Breakdown request addition failed due to validation errors.";
        logActivity($message, "ERROR", $_SESSION['username']);
    }

    exit();
}
?>
<!-- PHP Code -->

<!-- HTML Code -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Maintenance Booking</title>
    <link rel="stylesheet" href="assets/css/message_box.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div id="modalDialog" class="modal" style="display: block">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">Request Breakdown Assist</h5>
                <button type="button" class="close close-icon">
                    <span class="material-icons-sharp">close</span>
                </button>
            </div>
            <div class="modal_container">
                <div id="message-container"></div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="requestBreakdownForm">
                    <div class="details">
                        <div class="input-box full-width">
                            <span class="details">Breakdown Description</span>
                            <textarea name="breakdown_description" id="breakdown_description" placeholder="Describe the breakdown" required></textarea>
                        </div>
                    </div>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <div class="button">
                        <input type="submit" value="Request Assist" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<!-- HTML Code -->

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    document.getElementById('requestBreakdownForm').addEventListener('submit', function(event) {
        event.preventDefault();

        // Display the loading alert
        Swal.fire({
            title: 'Requesting Assist...',
            text: 'Please wait while we process your request.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;

                // Log the coordinates
                console.log("Latitude: " + position.coords.latitude);
                console.log("Longitude: " + position.coords.longitude);

                const formData = new FormData(document.getElementById('requestBreakdownForm'));

                // Proceed with form submission
                fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('message-container').innerHTML = data;
                        Swal.close(); // Close loading alert on success
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('message-container').innerHTML = '<div class="message-box error">An error occurred. Please try again later.</div>';
                        Swal.close(); // Close loading alert on error
                    });
            }, function(error) {
                console.error('Error retrieving GPS coordinates:', error);
                document.getElementById('message-container').innerHTML = '<div class="message-box error">Unable to retrieve GPS coordinates. Please ensure location services are enabled and try again.</div>';
                Swal.close(); // Close loading alert on error
            });

        } else {
            document.getElementById('message-container').innerHTML = '<div class="message-box error">Geolocation is not supported by this browser.</div>';
            Swal.close();
        }
    });
</script>
<!-- JS -->

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
        height: 100vh;
        justify-content: center;
        align-items: center;
        padding: 10px;
        background-color: #f5f5f5;
    }

    .modal_container {
        padding: 20px;
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
        backdrop-filter: blur(5px);
    }

    .modal-content {
        margin: 8% auto;
        border: 1px solid #888;
        max-width: 65%;
        width: auto;
        background-color: #fff;
        border: 1px solid rgba(0, 0, 0, .2);
        border-radius: 10px;
        outline: 0;
    }

    .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 0.3rem;
        border-top-right-radius: 0.3rem;
    }

    .modal-title {
        margin: 0;
        line-height: 1.5;
        font-size: 1.25rem;
        color: #666;
    }

    .close {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        opacity: 0.5;
        background-color: transparent;
        border: none;
        cursor: pointer;
    }

    .details {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .input-box {
        margin-bottom: 15px;
        width: calc(50% - 10px);
    }

    .input-box.full-width {
        width: 100%;
    }

    .input-box .details {
        display: block;
        font-weight: 500;
        margin-bottom: 5px;
    }

    h5 {
        padding-bottom: 8px;
    }

    .input-box input,
    .input-box textarea {
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

    .input-box textarea {
        height: auto;
        padding-top: 10px;
        resize: vertical;
    }

    .input-box input:focus,
    .input-box textarea:focus {
        border-color: #6C9BCF;
    }

    .button {
        height: 45px;
        width: 90%;
        margin: 0 auto;
        padding: 2px;
    }

    .button input {
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

    .button input:hover {
        background: linear-gradient(-135deg, #71b7e6, #6C9BCF);
    }

    @media (max-width: 584px) {
        .modal_container {
            max-width: 100%;
        }

        .details {
            max-height: 300px;
            overflow-y: scroll;
        }

        .input-box {
            width: 100%;
        }
    }
</style>
<!--  Css  -->