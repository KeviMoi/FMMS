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

// Query to get the total number of vehicles
$totalVehiclesQuery = "SELECT COUNT(*) AS total FROM vehicles";
$totalVehiclesResult = $conn->query($totalVehiclesQuery);
$totalVehiclesRow = $totalVehiclesResult->fetch_assoc();
$totalVehicles = $totalVehiclesRow['total'];

// Query to get the number of active vehicles
$activeVehiclesQuery = "SELECT COUNT(*) AS active FROM vehicles WHERE status = 'Active'";
$activeVehiclesResult = $conn->query($activeVehiclesQuery);
$activeVehiclesRow = $activeVehiclesResult->fetch_assoc();
$activeVehicles = $activeVehiclesRow['active'];

// Calculate the percentage of active vehicles
$percentageActive = ($totalVehicles > 0) ? ($activeVehicles / $totalVehicles) * 100 : 0;

// Query to get the number of pending breakdown requests
$pendingBreakdownQuery = "SELECT COUNT(*) AS pending FROM breakdown_requests WHERE status = 'Pending'";
$pendingBreakdownResult = $conn->query($pendingBreakdownQuery);
$pendingBreakdownRow = $pendingBreakdownResult->fetch_assoc();
$pendingBreakdowns = $pendingBreakdownRow['pending'];

// Query to get the number of today's pending schedules
$today = date('Y-m-d');
$pendingSchedulesQuery = "SELECT COUNT(*) AS pending_today FROM maintenance_schedule WHERE status = 'Scheduled' AND schedule_date = '$today'";
$pendingSchedulesResult = $conn->query($pendingSchedulesQuery);
$pendingSchedulesRow = $pendingSchedulesResult->fetch_assoc();
$pendingSchedulesToday = $pendingSchedulesRow['pending_today'];

// Query to get the earliest 6 schedules for the current day with status 'Scheduled'
$upcomingSchedulesQuery = "
    SELECT v.license_plate, mt.task_name, ms.schedule_start_time, ms.schedule_end_time 
    FROM maintenance_schedule ms
    JOIN vehicles v ON ms.vehicle_id = v.vehicle_id
    JOIN maintenance_tasks mt ON ms.task_id = mt.task_id
    WHERE ms.status = 'Scheduled' AND ms.schedule_date = '$today'
    ORDER BY ms.schedule_start_time
    LIMIT 6
";
$upcomingSchedulesResult = $conn->query($upcomingSchedulesQuery);

// Query to get the most recent 2 breakdown requests
$recentBreakdownsQuery = "
    SELECT v.license_plate, br.request_date
    FROM breakdown_requests br
    JOIN vehicles v ON br.vehicle_id = v.vehicle_id
    ORDER BY br.request_date DESC
    LIMIT 2
