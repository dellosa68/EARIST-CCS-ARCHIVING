<?php
session_start();
include "../../conn/db_conn.php";

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    echo json_encode([]);
    exit();
}

// Query to fetch all events
$query_events = "SELECT event_id, title, event_date, event_time, description FROM events";

$result_events = $conn->query($query_events);

if (!$result_events) {
    // Output error message if query fails
    $error = $conn->error;
    echo json_encode(['error' => "Query failed: $error"]);
    exit();
}

$events = [];

if ($result_events->num_rows > 0) {
    while ($row = $result_events->fetch_assoc()) {
        $events[] = [
            'id' => $row['event_id'],
            'title' => $row['title'],
            'start' => $row['event_date'], // FullCalendar expects start as ISO 8601 date format
            'end' => $row['event_date'], // If you have an end date, add it here
            'description' => $row['description']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($events);

$result_events->close();
$conn->close();
?>
