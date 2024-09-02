<?php
session_start(); // Start the session
include "../conn/db_conn.php";

function displayAccessDenied() {
    echo "<script>alert('Access Denied. Please log in.');</script>";
}

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    displayAccessDenied();
    header("Location: ../admin.php");
    exit();
}

$username = $_SESSION["username"];
$profile_pic = isset($_SESSION["profile_pic"]) ? $_SESSION["profile_pic"] : null;
$account_id = $_SESSION["account_id"];
// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

// Query to fetch published documents authored by the logged-in user
$query_published = "SELECT title, author, year, research_id FROM document WHERE author_id = ?";
$stmt_published = $conn->prepare($query_published);
$stmt_published->bind_param("i", $student_id);
$stmt_published->execute();
$result = $stmt_published->get_result();

$query_unreleased = "SELECT * FROM unreleased_research WHERE plagscan_cert IS NOT NULL AND grammarian_cert IS NOT NULL AND statistician_cert IS NOT NULL";
$result_unreleased = $conn->query($query_unreleased);

$query_authors = "SELECT COUNT(account_id) as total_authors FROM student";
$result_authors = $conn->query($query_authors);
$total_authors = $result_authors->fetch_assoc()['total_authors'];

$query_bscs_authors = "SELECT COUNT(account_id) as bscs_authors FROM student WHERE course = 'Bachelor of Science in Computer Science'";
$result_bscs_authors = $conn->query($query_bscs_authors);
$bscs_authors = $result_bscs_authors->fetch_assoc()['bscs_authors'];

// Query to fetch the number of BSIT authors
$query_bsit_authors = "SELECT COUNT(account_id) as bsit_authors FROM student WHERE course = 'BSIT'";
$result_bsit_authors = $conn->query($query_bsit_authors);
$bsit_authors = $result_bsit_authors->fetch_assoc()['bsit_authors'];

// Query to fetch the number of instructors
$query_instructors = "SELECT COUNT(account_id) as total_instructors FROM instructors"; // Adjust table and column names as needed
$result_instructors = $conn->query($query_instructors);
$total_instructors = $result_instructors->fetch_assoc()['total_instructors'];
$query_documents = "SELECT COUNT(research_id) as total_documents FROM document";
$result_documents = $conn->query($query_documents);
$total_documents = $result_documents->fetch_assoc()['total_documents'];

$query_events = "SELECT event_id, title, event_date, event_time, description FROM events";
$result_events = $conn->query($query_events);

