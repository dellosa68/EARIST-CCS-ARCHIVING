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
$student_id = $_SESSION["account_id"];
// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;
$course_filter = isset($_GET['course']) ? mysqli_real_escape_string($conn, $_GET['course']) : '';

// Modify the query to include the course filter
$query = "SELECT * FROM student";
if ($course_filter) {
    $query .= " WHERE course = '$course_filter'";
}
$author_result = mysqli_query($conn, $query);
$authors = [];
while ($row = mysqli_fetch_assoc($author_result)) {
    $authors[] = $row;
}
$authorContainerDisplay = 'block';
$bsitAuthorsContainerDisplay = 'none';
$bscsAuthorsContainerDisplay = 'none';

if ($course_filter === 'bsit') {
    $authorContainerDisplay = 'none';
    $bsitAuthorsContainerDisplay = 'block';
    $bscsAuthorsContainerDisplay = 'none';
} elseif ($course_filter === 'bscs') {
    $authorContainerDisplay = 'none';
    $bsitAuthorsContainerDisplay = 'none';
    $bscsAuthorsContainerDisplay = 'block';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
        background-size: cover;
        background-position: center;
        height: 200px; /* Adjust the height according to your image */
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #fff; /* Text color */
        text-align: center;
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

    .tab-content {
  background-color: #f9f9f9; /* Background color for tab content */
  padding: 20px;
  border-radius: 5px;
}

/* Style for the tabs */
.nav-tabs {
  border-bottom: none; /* Remove border from tabs */
}

.nav-tabs .nav-item .nav-link {
  border: none; /* Remove border from individual tab links */
  border-radius: 5px 5px 0 0; /* Rounded corners for the top */
  color: #555; /* Tab link color */
}

.nav-tabs .nav-item .nav-link.active {
  background-color: #fff; /* Background color for active tab */
  color: #333; /* Active tab text color */
  border-bottom: 2px solid #FF7575; /* Bottom border for active tab */
}

/* Style for tab pane */
.tab-pane {
  background-color: #fff; /* Background color for tab pane */
  border: 1px solid #ccc; /* Border for tab pane */
  padding: 20px;
  border-radius: 0 5px 5px 5px; /* Rounded corners, except for the top */
  margin-top: -1px; /* Adjust for border thickness */
}

/*\'' Style for title within tab pane */
.tab-pane .title {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  text-align: center;
  border-bottom: 1px solid black; /* Border color for title */
  padding-bottom: 10px;
  margin-bottom: 20px;
}

    .title {
  position: relative; /* Set position relative to allow absolute positioning */
}

#addUserBtn {
  position: absolute;
  top: 0;
  right: 0;
  margin-top: 10px; /* Adjust top margin to position it properly */
  margin-right: 10px; /* Adjust right margin to position it properly */
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
   .card {
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
}

.card:hover {
    transform: scale(1.05);
}

.card-img-top {
    height: 200px;
    object-fit: cover;
}


.card-body {
    flex: 1; /* Allows card body to take up remaining space */
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
}

.card-title {
    font-size: 1rem;
    margin-bottom: 10px;
}

/* Optional: Ensure card body has consistent padding and margin */
.card-body {
    padding: 15px;
}
.text-decoration-none {
    text-decoration: none;
}

.card:hover {
    /* Optional: Add hover effect */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
}

.instructor-container .card {
    height: 100%; /* Full height to align content */
}
.search-container {
      margin-bottom: 20px;
    }

/* Make sure the row wraps correctly and handles small screens */
@media (max-width: 768px) {
    .col-md-2 {
        flex: 0 0 100%;
        max-width: 100%;
    }
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
      <div class="container-fluid p-0">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tab1">Instructors</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab2">Authors</a>
            </li>
            
        </ul>
        <div class="tab-content mt-2">
          <div class="tab-pane fade show active" id="tab1">
              <div class="title">
                  <h1>Instructors</h1>
                  <button class="btn btn-primary" id="addUserBtn" onclick="goToAddUserPage()">Add New Instructor</button>
              </div>
              <div class="row">
                  <?php
                  // Fetch instructor data
                  $query = "SELECT account_id, pic, fname, lname FROM instructors";
                  $result = mysqli_query($conn, $query);
                  while ($row = mysqli_fetch_assoc($result)): ?>
                      <div class="col-md-4 mb-3">
                          <a href="instructor_details.php?account_id=<?php echo urlencode($row['account_id']); ?>" class="text-decoration-none">
                              <div class="card">
                                  <?php if ($row['pic']): ?>
                                      <img src="data:image/jpeg;base64,<?php echo base64_encode($row['pic']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['fname']); ?>">
                                  <?php else: ?>
                                      <img src="../images/default_profile.jpg" class="card-img-top" alt="Default Profile Picture">
                                  <?php endif; ?>
                                  <div class="card-body">
                                      <h5 class="card-title"><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></h5>
                                  </div>
                              </div>
                          </a>
                      </div>
                  <?php endwhile; ?>
              </div>

          </div>
          <div class="tab-pane fade" id="tab2">
              <div class="title">
                  <h1>Authors</h1>
                  <button class="btn btn-primary" id="addUserBtn" onclick="goToAddAuthorPage()">Add New Author</button>
              </div>
              
              <div class="container mb-4">
                  <!-- Filter by Course Dropdown -->
                   <form id="filterForm">
                    <div class="form-group">
                        <label for="courseFilter">Filter by Course:</label>
                        <select id="courseFilter" name="course" class="form-control">
                            <option value="All" <?php echo $course_filter === 'All' ? 'selected' : ''; ?>>All Courses</option>
                            <option value="bsit" <?php echo $course_filter === 'bsit' ? 'selected' : ''; ?>>BSIT</option>
                            <option value="bscs" <?php echo $course_filter === 'bscs' ? 'selected' : ''; ?>>BSCS</option>
                        </select>
                    </div>
                </form>
              </div>
              <div class="search-container">
        <input type="text" id="searchInput" class="form-control" placeholder="Search Authors by Name">
      </div>
              <div class="instructor-container" id="authorContainer" style="display: <?php echo $authorContainerDisplay; ?>;">
                   <?php
      // Handle search input
                $search_query = '';
                if (isset($_GET['search'])) {
                  $search_term = mysqli_real_escape_string($conn, $_GET['search']);
                  $search_query = " AND (first_name LIKE '%$search_term%' OR last_name LIKE '%$search_term%')";
                }

                // Modify the query to include the search term
                $query = "SELECT account_id, profile_pic, first_name, last_name FROM student WHERE 1";
                if ($course_filter) {
                  $query .= " AND course = '$course_filter'";
                }
                $query .= $search_query;
                
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0): ?>
                  <div class="row">
                    <?php while ($author = mysqli_fetch_assoc($result)): ?>
                      <div class="col-md-2 col-sm-4 mb-4">
                        <a href="author_details.php?author_id=<?php echo urlencode($author['account_id']); ?>">
                          <div class="card">
                            <?php if ($author['profile_pic']): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($author['profile_pic']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($author['first_name'] . ' ' . $author['last_name']); ?>">
                            <?php else: ?>
                                <img src="../images/default_profile.jpg" class="card-img-top" alt="Default Profile Picture">
                            <?php endif; ?>

                            <div class="card-body">
                              <h5 class="card-title"><?php echo htmlspecialchars($author['first_name'] . ' ' . $author['last_name']); ?></h5>
                            </div>
                          </div>
                        </a>
                      </div>
                    <?php endwhile; ?>
                  </div>
                <?php else: ?>
                  <p>No authors found.</p>
                <?php endif; ?>
              </div>
              <div class="instructor-container" id="bsitAuthorsContainer"  style="display: <?php echo $bsitAuthorsContainerDisplay; ?>;">
                  <?php
                  // Query to fetch BSIT course authors
                  $bsit_query = "SELECT account_id, profile_pic, first_name, last_name FROM student WHERE course = 'BSIT'";
                  $bsit_result = mysqli_query($conn, $bsit_query);
                  if (mysqli_num_rows($bsit_result) > 0): ?>
                      <div class="row">
                          <?php while ($bsit_author = mysqli_fetch_assoc($bsit_result)): ?>
                              <div class="col-md-2 col-sm-4 mb-4">
                                  <a href="author_details.php?author_id=<?php echo urlencode($bsit_author['account_id']); ?>">
                                      <div class="card">
                                         <?php if ($bsit_author['profile_pic']): ?>
                                              <img src="data:image/jpeg;base64,<?php echo base64_encode($bsit_author['profile_pic']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($bsit_author['first_name'] . ' ' . $bsit_author['last_name']); ?>">
                                          <?php else: ?>
                                              <img src="../images/default_profile.jpg" class="card-img-top" alt="Default Profile Picture">
                                          <?php endif; ?>

                                          <div class="card-body">
                                              <h5 class="card-title"><?php echo htmlspecialchars($bsit_author['first_name'] . ' ' . $bsit_author['last_name']); ?></h5>
                                          </div>
                                      </div>
                                  </a>
                              </div>
                          <?php endwhile; ?>
                      </div>
                  <?php else: ?>
                      <p>No BSIT authors found.</p>
                  <?php endif; ?>
              </div>
              <div class="instructor-container" id="bscsAuthorsContainer"  style="display: <?php echo $bscsAuthorsContainerDisplay; ?>;">
                  <?php
                  // Query to fetch BSCS course authors
                  $bscs_query = "SELECT account_id, profile_pic, first_name, last_name FROM student WHERE course = 'BSCS'";
                  $bscs_result = mysqli_query($conn, $bscs_query);
                  if (mysqli_num_rows($bscs_result) > 0): ?>
                      <div class="row">
                          <?php while ($bscs_author = mysqli_fetch_assoc($bscs_result)): ?>
                              <div class="col-md-2 col-sm-4 mb-4">
                                  <a href="author_details.php?author_id=<?php echo urlencode($bscs_author['account_id']); ?>">
                                      <div class="card">
                                          <?php if ($bscs_author['profile_pic']): ?>
                                              <img src="data:image/jpeg;base64,<?php echo base64_encode($bscs_author['profile_pic']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($bscs_author['first_name'] . ' ' . $bscs_author['last_name']); ?>">
                                          <?php else: ?>
                                              <img src="../images/default_profile.jpg" class="card-img-top" alt="Default Profile Picture">
                                          <?php endif; ?>

                                          <div class="card-body">
                                              <h5 class="card-title"><?php echo htmlspecialchars($bscs_author['first_name'] . ' ' . $bscs_author['last_name']); ?></h5>
                                          </div>
                                      </div>
                                  </a>
                              </div>
                          <?php endwhile; ?>
                      </div>
                  <?php else: ?>
                      <p>No BSCS authors found.</p>
                  <?php endif; ?>
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
  document.getElementById('courseFilter').addEventListener('change', function() {
        var selectedCourse = this.value;
        var authorContainer = document.getElementById('authorContainer');
        var bsitAuthorsContainer = document.getElementById('bsitAuthorsContainer');
        var bscsAuthorsContainer = document.getElementById('bscsAuthorsContainer');

        // Show/Hide containers based on selected course
        if (selectedCourse === 'bsit') {
          authorContainer.style.display = 'none';
          bsitAuthorsContainer.style.display = 'block';
          bscsAuthorsContainer.style.display = 'none';
        } else if (selectedCourse === 'bscs') {
          authorContainer.style.display = 'none';
          bsitAuthorsContainer.style.display = 'none';
          bscsAuthorsContainer.style.display = 'block';
        } else {
          authorContainer.style.display = 'block';
          bsitAuthorsContainer.style.display = 'none';
          bscsAuthorsContainer.style.display = 'none';
        }

        // Optionally, you might want to reload the page with the selected filter
        // window.location.href = 'yourpage.php?course=' + encodeURIComponent(selectedCourse);
      });
  document.getElementById('searchInput').addEventListener('input', function() {
      var searchValue = this.value.toLowerCase();
      var authorCards = document.querySelectorAll('#authorContainer .card');
      
      authorCards.forEach(function(card) {
        var title = card.querySelector('.card-title').textContent.toLowerCase();
        if (title.includes(searchValue)) {
          card.parentElement.style.display = 'block';
        } else {
          card.parentElement.style.display = 'none';
        }
      });
    });
  function goToAddUserPage() {
  window.location.href = 'add_instructor.php';
}
 function goToAddAuthorPage() {
  window.location.href = 'add_author.php';
}


</script>
</body>
</html>
