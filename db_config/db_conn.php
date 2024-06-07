<?php
// Define constants for database credentials
DEFINE('DB_USER','root');       // Username for database connection
DEFINE('DB_PASSWORD','');       // Password for database connection
DEFINE('DB_HOST','localhost');  // Database host 
DEFINE('DB_NAME','fmms_db');  // Name of the database

// Attempt to connect to the database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
    or die('Could not connect to MySQL: ' . mysqli_connect_error());
?>
