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

// Query to get the 10 most recent service history records for the vehicle
$sql = "SELECT mt.task_name, sh.service_date, sh.odometer_reading 
        FROM service_history sh 
        JOIN maintenance_tasks mt ON sh.task_id = mt.task_id 
        WHERE sh.vehicle_id = ? 
        ORDER BY sh.service_date DESC 
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the service history records
$service_history = $result->fetch_all(MYSQLI_ASSOC);

// Query to get the 2 earliest upcoming maintenance schedules for the vehicle
$sql = "SELECT mt.task_name, ms.schedule_date, ms.schedule_start_time, ms.schedule_end_time 
        FROM maintenance_schedule ms 
        JOIN maintenance_tasks mt ON ms.task_id = mt.task_id 
        WHERE ms.vehicle_id = ? AND ms.status = 'Scheduled' 
        ORDER BY ms.schedule_date ASC, ms.schedule_start_time ASC 
        LIMIT 2";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the upcoming maintenance schedules
$upcoming_schedules = $result->fetch_all(MYSQLI_ASSOC);

// Close the statement and connection
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/driver_dashboard_styles.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <title>Driver</title>
</head>

<body>


    <div class="container">
        <!--  Sidebar Section  -->
        <aside>
            <div class="toggle">
                <div class="logo">
                    <img src="assets/images/black-logo.png" />
                    <h2>Makvo<span class="primary">Limited</span></h2>
                </div>
                <div class="close" id="close-btn">
                    <span class="material-icons-sharp">
                        close
                    </span>
                </div>
            </div>
            <div class="sidebar">
                <a href="#" class="active">
                    <span class="material-icons-sharp">
                        dashboard
                    </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="#" id="vehicle_card">
                    <span class="material-icons-sharp">
                        local_shipping
                    </span>
                    <h3>Vehicle Info</h3>
                </a>
                <a href="#" id="schedule_maintenance">
                    <span class="material-icons-sharp">
                        add
                    </span>
                    <h3>Add Schedule</h3>
                </a>
                <a href="#" id="driver_schedules">
                    <span class="material-icons-sharp">
                        schedule
                    </span>
                    <h3>My Schedules</h3>
                </a>
                <a href="#" id="request_breakdown_assist">
                    <span class="material-icons-sharp">
                        report_gmailerrorred
                    </span>
                    <h3>Breakdown Assist</h3>
                </a>
                <a href="#" id="view_vehicle_service_history">
                    <span class="material-icons-sharp">
                        receipt_long
                    </span>
                    <h3>Service History</h3>
                </a>

                <a href="#" id="view_notifications">
                    <span class="material-icons-sharp">
                        notifications
                    </span>
                    <h3>Notifications</h3>
                    <span class="message-count" id="unreadCount">0</span>
                </a>
                <a href="#" id="driver_change_password">
                    <span class="material-icons-sharp"> password </span>
                    <h3>Change Password</h3>
                </a>
                <div class="logout-container">
                    <a href="logout.php">
                        <span class="material-icons-sharp"> logout </span>
                        <h3>Logout</h3>
                    </a>
                </div>
            </div>
        </aside>
        <!--  End of Sidebar Section  -->

        <!--  Main Content  -->
        <main>
            <h1>Driver Dashboard</h1>
            <!-- Recent Maintenance History Table -->
            <div class="schedules">
                <h2>Recent Service History</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Date</th>
                            <th>Odometer Reading</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($service_history as $history) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($history['task_name']); ?></td>
                                <td><?php echo htmlspecialchars($history['service_date']); ?></td>
                                <td><?php echo htmlspecialchars($history['odometer_reading']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="#">Show All</a>
            </div>
            <!-- End of Recent Orders -->
        </main>
        <!--  End of Main Content  -->

        <!-- Right Section -->
        <div class="right-section">
            <div class="nav">
                <button id="menu-btn">
                    <span class="material-icons-sharp">
                        menu
                    </span>
                </button>
                <div class="dark-mode">
                    <span class="material-icons-sharp active">
                        light_mode
                    </span>
                    <span class="material-icons-sharp">
                        dark_mode
                    </span>
                </div>

                <div class="profile">
                    <div class="info">
                        <p>Hey, <b><?php echo htmlspecialchars($first_name); ?></b></p>
                        <small class="text-muted">Driver</small>
                    </div>
                </div>

            </div>
            <!-- End of Nav -->

            <div class="company-profile">
                <div class="logo">
                    <img src="assets/images/black-logo.png" />
                    <h2>Makvo Limited</h2>
                    <p>Confidence in Motion</p>
                </div>
            </div>

            <div class="reminders">
                <div class="header">
                    <h2>Upcoming</h2>
                    <span class="material-icons-sharp">
                        notifications_none
                    </span>
                </div>

                <?php if (count($upcoming_schedules) > 0) : ?>
                    <?php foreach ($upcoming_schedules as $index => $schedule) : ?>
                        <div class="notification <?php echo $index === 1 ? 'deactive' : ''; ?>">
                            <div class="icon">
                                <span class="material-icons-sharp">
                                    notifications_active
                                </span>
                            </div>
                            <div class="content">
                                <div class="info">
                                    <h3><?php echo htmlspecialchars($schedule['task_name']); ?></h3>
                                    <small class="text_muted">
                                        <?php
                                        echo htmlspecialchars($schedule['schedule_date']) . ' at ' .
                                            htmlspecialchars($schedule['schedule_start_time']) . ' - ' .
                                            htmlspecialchars($schedule['schedule_end_time']);
                                        ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="notification">
                        <div class="icon">
                            <span class="material-icons-sharp">
                                notifications_active
                            </span>
                        </div>
                        <div class="content">
                            <div class="info">
                                <h3>No Upcoming Maintenance</h3>
                                <small class="text_muted">
                                    No scheduled maintenance tasks
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
        <div id="modalPlaceholder"></div>
    </div>
    <script src="assets/js/dummy_table.js"></script>
    <script src="assets/js/dashboard_script.js"></script>
    <script src="assets/js/modal_loader_script.js"></script>
    <script src="assets/js/unread_notifications.js"></script>
</body>

</html>