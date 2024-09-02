<?php
// Database connection
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'research';

$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>