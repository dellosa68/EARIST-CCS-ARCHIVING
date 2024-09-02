<?php
session_start(); // Start the session

// Function to display an access denied message
function displayAccessDenied() {
    echo "<script>alert('Access Denied. Please log in.');</script>";
}

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    displayAccessDenied();
    header("Location: ../index.php");
    exit();
}

// Get the username and profile picture from the session
$username = $_SESSION["username"];
$profile_pic = isset($_SESSION["profile_pic"]) ? $_SESSION["profile_pic"] : null;

// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

if (isset($_POST['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: ../index.php");
    exit();
}

include "../conn/db_conn.php";

// Initialize $instructor variable
$instructor = null;

if (isset($_GET['account_id'])) {
    $account_id = $_GET['account_id'];

    // Fetch instructor details based on account_id
    $sql = "SELECT fname, lname, description, pic, role FROM instructors WHERE account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $instructor = $result->fetch_assoc();
        $profile_pic_base64 = !empty($instructor['pic']) ? base64_encode($instructor['pic']) : '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <title>Instructor Details</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
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
        #sidebar.minimized a .text {
            display: none;
        }
        .main-content {
            transition: margin-left 0.5s;
        }
        .header {
           margin-top: 160px;
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
            background-color: white;
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
        }
        .profile {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
        }
        .profile .info {
            flex-grow: 1;
        }
        .tabs {
            margin-bottom: 20px;
        }
        .tabs a {
            padding: 10px 20px;
            display: inline-block;
            text-decoration: none;
            color: #d32f2f;
            border-bottom: 2px solid transparent;
            margin-right: 10px;
        }
        .tabs a.active {
            border-bottom: 2px solid #d32f2f;
        }
        .content {
            display: none;
        }
        .content.active {
            display: block;
        }
        .documents {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .document {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px;
            text-align: center;
            transition: box-shadow 0.3s, transform 0.3s;
            max-height: 320px; /* Set a maximum height for the document card */
            overflow: hidden; /* Hide overflow */
        }

        .document:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .thumbnail {
            width: 100%;
            height: 200px;
            background-color: #e0e0e0;
            margin-bottom: 10px;
            border-radius: 5px;
            background-size: cover;
            background-position: center;
        }

        .title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            white-space: nowrap; /* Prevent text from wrapping */
            overflow: hidden; /* Hide overflow */
            text-overflow: ellipsis; /* Add ellipsis for overflowing text */
        }

        .year {
            font-size: 14px;
            color: #777;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            color: black;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
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
                        <a href="#">Settings</a>
                    </div>
        <div class="profile">
            <?php if ($profile_pic_base64): ?>
                <a href="personal_details.php">
                    <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
                </a>
            <?php else: ?>
                <a href="personal_details.php">
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
            <div class="title">
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="home.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="instructors.php">Instructors</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo isset($instructor) ? htmlspecialchars($instructor['fname'] . ' ' . $instructor['lname']) : 'Unknown'; ?></li>
                </ol>
            </nav>
        </div>
        <div class="container">
            <?php
            if ($instructor) {
                $profile_pic_src = $profile_pic_base64 ? 'data:image/jpeg;base64,' . $profile_pic_base64 : 'https://via.placeholder.com/100';

                echo '<div class="profile">';
                echo '<img src="' . $profile_pic_src . '" alt="Instructor Photo">';
                echo '<div class="info">';
                echo '<h2>' . htmlspecialchars($instructor['fname'] . ' ' . $instructor['lname']) . '</h2>';
                echo '<p>' . htmlspecialchars($instructor['role']) . '</p>';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<p>No instructor found.</p>';
            }
            ?>
            <div class="tabs">
                <a href="#works" class="active">Works</a>
            </div>
            <div id="works"class="content active">
                <div class="adviser-works">
                    <h4>Works as Adviser</h4>
                    <div class="documents">
                        <?php
                        if (isset($account_id)) {
                            // Fetch works where this instructor is an adviser
                            $sql_adviser = "SELECT title, year, cover FROM document WHERE adviser_id = ?";
                            $stmt_adviser = $conn->prepare($sql_adviser);
                            $stmt_adviser->bind_param("i", $account_id);
                            $stmt_adviser->execute();
                            $result_adviser = $stmt_adviser->get_result();

                            if ($result_adviser->num_rows > 0) {
                                while ($row = $result_adviser->fetch_assoc()) {
                                    $cover_image_base64 = !empty($row['cover']) ? base64_encode($row['cover']) : '';
                                    $cover_image_src = $cover_image_base64 ? 'data:image/jpeg;base64,' . $cover_image_base64 : 'https://via.placeholder.com/80';

                                    echo '<a href="#" title="' . htmlspecialchars($row['title']) . '">';
                                    echo '<div class="document" title="' . htmlspecialchars($row['title']) . '">';
                                    echo '<div class="thumbnail" style="background-image: url(\'' . $cover_image_src . '\');"></div>';
                                    echo '<p class="title" title="' . htmlspecialchars($row['title']) . '">' . htmlspecialchars($row['title']) . '</p>';
                                    echo '<p class="year">' . htmlspecialchars($row['year']) . '</p>';
                                    echo '</div>';
                                    echo '</a>';
                                }
                            } else {
                                echo '<p>No works as Adviser found.</p>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
    <script>
        document.querySelectorAll('.tabs a').forEach(tab => {
            tab.addEventListener('click', function(event) {
                event.preventDefault();
                document.querySelectorAll('.tabs a').forEach(a => a.classList.remove('active'));
                document.querySelectorAll('.content').forEach(content => content.classList.remove('active'));
                this.classList.add('active');
                document.querySelector(this.getAttribute('href')).classList.add('active');
            });
        });

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
    </script>
</body>
</html>
