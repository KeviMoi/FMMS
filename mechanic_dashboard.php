<?php
// Start the session
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['username']) || !isset($_SESSION['full_name'])) {
    header("Location: index.php");
    exit;
}

// Retrieve the username and full name from the session
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];
$user_id = $_SESSION['user_id'];

// Extract the first name from the full name
$first_name = explode(' ', trim($full_name))[0];

// Include the database connection file
require_once 'db_config/db_conn.php';

// Get the current date
$current_date = date('Y-m-d');

// Query to count the number of pending schedules for today in the service center the user is assigned to
$query = "SELECT COUNT(*) AS pending_count
          FROM maintenance_schedule
          JOIN service_center_mechanics ON maintenance_schedule.service_center_id = service_center_mechanics.service_center_id
          WHERE service_center_mechanics.mechanic_id = ? 
          AND maintenance_schedule.schedule_date = ?
          AND maintenance_schedule.status = 'Scheduled'";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("is", $user_id, $current_date);
    $stmt->execute();
    $stmt->bind_result($pending_count);
    $stmt->fetch();
    $stmt->close();
} else {
    $pending_count = 0;
}

// Query to count the number of admitted schedules (in progress) for today in the service center the user is assigned to
$query = "SELECT COUNT(*) AS admitted_count
          FROM maintenance_schedule
          JOIN service_center_mechanics ON maintenance_schedule.service_center_id = service_center_mechanics.service_center_id
          WHERE service_center_mechanics.mechanic_id = ? 
          AND maintenance_schedule.schedule_date = ?
          AND maintenance_schedule.status = 'In Progress'";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("is", $user_id, $current_date);
    $stmt->execute();
    $stmt->bind_result($admitted_count);
    $stmt->fetch();
    $stmt->close();
} else {
    $admitted_count = 0;
}

// Query to count the number of completed schedules for today in the service center the user is assigned to
$query = "SELECT COUNT(*) AS completed_count
          FROM maintenance_schedule
          JOIN service_center_mechanics ON maintenance_schedule.service_center_id = service_center_mechanics.service_center_id
          WHERE service_center_mechanics.mechanic_id = ? 
          AND maintenance_schedule.schedule_date = ?
          AND maintenance_schedule.status = 'Completed'";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("is", $user_id, $current_date);
    $stmt->execute();
    $stmt->bind_result($completed_count);
    $stmt->fetch();
    $stmt->close();
} else {
    $completed_count = 0;
}

// Query to retrieve the earliest 6 pending schedules for the current day onwards
$query = "SELECT v.license_plate, ms.schedule_date, ms.schedule_start_time, ms.status
          FROM maintenance_schedule ms
          JOIN service_center_mechanics scm ON ms.service_center_id = scm.service_center_id
          JOIN vehicles v ON ms.vehicle_id = v.vehicle_id
          WHERE scm.mechanic_id = ? 
          AND ms.schedule_date >= ?
          AND ms.status = 'Scheduled'
          ORDER BY ms.schedule_date, ms.schedule_start_time
          LIMIT 6";

$pending_schedules = [];

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("is", $user_id, $current_date);
    $stmt->execute();
    $stmt->bind_result($license_plate, $schedule_date, $schedule_start_time, $status);

    while ($stmt->fetch()) {
        $pending_schedules[] = [
            'license_plate' => $license_plate,
            'schedule_date' => $schedule_date,
            'schedule_start_time' => $schedule_start_time,
            'status' => $status,
        ];
    }
    $stmt->close();
}

// Query to retrieve the earliest 2 admitted (in progress) schedules for the current day onwards
$query = "SELECT v.license_plate, ms.schedule_start_time
          FROM maintenance_schedule ms
          JOIN service_center_mechanics scm ON ms.service_center_id = scm.service_center_id
          JOIN vehicles v ON ms.vehicle_id = v.vehicle_id
          WHERE scm.mechanic_id = ? 
          AND ms.schedule_date >= ?
          AND ms.status = 'In Progress'
          ORDER BY ms.schedule_date, ms.schedule_start_time
          LIMIT 2";

$admitted_schedules = [];

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("is", $user_id, $current_date);
    $stmt->execute();
    $stmt->bind_result($license_plate, $schedule_start_time);

    while ($stmt->fetch()) {
        $admitted_schedules[] = [
            'license_plate' => $license_plate,
            'schedule_start_time' => $schedule_start_time
        ];
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/mechanic_dashboard_styles.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <title>Mechanic</title>
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
                <a href="#" id="mechanic_schedules">
                    <span class="material-icons-sharp">
                        local_shipping
                    </span>
                    <h3>Admit Vehicle</h3>
                </a>
                <a href="#" id="checkout_vehicle_view">
                    <span class="material-icons-sharp">
                        no_crash
                    </span>
                    <h3>Check Out Vehicle</h3>
                </a>
                <a href="#" id="mechanic_view_service_history">
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
                <a href="#" id="mechanic_change_password">
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
            <h1>Mechanic Dashboard</h1>
            <!--  Analyses  -->
            <div class="analyse">
                <div class="card-1">
                    <div class="status">
                        <div class="info">
                            <h3>Admitted Vehicles</h3>
                            <h1></h1>
                        </div>
                        <div class="progresss">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="percentage">
                                <h2><b><?php echo str_pad($admitted_count, 3, '0', STR_PAD_LEFT); ?></b></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-2">
                    <div class="status">
                        <div class="info">
                            <h3>Today's Pending Schedules</h3>
                            <h1></h1>
                        </div>
                        <div class="progresss">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="percentage">
                                <h2><b><?php echo str_pad($pending_count, 3, '0', STR_PAD_LEFT); ?></b></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-3">
                    <div class="status">
                        <div class="info">
                            <h3>Today's Completed Schedules</h3>
                            <h1></h1>
                        </div>
                        <div class="progresss">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="percentage">
                                <h2><b><?php echo str_pad($completed_count, 3, '0', STR_PAD_LEFT); ?></b></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--  End of Analyses  -->

            <!-- Recent Orders Table -->
            <div class="schedules">
                <h2>Pending Maintenance</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Registration Number</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pending_schedules)) : ?>
                            <?php foreach ($pending_schedules as $schedule) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($schedule['license_plate']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['schedule_date']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['schedule_start_time']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5">No pending schedules found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="#" id="show_all_mechanic_schedules">Show All</a>
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
                        <small class="text-muted">Mechanic</small>
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
                    <h2>Admitted</h2>
                    <span class="material-icons-sharp">
                        notifications_none
                    </span>
                </div>

                <?php if (!empty($admitted_schedules)) : ?>
                    <?php foreach ($admitted_schedules as $index => $schedule) : ?>
                        <div class="notification <?php echo $index == 0 ? '' : 'deactive'; ?>">
                            <div class="icon">
                                <span class="material-icons-sharp">
                                    notifications_active
                                </span>
                            </div>
                            <div class="content">
                                <div class="info">
                                    <h3><?php echo htmlspecialchars($schedule['license_plate']); ?></h3>
                                    <small class="text_muted">
                                        <?php echo htmlspecialchars($schedule['schedule_start_time']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="notification deactive">
                        <div class="icon">
                            <span class="material-icons-sharp">
                                notifications_active
                            </span>
                        </div>
                        <div class="content">
                            <div class="info">
                                <h3>No admitted schedules found.</h3>
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