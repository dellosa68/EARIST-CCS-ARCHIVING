<?php
session_start(); // Start the session
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

// Query to fetch published documents authored by the logged-in user
$query_published = "SELECT title, author, year, research_id FROM document WHERE panel_id = ?";
$stmt_published = $conn->prepare($query_published);
$stmt_published->bind_param("i", $account_id);
$stmt_published->execute();
$result = $stmt_published->get_result();

// Query to fetch unpublished documents authored by the logged-in user
$query_unreleased = "SELECT * FROM unreleased_research WHERE panel_id LIKE '%$account_id%'";
$stmt_unreleased = $conn->prepare($query_unreleased);
$stmt_unreleased->execute();
$result_unreleased = $stmt_unreleased->get_result();

// Close the statements after fetching results
$stmt_published->close();
$stmt_unreleased->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Status</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
     <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
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
            font-size: 15px;
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
        .open-btn {
            border: none;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            background-color: transparent;
        }
        .open-btn:hover {
            background-color: #FF7575;
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
        .admin-info p {
            color: #fff;
            margin-bottom: 0;
        }
        #sidebar.minimized a .text {
            display: none;
        }
        .main-content {
            transition: margin-left 0.5s;
            margin-left: 50px;
        }
        .header {
            background-color: #d32f2f;
            color: white;
            padding: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }
        .header img {
            height: 60px;
            margin-right: 20px;
        }
        .header .title h3 {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            margin: 0;
        }
        .container {
            width: 100%;
            max-width: 95%;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
        }
         h3 {
            text-align: center;
            color: #333;
        }
        
        .btn-submit {
            display: block;
            margin: 60px auto 0;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
    .icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 10px; /* Adjusted right position */
            color: #888;
        }
        .icon i {
            font-size: 10px;
        }
        footer {
            margin-top: 50px;
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
        .header, .sidebar {
            font-family: 'Roboto', sans-serif;
        }
        .header h3, .sidebar a {
            font-weight: 700; /* Bold font weight */
        }
        /* Container and Content */
        .container, .content {
            font-family: 'Roboto', Arial, sans-serif;
        }
        h1, h3, .title, .status, .btn-submit {
            font-weight: 700; /* Bold font weight */
        }
         .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
            border-radius: 5px;
        }
        .tab {
    overflow: hidden;
    border-radius: 5px;
    background-color: #f4f4f4; /* Background color */
    margin-bottom: 20px; /* Added margin */
}

.tab button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 10px 20px; /* Adjusted padding */
    transition: 0.3s;
    font-size: 16px;
    border-radius: 5px 5px 0 0; /* Rounded corners for the top */
    border: 1px solid #ccc; /* Border */
}

.tab button:hover {
    background-color: #ddd;
}

.tab button.active {
    background-color: #fff; /* Active tab background color */
    border-bottom: 1px solid #fff; /* White border for active tab */
}

.tabcontent {
    display: none;
    padding: 20px; /* Increased padding */
    border-top: none;
    background-color: #fff; /* Background color */
    border-radius: 0 5px 5px 5px; /* Rounded corners for the bottom */
    border: 1px solid #ccc; /* Border */
}
        .work-list {
            list-style-type: none;
            padding: 0;
            margin-top: 50px;
        }
        .work-list li {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .title, .status {
            font-size: 18px;
            font-weight: bold;
        }
        .status {
            padding: 5px 10px;
            border-radius: 10px;
            text-transform: uppercase;
        }
        .published {
            background-color: #4caf50;
            color: #fff;
        }
        .pending {
            background-color: #ffc107;
            color: #333;
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
     #main-content {
      transition: margin-left 0.5s;
      margin-left: 50px;
    }
    </style>
</head>
<body>
<div id="sidebar" class="minimized">
    <button class="open-btn" onclick="toggleNav()"><i class="fas fa-bars"></i></button>
    <div class="admin-info">
      <?php if ($profile_pic_base64): ?>
            <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
          <?php else: ?>
            <img src="../images/default_profile.jpg" alt="Default Profile Picture">
          <?php endif; ?>
      <h3 style="color: white;"><?php echo htmlspecialchars($username); ?></h3>
    </div>
    <a href="home.php" class="minimized"><i class="fas fa-home"></i><span class="text">   Dashboard</span></a>
    <a href="profile.php?=account_id=<?php echo $account_id; ?>" class="minimized"><i class="fas fa-user"></i><span class="text">   User</span></a>
    <a href="research_list.php" class="minimized"><i class="fas fa-file-alt"></i><span class="text">    Research Documents</span></a>
    <a href="research.php" class="minimized"><i class="fa fa-dashboard"></i><span class="text">   Consultation</span></a>
    <a href="calendar.php" class="minimized"><i class="fa fa-calendar"></i><span class="text">   Settings</span></a>
    <a href="add_author.php" class="minimized"><i class="fas fa-user-plus"></i><span class="text">   Add Author</span></a>
    <a href="add_research.php" class="minimized"><i class="fas fa-upload"></i><span class="text">   Add Research</span></a>
    <a href="bookmarks.php" class="minimized"><i class="fas fa-bookmark "></i><span class="text">   Bookmarks</span></a>
    <a href="reports.php" class="minimized"><i class="fas fa-chart-bar "></i><span class="text">   Reports</span></a>
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
          <form method="post">
            <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
          </form>
        </div>
    </nav>

    <div class="container">
        <h1>Publish a Reasearch</h1>
         <div class="tab">
            <button class="tablinks active" onclick="openTab(event, 'pending')">Pending</button>
        </div>

        
        <!-- Pending Tab Content -->
        <div id="pending" class="tabcontent" style="display: block;">
            <h3>Pending Research</h3>
                <ul class="work-list">
                    <!-- Pending research list -->
                    <?php while ($row_unreleased = $result_unreleased->fetch_assoc()): ?>
                        <li>
                            <a href="unpublish_research_details.php?research_id=<?php echo $row_unreleased['id']; ?>">
                                <div class="content">
                                    <span class="title"><?php echo htmlspecialchars($row_unreleased['title']); ?></span>
                                    <br>
                                    <small>by: <?php echo htmlspecialchars($row_unreleased['author']); ?></small>
                                </div>
                            </a>
                            <span class="status pending">Pending</span>
                        </li>
                    <?php endwhile; ?>
                </ul>
        </div>
    </div> 
</div>
    <script>
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
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
</body>
</html>
