<?php
// Database configuration
$servername = "localhost";
$username = "root"; // Change to your MySQL username
$password = ""; // Change to your MySQL password
$dbname = "spice_and_surprise";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Function to sanitize input
function sanitize_input($data, $conn) {
    return htmlspecialchars(stripslashes(trim($conn->real_escape_string($data))));
}

// Close connection function (optional)
function close_db_connection() {
    global $conn;
    if (isset($conn)) {
        $conn->close();
    }
}

// Register shutdown function
register_shutdown_function('close_db_connection');
?>