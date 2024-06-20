<!--  PHP Code  -->
<!--  PHP Code  -->
<?php require("mail_script.php"); ?>
<?php include 'db_config/db_conn.php'; ?>

<?php
session_start();

include 'logger.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Retrieve form data
    $license_plate = trim($_POST["license_plate"]);
    $make = trim($_POST["make"]);
    $model = trim($_POST["model"]);
    $year = trim($_POST["year"]);
    $vin = trim($_POST["vin"]);
    $mileage = trim($_POST["mileage"]);
    $fuel_type = trim($_POST["fuel_type"]);
    $assigned_driver_id = trim($_POST["assigned_driver_id"]);

    // Validation functions
    function validateLicensePlate($license_plate)
    {        
        return preg_match("/^K[A-Z]{2}\s*\d{3}[A-Z]$/", $license_plate);
    }


    function validateMake($make)
    {
        return preg_match("/^[a-zA-Z0-9 ]{2,50}$/", $make);
    }

    function validateModel($model)
    {
        return preg_match("/^[a-zA-Z0-9 ]{2,50}$/", $model);
    }

    function validateYear($year)
    {
        return preg_match("/^\d{4}$/", $year) && $year <= date("Y");
    }

    function validateVIN($vin)
    {
        return preg_match("/^[A-HJ-NPR-Z0-9]{17}$/", $vin);
    }

    function validateMileage($mileage)
    {
        return preg_match("/^\d+$/", $mileage);
    }

    // Validate form data
    if (!validateLicensePlate($license_plate)) {
        $errors[] = "Please enter a valid license plate.";
    }

    if (!validateMake($make)) {
        $errors[] = "Please enter a valid vehicle make.";
    }

    if (!validateModel($model)) {
        $errors[] = "Please enter a valid vehicle model.";
    }

    if (!validateYear($year)) {
        $errors[] = "Please enter a valid vehicle year.";
    }

    if (!validateVIN($vin)) {
        $errors[] = "Please enter a valid vehicle VIN.";
    }

    if (!validateMileage($mileage)) {
        $errors[] = "Please enter a valid vehicle mileage.";
    }

    if (!in_array($fuel_type, ['Petrol', 'Diesel', 'Electric', 'Hybrid'])) {
        $errors[] = "Please select a valid fuel type.";
    }

    // If there are no errors, proceed with inserting the vehicle
    if (empty($errors)) {
        try {
            // SQL statement
            $stmt = $conn->prepare("INSERT INTO vehicles (license_plate, make, model, year, vin, mileage, fuel_type, assigned_driver_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssisssi", $license_plate, $make, $model, $year, $vin, $mileage, $fuel_type, $assigned_driver_id);

            // Execute the SQL statement
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $stmt->error);
            }

            // Success message
            $message = "Vehicle successfully added: License Plate - $license_plate, VIN - $vin";
            logActivity($message, "SUCCESS", $_SESSION['username']);
            echo "<div class='message-box success'>Vehicle Successfully Added</div>";
        } catch (Exception $e) {
            // Error message
            $message = "Error adding vehicle: " . $e->getMessage();
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
        $message = "Vehicle addition failed due to validation errors";
        logActivity($message, "ERROR", $_SESSION['username']);
    }

    // Redirect to dashboard (optional)
    //header('Location: fleet_manager_dashboard.php');
    exit();
}
?>
<!--  PHP Code  -->

<!--  PHP Code  -->

<!--  HTML Code  -->

<head>
    <link rel="stylesheet" href="assets/css/message_box.css" />
</head>

<div id="modalDialog" class="modal" style="display: none">
    <div class="modal-content animate-top">
        <div class="modal-header">
            <h5 class="modal-title">Add New Vehicle</h5>
            <button type="button" class="close close-icon">
                <span class="material-icons-sharp">close</span>
            </button>
        </div>
        <!--  Modal Body  -->
        <div class="modal_container">
            <div id="message-container"></div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="createVehicleForm">
                <div class="user-details">
                    <div class="input-box">
                        <span class="details">License Plate</span>
                        <input type="text" name="license_plate" id="license_plate" placeholder="Enter license plate" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Make</span>
                        <input type="text" name="make" id="make" placeholder="Enter vehicle make" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Model</span>
                        <input type="text" name="model" id="model" placeholder="Enter vehicle model" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Year</span>
                        <input type="number" name="year" id="year" placeholder="Enter vehicle year" required />
                    </div>
                    <div class="input-box">
                        <span class="details">VIN</span>
                        <input type="text" name="vin" id="vin" placeholder="Enter vehicle VIN" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Mileage</span>
                        <input type="number" name="mileage" id="mileage" placeholder="Enter vehicle mileage" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Fuel Type</span>
                        <select id="fuel_type" name="fuel_type" required>
                            <option value="" disabled selected>Select Fuel Type</option>
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <span class="details">Assigned Driver ID</span>
                        <input type="number" name="assigned_driver_id" id="assigned_driver_id" placeholder="Enter driver ID" />
                    </div>
                </div>
                <div class="button">
                    <input type="submit" value="Add Vehicle" />
                </div>
            </form>

        </div>
        <!--  End of Modal Body  -->
    </div>
</div>
<!--  HTML Code  -->

<!--  js  -->
<!--  js  -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    document.getElementById('createVehicleForm').addEventListener('submit', function(event) {
        event.preventDefault();

        // Display the loading alert
        Swal.fire({
            title: 'Adding Vehicle...',
            text: 'Please wait while we process your request.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(this);

        fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('message-container').innerHTML = data;
                // Close the loading alert after request is completed
                Swal.close();
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('message-container').innerHTML = '<div class="message-box error">An error occurred. Please try again later.</div>';
                // Close the loading alert in case of error
                Swal.close();
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