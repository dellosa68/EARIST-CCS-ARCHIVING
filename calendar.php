<?php
// Set the timezone
date_default_timezone_set('Asia/Manila');

// Get the current year and month, or use provided parameters
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$day = isset($_GET['day']) ? intval($_GET['day']) : 0;

// Get the first and last day of the month
$firstDayOfMonth = date('Y-m-01', strtotime("$year-$month-01"));
$lastDayOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

// Get the day of the week for the first day of the month
$firstDayOfWeek = date('w', strtotime($firstDayOfMonth));

// Get the number of days in the month
$daysInMonth = date('t', strtotime($firstDayOfMonth));

// Generate the months and years for the dropdowns
$months = array(
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
);
$currentYear = date('Y');
$years = range($currentYear - 5, $currentYear + 5);

// Example events (You should replace this with actual database retrieval)
$events = [
    '2024-08-15' => ['Event Name' => 'Sample Event', 'Time' => '10:00 AM', 'Content' => 'This is a detailed description of the event.'],
    // Add more events as needed
];

// Get event details if a specific day is selected
$event = isset($events["$year-$month-$day"]) ? $events["$year-$month-$day"] : null;

// Create the calendar table
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Modern Calendar</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Roboto", sans-serif;
            background: #f4f4f4;
            margin: 0;
            display: flex;
            height: 100vh;
        }
        .container {
            display: flex;
            flex: 1;
        }
        .calendar {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 50%;
            padding: 20px;
        }
        .event-details {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            width: 50%;
            padding: 20px;
            box-sizing: border-box;
            margin-left: 20px;
        }
        .calendar-header {
            background-color: #333;
            color: #ffffff;
            text-align: center;
            padding: 10px 0;
            font-size: 1.4em;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .calendar-header select {
            font-size: 1em;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #ffffff;
            color: #333;
            margin: 0 5px;
        }
        .calendar-header select:focus {
            border-color: #007bff;
            outline: none;
        }
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
        }
        .calendar-table th,
        .calendar-table td {
            padding: 12px;
            text-align: center;
            font-size: 1em;
        }
        .calendar-table th {
            background-color: #f1f1f1;
            color: #666;
            font-weight: 600;
        }
        .calendar-table td {
            border: 1px solid #ddd;
            transition: background-color 0.3s, color 0.3s;
        }
        .calendar-table td.empty {
            background-color: #f9f9f9;
        }
        .calendar-table td:hover {
            background-color: #007bff;
            color: #ffffff;
            cursor: pointer;
            border-radius: 5px;
        }
        .event-card {
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .event-card h2 {
            margin: 0 0 10px;
        }
        .event-card p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="calendar">
            <div class="calendar-header">
                <form method="get" action="">
                    <select name="month" onchange="this.form.submit()">
                        <option value="">Month</option>';
                        foreach ($months as $num => $name) {
                            echo '<option value="' . $num . '"' . ($num == $month ? ' selected' : '') . '>' . $name . '</option>';
                        }
            echo '</select>
            <select name="year" onchange="this.form.submit()">
                <option value="">Year</option>';
                foreach ($years as $yr) {
                    echo '<option value="' . $yr . '"' . ($yr == $year ? ' selected' : '') . '>' . $yr . '</option>';
                }
            echo '</select>
        </form>
        </div>
        <table class="calendar-table">
            <tr>
                <th>Sun</th>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
            </tr>
            <tr>';

for ($i = 0; $i < $firstDayOfWeek; $i++) {
    echo '<td class="empty"></td>';
}

for ($day = 1; $day <= $daysInMonth; $day++) {
    if (($day + $firstDayOfWeek - 1) % 7 == 0 && $day != 1) {
        echo '</tr><tr>';
    }
    $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
    $link = $day == $day ? '?year=' . $year . '&month=' . $month . '&day=' . $day : '';
    echo '<td' . ($day == $day ? ' style="background-color: #e0f7fa;"' : '') . '><a href="' . $link . '" style="text-decoration: none; color: inherit;">' . $day . '</a></td>';
}

while (($day + $firstDayOfWeek - 1) % 7 != 0) {
    echo '<td class="empty"></td>';
    $day++;
}

echo '        </tr>
        </table>
    </div>
    <div class="event-details">';
    if ($event) {
        echo '<div class="event-card">
            <h2>' . htmlspecialchars($event['Event Name']) . '</h2>
            <p><strong>Time:</strong> ' . htmlspecialchars($event['Time']) . '</p>
            <p><strong>Content:</strong> ' . htmlspecialchars($event['Content']) . '</p>
        </div>';
    } else {
        echo '<p>Select a date to see event details.</p>';
    }
echo '  </div>
    </div>
</body>
</html>';
?>
