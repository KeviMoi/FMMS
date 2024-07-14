<?php
// Start the session
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username']) || !isset($_SESSION['full_name'])) {
    header("Location: index.php");
    exit;
}

// Retrieve the username, full name, and user ID from the session
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];
$user_id = $_SESSION['user_id'];

// Extract the first name from the full name
$first_name = explode(' ', trim($full_name))[0];

// Include the database connection file
include 'db_config/db_conn.php';

// Query to get the vehicle details assigned to the driver
$sql = "SELECT * FROM vehicles WHERE assigned_driver_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the vehicle details
$vehicle = $result->fetch_assoc();

// Check if a vehicle is assigned to the driver
if ($vehicle) {
    $vehicle_id = $vehicle['vehicle_id'];
    $license_plate = $vehicle['license_plate'];
    $make = $vehicle['make'];
    $model = $vehicle['model'];
    $year = $vehicle['year'];
    $vin = $vehicle['vin'];
    $mileage = $vehicle['mileage'];
    $fuel_type = $vehicle['fuel_type'];
    $status = $vehicle['status'];
} else {
    $vehicle_id = null;
    $license_plate = "No vehicle assigned";
    $make = "";
    $model = "";
    $year = "";
    $vin = "";
    $mileage = "";
    $fuel_type = "";
    $status = "";
}

$_SESSION['vehicle_id'] = $vehicle_id;


// Close the statement and connection
$stmt->close();
$conn->close();
?>

<div id="modalDialog" class="modal" style="display: none">
    <div class="modal-content animate-top">
        <div class="modal-header">
            <h5 class="modal-title">Vehicle Details</h5>
            <button type="button" class="close close-icon">
                <span class="material-icons-sharp">close</span>
            </button>
        </div>
        <!-- Modal Body -->
        <div class="modal_container">
            <div class="vehicle-card">
                <div class="vehicle-header">
                    <h2><?php echo htmlspecialchars($license_plate); ?></h2>
                </div>
                <div class="vehicle-info">
                    <div class="info-item">
                        <span class="label">Make:</span>
                        <span class="value"><?php echo htmlspecialchars($make); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Model:</span>
                        <span class="value"><?php echo htmlspecialchars($model); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Year:</span>
                        <span class="value"><?php echo htmlspecialchars($year); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">VIN:</span>
                        <span class="value"><?php echo htmlspecialchars($vin); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Mileage:</span>
                        <span class="value"><?php echo htmlspecialchars($mileage); ?> km</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Fuel Type:</span>
                        <span class="value"><?php echo htmlspecialchars($fuel_type); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Status:</span>
                        <span class="value"><?php echo htmlspecialchars($status); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Modal Body -->
    </div>
</div>

<!-- CSS -->
<style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap");

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
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
        background-color: rgba(0, 0, 0, 0.4);
        /* Semi-transparent background */
        backdrop-filter: blur(5px);
        /* Apply blur effect */
    }

    .modal-content {
        margin: 5% auto;
        border: 1px solid #888;
        max-width: 500px;
        width: 90%;
        background-color: #fff;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 10px;
        outline: 0;
        max-height: 80%;
        /* Ensure modal height doesn't exceed the viewport */
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: 0.3rem;
        border-top-right-radius: 0.3rem;
        position: sticky;
        top: 0;
        background-color: #fff;
        z-index: 10;
    }

    .modal-title {
        margin-bottom: 0;
        line-height: 1.5;
        margin-top: 0;
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
        cursor: pointer;
        background-color: transparent;
        border: 0;
    }

    .modal_container {
        padding: 20px;
        overflow-y: auto;
        flex-grow: 1;
        /* Ensure the container takes up remaining space */
    }

    .vehicle-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 100%;
        padding: 5px;
    }

    .vehicle-header {
        background-color: #0298cf;
        color: #fff;
        padding: 15px;
        border-radius: 10px 10px 0 0;
        text-align: center;
    }

    .vehicle-info {
        padding: 20px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .label {
        font-weight: bold;
        color: #333;
    }

    .value {
        color: #555;
    }

    ::-webkit-scrollbar {
        height: 5px;
        width: 6px;
    }

    ::-webkit-scrollbar-track {
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.3);
    }

    ::-webkit-scrollbar-thumb {
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.3);
    }
</style>
<!-- End of CSS -->