";
$recentBreakdownsResult = $conn->query($recentBreakdownsQuery);
$recentBreakdowns = $recentBreakdownsResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/fleet_manager_dashboard_styles.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <title>Fleet Manager</title>
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
                    <span class="material-icons-sharp"> close </span>
                </div>
            </div>
            <div class="sidebar">
                <a href="#" class="active">
                    <span class="material-icons-sharp"> dashboard </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="#" id="fleet_manager_schedules">
                    <span class="material-icons-sharp"> schedule </span>
                    <h3>Schedules</h3>
                </a>
                <div class="menu-item">
                    <a href="#">
                        <span class="material-icons-sharp"> local_shipping </span>
                        <h3>Vehicles</h3>
                    </a>
                    <div class="submenu">
                        <a href="#" id="addVehicle">
                            <span class="material-icons-sharp">add</span>
                            <h3>Add Vehicle</h3>
                        </a>
                        <a href="#" id="viewVehicles">
                            <span class="material-icons-sharp"> view_list </span>
                            <h3>View Vehicles</h3>
                        </a>
                    </div>
                </div>
                <div class="menu-item">
                    <a href="#">
                        <span class="material-icons-sharp"> manage_accounts </span>
                        <h3>Users</h3>
                    </a>
                    <div class="submenu">
                        <a href="#" id="addUser">
                            <span class="material-icons-sharp">person_add</span>
                            <h3>Create User</h3>
                        </a>
                        <a href="#" id="viewUsers">
                            <span class="material-icons-sharp"> people_outline </span>
                            <h3>View Users</h3>
                        </a>
                    </div>
                </div>

                <a href="#" id="fleet_manager_view_service_history">
                    <span class="material-icons-sharp"> receipt_long </span>
                    <h3>Service History</h3>
                </a>
                <a href="#" id="breakdown_requests">
                    <span class="material-icons-sharp"> report_gmailerrorred </span>
                    <h3>Breakdowns</h3>
                </a>
                <a href="#" id="view_notifications">
                    <span class="material-icons-sharp">
                        notifications
                    </span>
                    <h3>Notifications</h3>
                    <span class="message-count" id="unreadCount">0</span>
                </a>
                <a href="#" id="fleet_manager_change_password">
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
            <h1>Fleet Manager Dashboard</h1>
            <!--  Analyses  -->
            <div class="analyse">
                <div class="card-1">
                    <div class="status">
                        <div class="info">
                            <h3>Active Vehicles</h3>
                            <h1></h1>
                        </div>
                        <div class="progresss">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="percentage">
                                <h2><b><?php echo number_format($percentageActive, 0); ?>%</b></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-2">
                    <div class="status">
                        <div class="info">
                            <h3>Pending Breakdown Requests</h3>
                            <h1></h1>
                        </div>
                        <div class="progresss">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="percentage">
                                <h2><b><?php echo str_pad($pendingBreakdowns, 3, '0', STR_PAD_LEFT); ?></b></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-3">
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
                                <h2><b><?php echo str_pad($pendingSchedulesToday, 3, '0', STR_PAD_LEFT); ?></b></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--  End of Analyses  -->

            <!-- Recent Orders Table -->
            <div class="schedules">
                <h2>Upcoming Schedules</h2>
                <table>
                    <thead>
                        <tr>
                            <th>License Plate</th>
                            <th>Task</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($upcomingSchedulesResult->num_rows > 0) {
                            while ($row = $upcomingSchedulesResult->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['license_plate'] . "</td>";
                                echo "<td>" . $row['task_name'] . "</td>";
                                echo "<td>" . date('H:i', strtotime($row['schedule_start_time'])) . "</td>";
                                echo "<td>" . date('H:i', strtotime($row['schedule_end_time'])) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No schedules found for today</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <a href="#" id="show_all_schedules">Show All</a>
            </div>
            <!-- End of Recent Orders -->
        </main>
        <!--  End of Main Content  -->

        <!-- Right Section -->
        <div class="right-section">
            <div class="nav">
                <button id="menu-btn">
                    <span class="material-icons-sharp"> menu </span>
                </button>
                <div class="dark-mode">
                    <span class="material-icons-sharp active"> light_mode </span>
                    <span class="material-icons-sharp"> dark_mode </span>
                </div>

                <div class="profile">
                    <div class="info">
                        <p>Hey, <b><?php echo htmlspecialchars($first_name); ?></b></p>
                        <small class="text-muted">Fleet Manager</small>
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
                    <h2>Recent Breakdowns </h2>
                    <span class="material-icons-sharp"> notifications_none </span>
                </div>

                <?php
                if (!empty($recentBreakdowns)) {
                    foreach ($recentBreakdowns as $index => $breakdown) {
                        $class = $index == 1 ? 'notification deactive' : 'notification';
                        echo "<div class=\"$class\">
                    <div class=\"icon\">
                        <span class=\"material-icons-sharp\">
                            notifications_active
                        </span>
                    </div>
                    <div class=\"content\">
                        <div class=\"info\">
                            <h3>" . $breakdown['license_plate'] . "</h3>
                            <small class=\"text_muted\">" . date('Y-m-d H:i', strtotime($breakdown['request_date'])) . "</small>
                        </div>
                    </div>
                </div>";
                    }
                } else {
                    echo "<div class=\"notification\">
                <div class=\"icon\">
                    <span class=\"material-icons-sharp\">
                        notifications_active
                    </span>
                </div>
                <div class=\"content\">
                    <div class=\"info\">
                        <h3>No recent breakdowns</h3>
                        <small class=\"text_muted\"></small>
                    </div>
                </div>
            </div>";
                }
                ?>
            </div>
        </div>
        <div id="modalPlaceholder"></div>
    </div>


    <script src="assets/js/dashboard_script.js"></script>
    <script src="assets/js/modal_loader_script.js"></script>
    <script src="assets/js/unread_notifications.js"></script>
</body>

</html>