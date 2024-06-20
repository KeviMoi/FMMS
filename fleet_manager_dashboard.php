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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/dashboard_styles.css" />
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
                <a href="#">
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

                <a href="#">
                    <span class="material-icons-sharp"> receipt_long </span>
                    <h3>Service History</h3>
                </a>
                <a href="#">
                    <span class="material-icons-sharp"> report_gmailerrorred </span>
                    <h3>Breakdowns</h3>
                </a>
                <a href="#">
                    <span class="material-icons-sharp"> mail_outline </span>
                    <h3>Messages</h3>
                    <span class="message-count">27</span>
                </a>
                <a href="#" id="changePassword">
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
                            <h1>201</h1>
                        </div>
                        <div class="progresss">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="percentage">
                                <h2><b>81%</b></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-2">
                    <div class="status">
                        <div class="info">
                            <h3>Pending Schedules</h3>
                            <h1>030</h1>
                        </div>
                        <div class="progresss">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="percentage">
                                <h2><b>48%</b></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-3">
                    <div class="status">
                        <div class="info">
                            <h3>Pending Breakdowns</h3>
                            <h1>001</h1>
                        </div>
                        <div class="progresss">
                            <svg>
                                <circle cx="38" cy="38" r="36"></circle>
                            </svg>
                            <div class="percentage">
                                <h2><b>21%</b></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--  End of Analyses  -->

            <!-- Recent Orders Table -->
            <div class="schedules">
                <h2>Maintenance Schedules</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Registration Number</th>
                            <th>Maintenance Type</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
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
                    <h2>Reminders</h2>
                    <span class="material-icons-sharp"> notifications_none </span>
                </div>

                <div class="notification">
                    <div class="icon">
                        <span class="material-icons-sharp"> volume_up </span>
                    </div>
                    <div class="content">
                        <div class="info">
                            <h3>Service</h3>
                            <small class="text_muted"> 08:00 AM - 12:00 PM </small>
                        </div>
                        <span class="material-icons-sharp"> more_vert </span>
                    </div>
                </div>

                <div class="notification deactive">
                    <div class="icon">
                        <span class="material-icons-sharp"> edit </span>
                    </div>
                    <div class="content">
                        <div class="info">
                            <h3>Tire Change</h3>
                            <small class="text_muted"> 08:00 AM - 12:00 PM </small>
                        </div>
                        <span class="material-icons-sharp"> more_vert </span>
                    </div>
                </div>

                <div class="notification add-reminder">
                    <div>
                        <span class="material-icons-sharp"> add </span>
                        <h3>Add Reminder</h3>
                    </div>
                </div>
            </div>
        </div>
        <div id="modalPlaceholder"></div>
    </div>


    <script src="assets/js/dummy_table.js"></script>
    <script src="assets/js/dashboard_script.js"></script>
    <script src="assets/js/fleet_manager_dashboard_script.js"></script>
</body>

</html>