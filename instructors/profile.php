<?php
session_start(); // Start the session
include "../conn/db_conn.php";

function displayAccessDenied() {
    echo "<script>alert('Access Denied. Please log in.');</script>";
}

$username = $_SESSION["username"];
$profile_pic = isset($_SESSION["profile_pic"]) ? $_SESSION["profile_pic"] : null;
$account_id = $_SESSION["account_id"];

$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

// Fetch instructor details
$instructor = [];
if (isset($account_id)) {
    $sql_instructor = "SELECT * FROM instructors WHERE account_id = ?";
    $stmt_instructor = $conn->prepare($sql_instructor);
    $stmt_instructor->bind_param("i", $account_id);
    $stmt_instructor->execute();
    $result_instructor = $stmt_instructor->get_result();

    if ($result_instructor->num_rows > 0) {
        $instructor = $result_instructor->fetch_assoc();
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
        .adviser-works h4 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: #d32f2f; /* Use the same color for consistency */
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
        }
        .open-btn:hover {
            background-color: #FF7575;
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
        .work-list {
            list-style-type: none;
            padding: 0;
        }
        .work-list li {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .work-list li .title {
            font-weight: bold;
        }
        .work-list li .status {
            background-color: #d32f2f;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
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

    <div class="main-content" id="main-content">
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
          <form action="conn/logout.php" method="post">
            <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
          </form>
        </div>
    </nav>
  <div class="breadcrumb-container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="home.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="users.php">User Accounts</a></li>
        <li class="breadcrumb-item active" aria-current="page">Instructor's Profile</li>
      </ol>
    </nav>
  </div>

        <div class="container">
            <?php if (!empty($instructor)): ?>
                <div class="profile">
                    <?php if ($instructor['pic']): ?>
                        <?php $profile_pic_base64 = base64_encode($instructor['pic']); ?>
                        <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
                    <?php else: ?>
                        <img src="../images/default_profile.png" alt="Default Profile Picture">
                    <?php endif; ?>
                    <div class="info">
                        <h2><?php echo htmlspecialchars($instructor['title'] . ' ' . $instructor['fname'] . ' ' . $instructor['mi'] . ' ' . $instructor['lname']); ?></h2>
                        <p>Role: <?php echo htmlspecialchars($instructor['role']); ?></p>
                        <p>Email: <?php echo htmlspecialchars($instructor['email']); ?></p>
                    </div>
                    <button class='btn btn-primary' data-toggle='modal' data-target='#editModal'>Edit Details</button>
                </div>
            <?php else: ?>
                <p>Instructor details not found.</p>
            <?php endif; ?>
            <div class="tabs">
                <a href="#works" class="active">Works</a>
            </div>
            <div id="works" class="content active">
    <div class="adviser-works">
        <h4>Works as Adviser</h4>
        <div class="documents">
            <?php
            if (isset($account_id)) {
                // Fetch works where this instructor is an adviser
                $sql_adviser = "SELECT * FROM document WHERE adviser_id = ?";
                $stmt_adviser = $conn->prepare($sql_adviser);
                $stmt_adviser->bind_param("i", $account_id);
                $stmt_adviser->execute();
                $result_adviser = $stmt_adviser->get_result();

                if ($result_adviser->num_rows > 0) {
                    while ($row = $result_adviser->fetch_assoc()) {
                        $cover_image_base64 = !empty($row['cover']) ? base64_encode($row['cover']) : '';
                        $cover_image_src = $cover_image_base64 ? 'data:image/jpeg;base64,' . $cover_image_base64 : 'https://via.placeholder.com/80';

                        echo '<a href="research_details.php?research_id=' . htmlspecialchars($row['research_id']) . '" title="' . htmlspecialchars($row['title']) . '">';
                        echo '<div class="document">';
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
        <h4>Works as Panelist</h4>
                    <div class="documents">
                        <?php
                        if (isset($account_id)) {
                            // Fetch works where this instructor is a panelist
                            $sql_panelist = "SELECT research_id, title, year, cover FROM document WHERE FIND_IN_SET(?, panel_id) > 0";
                            $stmt_panelist = $conn->prepare($sql_panelist);
                            $stmt_panelist->bind_param("i", $account_id);
                            $stmt_panelist->execute();
                            $result_panelist = $stmt_panelist->get_result();

                            if ($result_panelist->num_rows > 0) {
                                while ($row = $result_panelist->fetch_assoc()) {
                                    $cover_image_base64 = !empty($row['cover']) ? base64_encode($row['cover']) : '';
                                    $cover_image_src = $cover_image_base64 ? 'data:image/jpeg;base64,' . $cover_image_base64 : 'https://via.placeholder.com/80';

                                    echo '<a href="research_details.php?research_id=' . htmlspecialchars($row['research_id']) . '" title="' . htmlspecialchars($row['title']) . '">';
                                    echo '<div class="document">';
                                    echo '<div class="thumbnail" style="background-image: url(\'' . $cover_image_src . '\');"></div>';
                                    echo '<p class="title" title="' . htmlspecialchars($row['title']) . '">' . htmlspecialchars($row['title']) . '</p>';
                                    echo '<p class="year">' . htmlspecialchars($row['year']) . '</p>';
                                    echo '</div>';
                                    echo '</a>';
                                }
                            } else {
                                echo '<p>No works as Panelist found.</p>';
                            }
                        }
                        ?>
                    </div>
    </div>


            </div>
        </div>
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Instructor Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm" enctype="multipart/form-data">
                        <input type="hidden" name="account_id" value="<?php echo $account_id; ?>">
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($instructor['title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="fname">First Name:</label>
                            <input type="text" class="form-control" id="fname" name="fname" value="<?php echo htmlspecialchars($instructor['fname']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="mname">Middle Initial:</label>
                            <input type="text" class="form-control" id="mname" name="mname" value="<?php echo htmlspecialchars($instructor['mi']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="lname">Last Name:</label>
                            <input type="text" class="form-control" id="lname" name="lname" value="<?php echo htmlspecialchars($instructor['lname']); ?>" required>
                        </div>
                        <div class="form-group">
                          <label for="post">Post Nominal:</label>
                          <input type="text" id="post" class="form-control" name="post" value="<?php echo htmlspecialchars($instructor['post_nominal']); ?>" >
                            
                        </div>
                        <div class="form-group">
                            <label for="role">Role:</label>
                            <input type="text" class="form-control" id="role" name="role" value="<?php echo htmlspecialchars($instructor['role']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($instructor['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="profile_pic">Profile Picture:</label>
                            <input type="file" class="form-control-file" id="profile_pic" name="profile_pic" accept="image/*">
                            <img id="profile_pic_preview" class="preview-img">
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
        function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('profilePicPreview');
        output.src = reader.result;
        output.style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
}

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
    document.getElementById('profile_pic').addEventListener('change', function(event) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('profile_pic_preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(event.target.files[0]);
        });

        document.getElementById('editForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            var formData = new FormData(this);

            fetch('conn/update_instructor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() == 'success') {
                    alert('Instructor updated successfully');
                    window.location.reload();
                } else {
                    alert('Error updating instructor: ' + data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating instructor');
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
