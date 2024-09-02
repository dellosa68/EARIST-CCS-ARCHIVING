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
  <title>ADD USER ACCOUNT</title>
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
    #main-content h1 {
      text-align: center;
      margin-bottom: 30px;
    }
    .container {
      margin-bottom: 50px;
    }
    .container h1 {
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

    .custom-file-upload {
      border: 1px solid #ddd;
      display: inline-block;
      padding: 6px 12px;
      cursor: pointer;
      border-radius: 4px;
      background-color: #fff;
      color: #333;
    }

    .custom-file-upload:hover {
      background-color: #f0f0f0;
    }

    .img-preview {
      max-width: 100%;
      max-height: 200px;
      margin-top: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      display: none;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    button[type="submit"] {
      color: white;
      border: none;
      padding: 10px 20px;
      cursor: pointer;
      border-radius: 5px;
      font-size: 16px;
      width: 100%;
    }

    button[type="submit"]:hover {
      background-color: #FF5A5A;
    }
    
    .input-error {
  border-color: red;
}

.error-message {
  display: block;
  color: red;
  font-size: 0.875em;
  margin-top: 5px;
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
      <form method="post">
        <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
      </form>
    </div>
  </nav>
  <div class="breadcrumb-container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="home.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="users.php">User Accounts</a></li>
        <li class="breadcrumb-item active" aria-current="page">Add an Author Account</li>
      </ol>
    </nav>
  </div>

  <div class="container">
    <h1>Add an Author</h1>
    <form method="post" enctype="multipart/form-data" action="conn/save_author.php">
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="first_name">First Name</label>
          <input type="text" class="form-control" id="first_name" name="first_name" required>
        </div>
        
        <div class="form-group col-md-6">
          <label for="last_name">Last Name</label>
          <input type="text" class="form-control" id="last_name" name="last_name" required>
        </div>
      </div>
      <div class="form-row">
  <div class="form-group col-md-6">
    <label for="email">Email</label>
    <input type="email" class="form-control" id="email" name="email" required>
    <span class="error-message text-danger"></span>
  </div>
  <div class="form-group col-md-6">
    <label for="student_id">Student ID</label>
    <input type="text" class="form-control" id="student_id" name="student_id" required>
    <span class="error-message text-danger"></span>
  </div>
</div>
      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="course">Course</label>
          <select class="form-control" id="course" name="course" required>
            <option value="BSIT">Bachelor of Science in Information Technology</option>
            <option value="BSCS">Bachelor of Science in Computer Science</option>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label for="year">Year</label>
          <select class="form-control" id="year" name="year" required>
            <option value="1st Year">1st Year</option>
            <option value="2nd Year">2nd Year</option>
            <option value="3rd Year">3rd Year</option>
            <option value="4th Year">4th Year</option>
          </select>
        </div>
        <div class="form-group col-md-4">
          <label for="section">Section</label>
          <select class="form-control" id="section" name="section" required>
            <option value="Section A">Section A</option>
            <option value="Section B">Section B</option>
            <option value="Section C">Section C</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="profile_picture" class="custom-file-upload">
          <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required onchange="previewImage(event)">
          Choose Profile Picture
        </label>
        <p class="file-size-info">Allowed file types: JPG, PNG, GIF. Maximum file size: 2 MB.</p>
        <img id="image_preview" class="img-preview" src="" alt="Image Preview">
      </div>
      <button type="submit">Submit</button>
    </form>
  </div>
</div>

<footer>
  &copy; 2024 Your Company Name. All rights reserved.
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

  function previewImage(event) {
    var file = event.target.files[0];
    var reader = new FileReader();
    var output = document.getElementById('image_preview');
    
    // Check file size (2 MB limit)
    if (file.size > 2 * 1024 * 1024) {
      alert('File size exceeds 2 MB. Please choose a smaller file.');
      event.target.value = ''; // Clear the file input
      output.style.display = 'none';
      return;
    }

    reader.onload = function() {
      output.src = reader.result;
      output.style.display = 'block';
    }
    reader.readAsDataURL(file);
  }

  $(document).ready(function() {
  function validateInput() {
    var email = $('#email').val();
    var student_id = $('#student_id').val();

    // Only make the AJAX request if both fields have values
    if (email.trim() === '' && student_id.trim() === '') {
      $('#email').removeClass('input-error');
      $('#email').next('.error-message').text('');
      $('#student_id').removeClass('input-error');
      $('#student_id').next('.error-message').text('');
      return;
    }

    $.ajax({
      url: 'conn/validate_student.php',
      type: 'POST',
      data: {
        email: email,
        student_id: student_id
      },
      success: function(response) {
        console.log("Response:", response); // Log the response

        if (email.trim() !== '') {
          if (response.email_exists) {
            $('#email').addClass('input-error');
            $('#email').next('.error-message').text('Email already exists.');
          } else {
            $('#email').removeClass('input-error');
            $('#email').next('.error-message').text('');
          }
        }

        if (student_id.trim() !== '') {
          if (response.student_id_exists) {
            $('#student_id').addClass('input-error');
            $('#student_id').next('.error-message').text('Student ID already exists.');
          } else {
            $('#student_id').removeClass('input-error');
            $('#student_id').next('.error-message').text('');
          }
        }
      },
      error: function(xhr, status, error) {
        console.error("AJAX error:", status, error);
        console.error("Response text:", xhr.responseText);
        alert("An error occurred while processing the request.");
      }
    });
  }

  $('#email, #student_id').on('input', function() {
    validateInput();
  });
});

</script>


</body>
</html>
