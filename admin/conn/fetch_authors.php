<?php
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Include database connection
include "../../conn/db_conn.php";

// Check if the connection is established
if (!$conn) {
    echo json_encode(['error' => 'Failed to connect to database']);
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    echo json_encode(['error' => 'Access Denied']);
    exit();
}

$course_filter = isset($_GET['course']) ? mysqli_real_escape_string($conn, $_GET['course']) : '';

// Modify the query to include the course filter
$query = "SELECT * FROM student";
if ($course_filter) {
    $query .= " WHERE course = '$course_filter'";
}

$author_result = mysqli_query($conn, $query);

if (!$author_result) {
    echo json_encode(['error' => 'Database query failed']);
    exit();
}

$authors = [];
while ($row = mysqli_fetch_assoc($author_result)) {
    $authors[] = $row;
}

echo json_encode($authors);
?>
