<?php
include '../conn/db_conn.php';
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"];
$profile_pic = isset($_SESSION["profile_pic"]) ? $_SESSION["profile_pic"] : null;
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

if (isset($_POST['delete'])) {
    $research_id = $_POST['research_id'];
    $delete_sql = "DELETE FROM bookmarks WHERE research_id = ? AND username = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("is", $research_id, $username);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: bookmarks.php");
    exit();
}

$sql = "SELECT b.research_id, d.cover, d.title, d.author, d.year FROM bookmarks b JOIN document d ON b.research_id = d.research_id WHERE b.username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookmarked Research</title>
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

    #main-content {
      transition: margin-left 0.5s;
      margin-left: 50px;
    }

    h1 {
      text-align: center;
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
      /* Set background color to transparent */
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
        .main-content {
            transition: margin-left 0.5s;
        }
        .header {
            color: white;
            padding: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 150px;
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
        .container {
            padding: 20px;
        }
        .documents {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }

        .card {
            background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 15px;
        text-align: center;
        transition: box-shadow 0.3s, transform 0.3s;
        max-height: 450px; /* Set a maximum height for the document card */
        overflow: hidden; /* Hide overflow */
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .card img {
           width: 100%;
        min-height: 200px;
        background-color: #e0e0e0;
        margin-bottom: 10px;
        border-radius: 5px;
        background-size: cover;
        background-position: center;
        }
        .card-title {
            font-size: 16px;
        font-weight: 600;
        color: #333;
        white-space: nowrap; /* Prevent text from wrapping */
        overflow: hidden; /* Hide overflow */
        text-overflow: ellipsis; 
    
        }
        .card-text {
            text-overflow: ellipsis; 
           font-size: 14px;
        color: #777;
        }
        .btn-primary {
            background-color: #d32f2f;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
            font-size: 14px;
        }
        .btn-primary:hover {
            background-color: #FF7575;
        }
        .btn-icon {
            background: none;
            border: none;
            padding: 0;
            color: #d32f2f;
            cursor: pointer;
            margin-left: 10px;
        }
        .btn-icon:hover {
            color: #FF7575;
        }
        .card-body .button-group {
            display: flex;
            align-items: center;
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
        #sidebar.minimized a .text {
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
                <a href="profile.php">
                    <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
                </a>
            <?php else: ?>
                <a href="perofile.php">
                    <img src="../images/default_profile.jpg" alt="Default Profile Picture">
                </a>
            <?php endif; ?>

            <form method="post" style="margin-left: 10px;">
                <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
            </form>
        </div>
    </nav>
        <div class="container">
            <h2>Bookmarked Research</h2>
            <section class="documents" id="documents-section">
                <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="card">
                            <img src="data:image/jpeg;base64,<?= base64_encode($row['cover']) ?>" alt="Cover Image">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="card-text"><strong>Year:</strong> <?= htmlspecialchars($row['year']) ?></p>
                                <div class="button-group">
                                    <a href="research_details.php?research_id=<?= $row['research_id'] ?>" class="btn btn-primary">View Details</a>
                                    <form method="post" class="d-inline-block">
                                        <input type="hidden" name="research_id" value="<?= $row['research_id'] ?>">
                                        <button type="submit" name="delete" class="btn-icon"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    
                <?php endwhile; ?>
            </section>
        </div>
    </div>
    <script>
        
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
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>