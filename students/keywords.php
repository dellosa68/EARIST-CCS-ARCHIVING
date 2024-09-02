<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include '../conn/db_conn.php';

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
if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
    
    // Fetch research documents with the matching keyword from the database
    $sql = "SELECT * FROM document WHERE keywords LIKE '%$keyword%' ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $researchDocs = [];
    while ($row = $result->fetch_assoc()) {
        $researchDocs[] = $row;
    }
} else {
    $keyword = "";
    $researchDocs = [];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Results</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
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
        .main-content {
            transition: margin-left 0.5s;
            margin-top: 100px;
        }
        .header {
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
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .container h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .list-group-item {
            padding: 15px;
            border: none;
            border-bottom: 1px solid #dee2e6;
            transition: background-color 0.3s;
        }
        .list-group-item:last-child {
            border-bottom: none;
        }
        .list-group-item:hover {
            background-color: #f8f9fa;
        }
        .list-group-item a {
            color: #343a40;
            font-weight: 500;
        }
        .list-group-item small {
            color: #6c757d;
        }
        .btn-back {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #343a40;
            border: none;
            color: #fff;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .btn-back:hover {
            background-color: #6c757d;
        }
        a {
            text-decoration: none;
            color: #007bff;
            font-size: 14px;
        }
        a:hover {
            text-decoration: underline;
        }
        .icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 10px; /* Adjusted right position */
            color: #888;
        }
        .icon i {
            font-size: 18px;
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
            <a href="research.php">Documents</a>
            <a href="publish_research.php">Publish a Research</a>
            <a href="bookmarks.php">Bookmarks</a>
            <a href="#">Settings</a>
        </div>
        <div class="profile">
            <?php if ($profile_pic_base64): ?>
                <a href="profile.php">
                    <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
                </a>
            <?php else: ?>
                <a href="profile.php">
                    <img src="../images/default_profile.jpgg" alt="Default Profile Picture">
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
            <h1>Research Results for keyword "<?php echo htmlspecialchars($keyword); ?>"</h1>
            <?php if (count($researchDocs) > 0): ?>
                <ul class="list-group">
                    <?php foreach ($researchDocs as $doc): ?>
                        <li class="list-group-item">
                            <a href="research_details.php?research_id=<?php echo $doc['research_id']; ?>"><?php echo htmlspecialchars($doc['title']); ?></a>
                            <br>
                            <small>by: <?php echo htmlspecialchars($doc['author']); ?> • <?php echo htmlspecialchars($doc['year']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No research documents found for the keyword "<?php echo htmlspecialchars($keyword); ?>"</p>
            <?php endif; ?>
            <a href="javascript:history.back()" class="btn btn-back">Back</a>

        </div>
    </div>
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
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
    </script>
</body>
</html>
