<?php
// db_connect.php
$servername = "localhost";
$username = "root"; // Your database username
$password = "";     // Your database password
$dbname = "tracking_system"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>