$stmt_published->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.css">
  <style>
    /* CSS to style the toggled side panel */
    #sidebar {
      height: 100%;
      width: 50px;
      position: fixed;
      z-index: 1;
      top: 0;
      left: 0;
      background-color: #111;
      overflow-x: hidden;
      transition: 0.5s;
    }

    #sidebar a {
      padding: 10px 15px;
      text-decoration: none;
      font-size: 20px;
      color: #818181;
      display: block;
      transition: 0.3s;
    }

    #sidebar a:hover {
      color: #FF7575;
    }

    #sidebar .close-btn {
      position: absolute;
      top: 0;
      right: 25px;
      font-size: 36px;
      margin-left: 50px;
      color: #fff;
    }

    #main-content {
      transition: margin-left 0.5s;
      margin-left: 50px;
    }
    .container {
        margin-bottom: 50px;
    }

    .open-btn {
      border: none;
      color: white;
      padding: 10px 20px;
      font-size: 16px;
      background-color: transparent; /* Set background color to transparent */
    }

    .open-btn:hover {
        background-color: #FF7575; /* Add background color on hover */
    }

    .admin-info {
        padding: 20px;
        text-align: center;
        display: none;
    }

    .admin-info img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin-bottom: 10px;
    }

    /* Hide text when sidebar width is 50px */
    #sidebar.minimized a .text {
        display: none;
    }

    .card {
        margin-top: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

     .card-title {
        font-size: 2rem;
        color: #ffffff;
    }

     .card-icon {
        font-size: 5rem;
        color: #ffffff;
    }

    .card-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
    }

    .total-count {
        font-size: 3rem;
        font-weight: bold;
        color: #ffffff;
    }


    .navbar {
        padding: 10px 20px;
        position: block;
        width: 100%;
        top: 0;
        z-index: 1;
        transition: background-color 0.3s;
        background-color: rgba(0, 0, 0, 0.8);
    }

    .navbar.scrolled {
        background-color: rgba(0, 0, 0, 0.8); /* Background color when scrolled */
    }

    .navbar .logo {
        display: flex;
        align-items: center;
    }

    .navbar .logo img {
        height: 60px; /* Increased the height */
        margin-right: 10px;
    }

    .navbar .logo h2 {
        color: white;
        margin: 0;
        font-family: 'Times New Roman', serif;
    }

    .navbar .profile {
        display: flex;
        align-items: center;
        margin-left: auto;
    }

    .navbar .profile img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
    }

    .navbar .profile p {
        color: #fff;
        margin: 0;
    }

    .navbar .profile form {
        margin: 0;
    }

    footer {
        width: 100%;
        background-color: #333;
        color: #fff;
        padding: 20px 0;
        text-align: center;
        font-family: 'Times New Roman', serif;
    }

    .button-group-container {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .btn-group {
        width: 225px; /* Set the width to 200px */
        height: 225px; /* Set the height to 200px */
        display: grid;
        grid-template-columns: 1fr 1fr; /* Create a 2x2 grid */
        grid-gap: 10px; /* Add space between buttons */
    }

    .btn-group .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%; /* Ensure buttons take full width of grid cell */
        height: 100%; /* Ensure buttons take full height of grid cell */
        padding: 0; 
    }
    .popup {
        display: none; /* Hidden by default */
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5); /* Black background with opacity */
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .popup-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        width: 300px;
    }

    .popup-content h3 {
        margin-bottom: 20px;
    }

    .popup-content button {
        margin: 5px;
    }

    .popup-content .close {
        cursor: pointer;
        color: #FF7575;
        font-size: 20px;
        float: right;
    }
    .calendar-section {
    display: flex;
    flex-direction: row;
    height: 600px; /* Increased height for a better view */
    margin-top: 20px;
    border-radius: 10px;
    overflow: hidden; /* Ensure content doesn't overflow */
}

.calendar-container {
    flex: 2;
    border-right: 2px solid #ddd;
    padding: 20px;
    background: #f9f9f9; /* Light background for contrast */
    border-radius: 10px 0 0 10px; /* Rounded corners on the left side */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
}

.content-container {
    flex: 1;
    padding: 20px;
    background: #fff; /* White background for the content */
    border-radius: 0 10px 10px 0; /* Rounded corners on the right side */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    overflow-y: auto;
}


