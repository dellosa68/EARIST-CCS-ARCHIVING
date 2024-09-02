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

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <script src="https://cdn.tiny.cloud/1/hqios0tk9hkwf4oo5pr7c87ki0iiyy44eyix3df13hgaq6y7/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
  <style>
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
    #main-content h1{
      text-align: center;
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

    .header {
      background-image: url('../images/kkkk.drawio.png'); /* Replace 'path/to/your/image.jpg' with the actual path to your image */
        background-repeat: no-repeat;
        background-attachment: fixed; 
        background-size: 100%;
        height: 190px; /* Adjust the height according to your image */
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #fff; /* Text color */
        text-align: center;
    }
    .admin-info {
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
    label {
        font-weight: bold;
        color: #555;
    }
    input[type="text"],
    input[type="file"] {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 16px;
    }
    input[type="file"] {
        cursor: pointer;
    }
    #preview {
        margin-top: 10px;
        width: 100%;
        border: 1px solid black;
    }
    button[type="submit"] {
        color: white;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
        grid-column: span 3;
    }

    button[type="submit"]:hover {
        background-color: #FF5A5A;
    }
    .content{
      display:flex;
      flex-direction: row;
    }
   
       footer {
      width: 100%;
      background-color: #333;
      color: #fff;
      padding: 20px 0;
      text-align: center;
      font-family: 'Times New Roman', serif;
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
    .form-group {
      margin-bottom: 1rem;
    }
    .form-control {
    margin-top: 7px;
    display: block;
    width: 100%;
    height: 45px;
    padding: 10px;
    font-size: 1rem;
    font-weight: 400;}
    .preview-img {
            max-width: 100px;
            max-height: 100px;
            margin-top: 10px;
            display: none;
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
  <div class="breadcrumb-container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="home.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="users.php">User Accounts</a></li>
        <li class="breadcrumb-item active" aria-current="page">Add an Instructor Account</li>
      </ol>
    </nav>
  </div>
  <div class="container">
    <h1>Add New Instructor</h1>
    <form action="../conn/add_ins.php" method="post" enctype="multipart/form-data">
      <div class="form-row">
        <div class="form-group col-md-4">
          <div class="inspic">
          <div class="name">
            <img id="preview" src="../images/profile.png" alt="Preview">
            <label for="instructor-picture">Instructor Picture:</label>
            <input type="file" id="instructor-picture" name="instructor-picture" onchange="previewImage(this)" required>
          </div>
        </div>
        </div>
        
        <div class="form-group col-md-8" style="margin-top: 20px;">
          <div class="form-row">
            <div class="form-group col-md-2">
              <label for="title">Title:</label>
              <select id="title" class="form-control" name="title">
                <option selected>Choose</option>
                <option>Ms.</option>
                <option>Mr.</option>
                <option>Dr.</option>
              </select>
            </div>
            <div class="form-group col-md-4">
              <label for="first-name">First Name:</label>
              <input type="text" id="first-name" name="first-name">
            </div>
            <div class="form-group col-md-2">
              <label for="middle">MI:</label>
              <input type="text" id="middle" name="middle">
            </div>
            <div class="form-group col-md-4">
              <label for="last-name">Last Name:</label>
              <input type="text" id="last-name" name="last-name">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="email">Email:</label>
              <input type="text" id="email" name="email">
            </div>
            <div class="form-group col-md-6">
              <label for="role">Role:</label>
              <select id="role" class="form-control" name="role">
              <option selected>Choose</option>
              <option>Instructor</option>
              <option>Proffesor/Research Coordinator</option>
              </select>
            </div>
            
          </div>
          <div class="form-row">
            <button type="submit">Submit</button>
          </div>
        </div>
      </div>
      
      
    </form>
  </div>
    
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
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
  function previewImage(input) {
    var preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
tinymce.init({
    selector: 'textarea#description',
    plugins: 'lists link image imagetools media table code',
    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | code'
  });
</script>

</body>
</html>
