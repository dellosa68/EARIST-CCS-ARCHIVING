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

$query_unreleased = "SELECT * FROM unreleased_research WHERE plagscan_cert IS NOT NULL AND grammarian_cert IS NOT NULL AND statistician_cert IS NOT NULL";
$result_unreleased = $conn->query($query_unreleased);


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Research</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" rel="stylesheet">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.6.0/jszip.min.js"></script>

   <style>
    body {
      font-family: 'Roboto', Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    #main-content {
      transition: margin-left 0.5s;
      margin-left: 50px;
    }

    h1 {
      text-align: center;
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

    .open-btn {
      border: none;
      color: white;
      padding: 10px 20px;
      font-size: 16px;
      background-color: transparent;
      /* Set background color to transparent */
    }

    .open-btn:hover {
      background-color: #FF7575;
      /* Add background color on hover */
    }

    .header {
      height: 130px;
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

    .admin-info h3 {
      color: #fff;
      margin-bottom: 0;
    }

    #sidebar.minimized a .text {
      display: none;
    }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            position: relative;
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
        .navbar .menu {
            display: flex;
            align-items: center;
        }
        .navbar .menu a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            transition: color 0.3s;
        }
        .navbar .menu a:hover {
            color: #FF7575;
        }
        .navbar .profile {
            display: flex;
            align-items: center;
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
        footer {
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            font-family: 'Times New Roman', serif;
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
                <a href="profile.php">
                    <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
                </a>
            <?php else: ?>
                <a href="profile.php">
                    <img src="../images/default_profile.jpg" alt="Default Profile Picture">
                </a>
            <?php endif; ?>

            <form method="post" style="margin-left: 10px;">
                <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
            </form>
        </div>
    </nav>
        <div class="container">
         <div class="tab">
            <button class="tablinks" onclick="openTab(event, 'ForApproval')" id="defaultOpen">For Approval</button>
        </div>
        <div id="ForApproval" class="tabcontent">
            <h3>Research Papers for Approval</h3>
            <ul class="work-list">
                <?php while ($row = $result_unreleased->fetch_assoc()): ?>
                    <li>
                        <a href="approval.php?research_id=<?php echo $row['id']; ?>">
                            <div class="content">
                                <span class="title"><?php echo htmlspecialchars($row['title']); ?></span>
                                <br>
                                <small>by: <?php echo htmlspecialchars($row['author']); ?></small>
                            </div>
                        </a>
                            <span class="status pending">For Approval</span>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
    
    </div>

    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
    window.onload = function() {
        document.getElementById("defaultOpen").click();
    };

    function submitResearch() {
        window.location.href = 'submit_research.php';
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
        if (evt != null) {
            evt.currentTarget.className += " active";
        } else {
            document.getElementById("defaultOpen").className += " active";
        }
    }

    function toggleNav() {
        var sidebar = document.getElementById("sidebar");
        if (sidebar.style.width === '50px') {
            sidebar.style.width = "250px";
            document.getElementById("main-content").style.marginLeft = "250px";
            document.querySelector(".admin-info").style.display = "block";
            sidebar.classList.remove('minimized');
        } else {
            sidebar.style.width = "50px";
            document.getElementById("main-content").style.marginLeft = "50px";
            document.querySelector(".admin-info").style.display = "none";
            sidebar.classList.add('minimized');
        }
    }
</script>


</body>
</html>