#event-details {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    background: #fff;
}
.event-list {
        margin-top: 20px;
    }
    .event-item {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .event-title {
        font-size: 1.1rem;
        font-weight: bold;
        color: #333;
    }
    .event-time {
        color: #6c757d;
    }
    .event-days {
        color: #007bff;
        font-weight: bold;
        font-size: xx-large;
    }
    .event-item:hover {
        background-color: #f1f1f1;
    }
  </style>
</head>
<body>

<div id="sidebar" class="minimized">
    <button class="open-btn" onclick="toggleNav()"><i class="fas fa-bars"></i></button>
    <div class="admin-info">
        <?php if ($profile_pic_base64): ?>
                <a href="personal_details.php">
                    <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
                </a>
            <?php else: ?>
                <a href="personal_details.php">
                    <img src="../images/default_profile.jpg" alt="Default Profile Picture">
                </a>
            <?php endif; ?>
      <h3 style="color: white;"><?php echo htmlspecialchars($username); ?></h3>ss
    </div>
    <a href="home.php" class="minimized"><i class="fas fa-home"></i><span class="text">   Dashboard</span></a>
    <a href="users.php" class="minimized"><i class="fas fa-user"></i><span class="text">   User</span></a>
    <a href="research.php" class="minimized"><i class="fas fa-file-alt"></i><span class="text">    Research Documents</span></a>
    <a href="research_status.php" class="minimized"><i class="fa fa-dashboard"></i><span class="text">   Consultation</span></a>
    <a href="calendar.php" class="minimized"><i class="fa fa-calendar"></i><span class="text">   Settings</span></a>
    <a href="reports.php" class="minimized"><i class="fas fa-chart-bar"></i><span class="text">   Reports</span></a>
</div>

<div id="main-content">
    <nav class="navbar" id="navbar">
        <div class="logo">
          <img src="../images/ccs.png" alt="Logo">
          <h2>EARIST - College of Computing Studies Research Archiving System</h2>
        </div>
        <div class="profile">
          <?php if ($profile_pic_base64): ?>
            <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
          <?php else: ?>
            <img src="../images/default_profile.jpg" alt="Default Profile Picture">
          <?php endif; ?>
          <form method="post" action="conn/logout.php">
            <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
          </form>
        </div>
    </nav>
    <div class="container">
        <h1 class="mt-5">Dashboard</h1>
        <div class="row">
            <div class="col-md-12">
            <div class="card" style="flex-direction: row; padding-bottom: 20px;">
                <div class="col-md-3">
                    <a href="users.php">
                <div class="card  bg-info">
                    <div class="card-body">
                        <div>
                            <h5 class="card-title">Authors</h5>
                            <p class="total-count" id="totalAuthors"><?php echo $total_authors; ?></p>
                        </div>
                        <i class="card-icon fas fa-user"></i>
                    </div>
                </div>
            </a>
            </div>
            <div class="col-md-3">
                <a href="users.php">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div>
                            <h3 class="card-title">Instructors</h3>
                            <p class="total-count" id="totalInstructors"><?php echo $total_instructors; ?></p>
                        </div>
                        <i class="card-icon fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
            </a>
            </div>
            <div class="col-md-3">
                <a href="research.php">
                <div class="card bg-warning">
                    <div class="card-body">
                        <div>
                            <h5 class="card-title">Documents</h5>
                            <p class="total-count" id="totalInstructors"><?php echo $total_documents; ?></p>
                        </div>
                        <i class="card-icon fas fa-file-alt"></i>
                    </div>
                </div>
            </a>
            </div>
            <div class="col-md-3">
                <div class="row button-group-container">
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <button class="btn btn-primary" onclick="showPopup()">
                        <i class="fas fa-user-plus fa-3x"></i><br>
                        </button>
                        <a href="old_research.php" class="btn btn-secondary">
                            <i class="fas fa-upload fa-3x"></i><br>
                        </a>
                        <a href="new_research.php" class="btn btn-success">
                            <i class="fas fa-certificate fa-3x"></i><br>
                        </a>
                        <a href="reports.php" class="btn btn-danger">
                            <i class="fas fa-chart-bar fa-3x"></i><br>
                        </a>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>
        <div class="calendar-section">
            <div class="calendar-container">
                <h3>Calendar</h3>
                <div id="calendar"></div> <!-- Calendar will be rendered here -->
            </div>
            <div class="content-container">
                <h3>Upcoming Events</h3>
                <input type="hidden" id="selected-date" readonly />
                <div class="event-list" id="eventList">
                     <?php if ($result_events->num_rows > 0): ?>
                        <?php while ($row = $result_events->fetch_assoc()): ?>
                            <?php
                                $event_date = new DateTime($row['event_date']);
                                $current_date = new DateTime();
                                $interval = $current_date->diff($event_date);
                                $days_left = $interval->format('%a');
                            ?>
                            <div class="event-item">
                                <div>
                                    <div class="event-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                    <div class="event-time"><?php echo htmlspecialchars($row['event_time']); ?></div>
                                </div>
                                <div class="event-days"><?php echo $days_left; ?></div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-center">No events found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        
    </div>
</div>
<footer>
    &copy; 2024 Your Company Name. All rights reserved.
</footer>
<!-- Popup HTML -->
<div id="popup" class="popup">
    <div class="popup-content">
        <span class="close" onclick="hidePopup()">&times;</span>
        <h3>Select User Type</h3>
        <a href="add_author.php"><button class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Author
        </button></a>
        <a href="add_instructor.php"><button class="btn btn-secondary">
            <i class="fas fa-chalkboard-teacher"></i> Instructor
        </button></a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.css">

  <!-- FullCalendar JavaScript -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.js"></script>
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script>
  // JavaScript to control the toggling of side panel
  function toggleNav() {
    var sidebarWidth = document.getElementById("sidebar").style.width;
    var sidebar = document.getElementById("sidebar");
    if (sidebarWidth === '50px') {
      // If the side panel is open, close it
      sidebar.style.width = "250px";
      document.getElementById("main-content").style.marginLeft = "250px";
      document.querySelector(".admin-info").style.display = "block";
      sidebar.classList.remove('minimized');
    } else {
      // If the side panel is closed, open it
      sidebar.style.width = "50px";
      document.getElementById("main-content").style.marginLeft = "50px";
      document.querySelector(".admin-info").style.display = "none"; 
      sidebar.classList.add('minimized');
    }
  }

  // JavaScript to show and hide the popup
  function showPopup() {
    document.getElementById('popup').style.display = 'flex';
  }

  function hidePopup() {
    document.getElementById('popup').style.display = 'none';
  }

  function selectUserType(type) {
    if (type === 'Author') {
        window.location.href = 'add_author.php';
    } else if (type === 'Instructor') {
        window.location.href = 'add_instructor.php'; // Adjust as necessary
    } else if (type === 'Admin') {
        window.location.href = 'add_admin.php'; // Adjust as necessary
    }
    hidePopup();
  }

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var eventListEl = document.getElementById('eventList');
    var events = []; // Array to store fetched events

    var calendar = new FullCalendar.Calendar(calendarEl, {
        themeSystem: 'bootstrap',
        initialView: 'dayGridMonth',
        editable: true,
        selectable: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch('conn/fetch_events.php')
                .then(response => response.json())
                .then(data => {
                    events = data; // Store events in the array
                    successCallback(data);
                })
                .catch(error => failureCallback(error));
        },
        dateClick: function(info) {
            console.log('Clicked date:', info.dateStr);
            var dateInput = document.getElementById('selected-date');
            if (dateInput) {
                dateInput.value = info.dateStr;
            } else {
                console.error('Input field not found');
            }
            filterEvents(info.dateStr);
        },
        eventContent: function(arg) {
            return {
                html: `<div style="background-color: #FF7575; color: white; padding: 5px; border-radius: 5px;">${arg.event.title}</div>`
            }
        },
        eventDidMount: function(info) {
            info.el.style.backgroundColor = '#FF7575'; // Custom color for events
            info.el.style.color = '#fff';
            info.el.style.borderRadius = '5px';
        }
    });
    calendar.render();

    function filterEvents(date) {
        // Clear current events
        eventListEl.innerHTML = '';

        // Filter events based on the selected date
        var filteredEvents = events.filter(event => event.start === date); // Adjust 'start' to match your event date field
        if (filteredEvents.length === 0) {
            eventListEl.innerHTML = '<p class="text-center">No events found for this date.</p>';
        } else {
            filteredEvents.forEach(event => {
                var eventItem = document.createElement('div');
                eventItem.classList.add('event-item');
                eventItem.innerHTML = `
                    <div>
                        <div class="event-title">${event.title}</div>
                        <div class="event-time">${event.event_time}</div>
                    </div>
                    <div class="event-days">${getDaysLeft(event.start)}</div>
                `;
                eventListEl.appendChild(eventItem);
            });
        }
    }

    function getDaysLeft(eventDate) {
        var eventDateTime = new Date(eventDate);
        var currentDateTime = new Date();
        var timeDifference = eventDateTime - currentDateTime;
        var daysLeft = Math.ceil(timeDifference / (1000 * 3600 * 24));
        return daysLeft;
    }
});

</script>

</body>
</html>
