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
$student_id = $_SESSION["student_id"];
// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

// Query to fetch published documents authored by the logged-in user
$query_published = "SELECT title, author, year, research_id FROM document WHERE author_id = ?";
$stmt_published = $conn->prepare($query_published);
$stmt_published->bind_param("i", $student_id);
$stmt_published->execute();
$result = $stmt_published->get_result();

// Query to fetch unpublished documents authored by the logged-in user
$query_unreleased = "SELECT * FROM unreleased_research WHERE author_id LIKE '%$student_id%'";
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
       
        .header {
            color: white;
            padding: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            height: 120px;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            position: fixed;
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
    </style>
</head>
<body>
    <nav class="navbar" id="navbar">
        <div class="logo">
            <img src="../images/ccs.png" alt="Logo">
            <h2>EARIST - College of Computing Studies Research Archiving System</h2>
        </div>
        <div class="menu">
            <a href="home.php">Home</a>
            <a href="profile.php">Profile</a>
            <a href="talk.php">Talk</a>
            <a href="research.php">Documents</a>
            <a href="publish_research.php">Publish a Research</a>
            <a href="bookmarks.php">Bookmarks</a>
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
    <div class="main-content" id="main-content">
        <div class="header">
            
        </div>

    <div class="container">
        <h1>Publish a Reasearch</h1>
         <div class="tab">
            <button class="tablinks active" onclick="openTab(event, 'published')">Published</button>
            <button class="tablinks" onclick="openTab(event, 'pending')">Pending</button>
        </div>

        <div id="published" class="tabcontent" style="display: block;">
            <h3>Published Research</h3>
            <ul class="work-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li>
                        <a href="research_details.php?research_id=<?php echo $row['research_id']; ?>">
                            <div class="content">
                                <span class="title"><?php echo htmlspecialchars($row['title']); ?></span>
                                <br>
                                <small>by: <?php echo htmlspecialchars($row['author']); ?> • <?php echo htmlspecialchars($row['year']); ?></small>
                            </div>
                        </a>
                            <span class="status published">Published</span>
                        
                    </li>
                <?php endwhile; ?>
            </ul>
            <button class="btn-submit" onclick="submitResearch()">Submit a New Research</button>
        </div>
        <!-- Pending Tab Content -->
        <div id="pending" class="tabcontent">
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
<footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
    <script>
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
            evt.currentTarget.className += " active";
        }
    </script>
</body>
</html>
