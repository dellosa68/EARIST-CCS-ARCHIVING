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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            background-image: url('../images/bg.jpg'); /* Replace 'path/to/your/image.jpg' with the actual path to your image */
            background-size: cover;
            background-position: center;
        }
        .open-btn {
            border: none;
            color: white;
            font-size: 16px;
            background-color: transparent;
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
            background-color: transparent; /* Make navbar transparent initially */
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
        .main-content {
            padding-top: 80px;
            text-align: center;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin-top: 90px;
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
            height: 400px;
            margin-right: 20px;
        }
        .header .title h3 {
            font-family: 'Times New Roman', serif;
            font-weight: 700;
            margin: 0;
        }
        .container {
            width: 100%;
            max-width: 60%;
            padding: 40px;
            background: #fff;
            border-radius: 50px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
        }
        h2 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 28px;
            color: #333;
            text-align: center;
            font-family: 'Times New Roman', serif;
        }
        p {
            margin-bottom: 20px;
            color: #666;
            font-size: 16px;
            text-align: center;
            font-family: 'Times New Roman', serif;
        }
        form {
            text-align: center;
        }
        label {
            font-size: 16px;
            color: #555;
            margin-bottom: 8px;
            display: block;
            text-align: left;
        }
        input[type="text"],
        input[type="password"],
        select {
            width: 80%; /* Adjusted width for the search input */
            padding: 14px;
            margin-bottom: 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            background-color: #f5f5f5;
            box-shadow: inset 0px 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        select {
            width: 20%; /* Adjusted width for the dropdown */
        }
        input[type="text"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            background-color: #e0e0e0;
        }
        input[type="submit"] {
            width: 20%;
            padding: 14px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
      
        a:hover {
            text-decoration: underline;
        }
        .form-group {
            position: relative;
            margin-bottom: 20px;
            display: flex;
        }
        .icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 10px; 
            background-color: transparent;
        }
        .icon i {
            font-size: 18px;
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
    <div class="main-content">
        <form action="result.php" method="GET" class="container">
            <h1 style="font-size: 32px; color: #333; margin: 20px 0; text-align: center;">
                Welcome, <?php echo htmlspecialchars($username); ?>!
            </h1>
            <h2>Search</h2>
            <div class="form-group">
                <select name="category" id="category">
                    <option value="all">All</option>
                    <option value="research">Research</option>
                    <option value="instructors">Instructors</option>
                    <option value="authors">Authors</option>
                    <option value="keywords">Keyword</option>
                </select>
                <input type="text" name="search" id="search" placeholder="Enter your search query" required>
                <label for="search" class="icon"><i class="fa fa-search"></i></label>
            </div>
            <input type="submit" value="Search">
        </form>
    </div>
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
    <script>
        // JavaScript to change navbar background color on scroll
        window.addEventListener('scroll', function() {
            var navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
