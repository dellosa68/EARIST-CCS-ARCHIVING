<?php
include "../conn/db_conn.php";

function displayAccessDenied() {
    echo "<script>alert('Access Denied. Please log in.');</script>";
}

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    displayAccessDenied();
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION["username"];
$profile_pic = isset($_SESSION["profile_pic"]) ? $_SESSION["profile_pic"] : null;
$account_id = $_SESSION["account_id"];
// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

// Handle form submission to save event
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_event'])) {
    $title = $_POST['title'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $description = $_POST['description'];

    // Generate a unique 8-digit event ID
    $event_id = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

    // Insert the event into the database
    $query_insert = "INSERT INTO events (event_id, title, event_date, event_time, description) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bind_param("sssss", $event_id, $title, $event_date, $event_time, $description);

    if ($stmt_insert->execute()) {
        echo "<script>alert('Event saved successfully!');</script>";
    } else {
        echo "<script>alert('Error saving event.');</script>";
    }

    $stmt_insert->close();
}

// Query to fetch events
$query_events = "SELECT event_id, title, event_date, event_time, description FROM events";
$result_events = $conn->query($query_events);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save Event</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <style>
         .container {
            margin-top: 20px;
        }
        .card {
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container, .calendar-container {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
        }
        .form-container {
            background-color: #f8f9fa;
        }
        .calendar-container {
            background-color: #ffffff;
        }
    </style>
</head>

<body>
     <div class="container">
        <div class="row">
            <div class="col-md-6 form-container">
                <h1>Save Event</h1>
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="event_date">Event Date</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required>
                            </div>
                            <div class="form-group">
                                <label for="event_time">Event Time</label>
                                <input type="time" class="form-control" id="event_time" name="event_time" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            <button type="submit" name="save_event" class="btn btn-primary">Save Event</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 calendar-container">
                <h2>Saved Events</h2>
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    <?php
                    if ($result_events->num_rows > 0) {
                        while ($row = $result_events->fetch_assoc()) {
                            echo "{";
                            echo "title: '" . htmlspecialchars($row['title']) . "',";
                            echo "start: '" . htmlspecialchars($row['event_date']) . "T" . htmlspecialchars($row['event_time']) . "',";
                            echo "description: '" . htmlspecialchars($row['description']) . "'";
                            echo "},";
                        }
                    }
                    ?>
                ],
                eventClick: function(info) {
                    alert('Event: ' + info.event.title + '\nDescription: ' + info.event.extendedProps.description);
                }
            });
            calendar.render();
        });
    </script>
</body>
