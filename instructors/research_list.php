<?php
    session_start();
include '../conn/db_conn.php';

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT research_id, title, cover, year FROM document";
if ($searchKeyword) {
    $sql .= " WHERE title LIKE '%" . $conn->real_escape_string($searchKeyword) . "%' 
              OR author LIKE '%" . $conn->real_escape_string($searchKeyword) . "%' 
              OR course LIKE '%" . $conn->real_escape_string($searchKeyword) . "%'
              OR adviser LIKE '%" . $conn->real_escape_string($searchKeyword) . "%' 
              OR panel LIKE '%" . $conn->real_escape_string($searchKeyword) . "%'
              OR keywords LIKE '%" . $conn->real_escape_string($searchKeyword) . "%'
              OR year LIKE '%" . $conn->real_escape_string($searchKeyword) . "%'";
}

$result = $conn->query($sql);

$documents = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }
}


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


$conn->close();
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
    h1{
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

    .search-bar {
        padding: 10px 20px;
        font-size: 16px;
        width: 400px;
        border: none;
        border-radius: 30px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
    }

    .search-bar:focus {
        outline: none;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .add-button {
        font-size: 28px;
        background-color: #FF7575;
        border: none;
        color: white;
        border-radius: 50%;
        cursor: pointer;
        padding: 10px 20px;
        transition: all 0.3s;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .add-button:hover {
        background-color: #ff5252;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
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

    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid black;
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

    footer {
        width: 100%;
        background-color: #333;
        color: #fff;
        padding: 20px 0;
        text-align: center;
        font-family: 'Times New Roman', serif;
    }
    .modal {
      display: none; /* Hidden by default */
      position: fixed; /* Stay in place */
      z-index: 1; /* Sit on top */
      left: 0;
      top: 0;
      width: 100%; /* Full width */
      height: 100%; /* Full height */
      overflow: auto; /* Enable scroll if needed */
      background-color: rgb(0,0,0); /* Fallback color */
      background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
      padding-top: 60px; /* Location of the box */
    }

    .modal-content {
      background-color: #fefefe;
      margin: 5% auto; /* 15% from the top and centered */
      padding: 20px;
      border: 1px solid #888;
      width: 50%; /* Could be more or less, depending on screen size */
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
    }

    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }

    .modal-buttons {
      display: flex;
      justify-content: center;
      gap: 20px;
    }

    .modal-buttons button {
      padding: 10px 20px;
      font-size: 16px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .modal-buttons .btn-old {
      background-color: #007bff;
      color: #fff;
    }

    .modal-buttons .btn-new {
      background-color: #28a745;
      color: #fff;
    }

    .modal-buttons button:hover {
      opacity: 0.8;
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
    <div class="container">
        <header>
            <input type="text" placeholder="Search by title, author, or year" class="search-bar" oninput="searchDocuments()">
        </header>
        <section class="documents" id="documents-section">
            <?php if (count($documents) > 0): ?>
                <?php foreach ($documents as $doc): ?>
                    <a href="research_details.php?research_id=<?php echo $doc['research_id']; ?>" title="<?php echo htmlspecialchars($doc['title']); ?>">
                        <div class="document" title="<?php echo htmlspecialchars($doc['title']); ?>">
                            <div class="thumbnail" style="background-image: url('data:image/jpeg;base64,<?php echo base64_encode($doc['cover']); ?>');"></div>
                            <p class="title" title="<?php echo htmlspecialchars($doc['title']); ?>"><?php echo htmlspecialchars($doc['title']); ?></p>
                            <p class="year"><?php echo htmlspecialchars($doc['year']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No documents found</p>
            <?php endif; ?>
        </section>
    </div>
</div>
<div id="myModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h2 style="text-align: center;">Select an Option</h2>
    <div class="modal-buttons">
      <button class="btn-old" onclick="window.location.href='old_research.php'">Old Research</button>
      <button class="btn-new" onclick="window.location.href='new_research.php'">New Research</button>
    </div>
  </div>
</div>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <script>
    function showModal() {
      document.getElementById('myModal').style.display = 'block';
    }

    function closeModal() {
      document.getElementById('myModal').style.display = 'none';
    }

    window.onclick = function(event) {
      if (event.target == document.getElementById('myModal')) {
        closeModal();
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

    function searchDocuments() {
      const searchKeyword = document.querySelector('.search-bar').value;
      fetch(`research_list.php?search=${searchKeyword}`) // Update this path if necessary
        .then(response => response.text())
        .then(data => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(data, 'text/html');
          const newContent = doc.querySelector('#documents-section').innerHTML;
          document.getElementById('documents-section').innerHTML = newContent;
        })
        .catch(error => console.error('Error:', error));
    }
  </script>
</body>
</html>