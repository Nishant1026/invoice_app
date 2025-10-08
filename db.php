<?php
$servername = "localhost";  // Change if necessary
$username = "root";         // Default for XAMPP
$password = "";             // Default for XAMPP
$dbname = "invoice_app";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
