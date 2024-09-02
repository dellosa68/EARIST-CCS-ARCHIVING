<?php
session_start();
include "../conn/db_conn.php";

// Check if an author ID is provided
if (!isset($_GET['author_id'])) {
    echo "<script>alert('Author ID is missing.'); window.location.href = 'authors.php';</script>";
    exit();
}

$author_id = mysqli_real_escape_string($conn, $_GET['author_id']);
$query = "SELECT * FROM student WHERE account_id = '$author_id'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>alert('Author not found.'); window.location.href = 'authors.php';</script>";
    exit();
}

$author = mysqli_fetch_assoc($result);

// Update author information
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $profile_pic = $_FILES['profile_pic']['tmp_name'] ? addslashes(file_get_contents($_FILES['profile_pic']['tmp_name'])) : $author['profile_pic'];
    $cor = $_FILES['cor']['tmp_name'] ? addslashes(file_get_contents($_FILES['cor']['tmp_name'])) : $author['cor'];

    $update_query = "UPDATE student SET first_name='$fname', last_name='$lname', course='$course', email='$email', phone='$phone', year='$year', profile_pic='$profile_pic', cor='$cor' WHERE account_id='$author_id'";
    mysqli_query($conn, $update_query);
    header("Location: author_details.php?author_id=$author_id");
    exit();
}

// Prepare profile picture
$profile_pic_base64 = $author['profile_pic'] ? base64_encode($author['profile_pic']) : null;
$profile_pic_src = $profile_pic_base64 ? "data:image/jpeg;base64,$profile_pic_base64" : 'path/to/default/profile-pic.jpg';

// Prepare COR image
$cor_base64 = $author['cor'] ? base64_encode($author['cor']) : null;
$cor_src = $cor_base64 ? "data:image/jpeg;base64,$cor_base64" : '../images/default_profile.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Author Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            margin-top: 30px;
        }
        .card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s;
            background: #fff;
        }
        .card-header {
            background: #FF7575;
            color: #fff;
            font-size: 1.5rem;
            text-align: center;
            padding: 20px;
        }
        .card-img-top {
            height: 250px;
            object-fit: cover;
        }
        .card-body {
            padding: 20px;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #FF7575;
            border: none;
        }
        .btn-primary:hover {
            background-color: #e14d4d;
        }
        footer {
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            font-family: 'Times New Roman', serif;
            margin-top: 20px;
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
        .cor-img {
            height: 150px;
            object-fit: cover;
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
    <div class="card">
        <div class="card-header">
            Author Details
        </div>
        <img src="<?php echo $profile_pic_src; ?>" class="card-img-top" alt="Profile Picture">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="fname">First Name</label>
                    <input type="text" class="form-control" id="fname" name="fname" value="<?php echo htmlspecialchars($author['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input type="text" class="form-control" id="lname" name="lname" value="<?php echo htmlspecialchars($author['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="course">Course</label>
                    <select class="form-control" id="course" name="course" required>
                        <option value="bsit" <?php echo $author['course'] == 'bsit' ? 'selected' : ''; ?>>BSIT</option>
                        <option value="bscs" <?php echo $author['course'] == 'bscs' ? 'selected' : ''; ?>>BSCS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($author['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($author['phone']); ?>">
                </div>
                <div class="form-group">
                    <label for="year">Year</label>
                    <input type="text" class="form-control" id="year" name="year" value="<?php echo htmlspecialchars($author['year']); ?>">
                </div>
                <div class="form-group">
                    <label for="profile_pic">Profile Picture</label>
                    <input type="file" class="form-control-file" id="profile_pic" name="profile_pic">
                    <small class="form-text text-muted">Leave blank to keep current picture.</small>
                </div>
                <div class="form-group">
                    <label for="cor">Certificate of Registration (COR)</label>
                    <input type="file" class="form-control-file" id="cor" name="cor">
                    <small class="form-text text-muted">Leave blank to keep current COR.</small>
                </div>
                <div class="mt-4">
                <h5>Current Certificate of Registration</h5>
                <img src="<?php echo $cor_src; ?>" class="cor-img" alt="Certificate of Registration">
            </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
</div>
<footer>
    &copy; 2024 Your Company Name. All rights reserved.
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
