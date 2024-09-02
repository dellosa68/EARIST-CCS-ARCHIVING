<?php
// get_data.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = [];

// Fetch student data
$sql = "SELECT student_id, CONCAT(first_name, ' ', last_name) AS fullname FROM student";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data['student'][] = $row;
    }
}

// Fetch instructors data
$sql = "SELECT adviser_id, pic, CONCAT(fname, ' ', lname) AS fullname FROM instructors";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data['instructors'][] = $row;
    }
}

$sql = "SELECT panel_id, pic, CONCAT(fname, ' ', lname) AS fullname FROM instructors";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data['instructors'][] = $row;
    }
}

$conn->close();

echo json_encode($data);
?>
