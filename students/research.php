<?php

session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Get the username and profile picture from the session
$username = $_SESSION["username"];
$profile_pic = isset($_SESSION["profile_pic"]) ? $_SESSION["profile_pic"] : null;

// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

if (isset($_POST['logout'])) {
    session_destroy(); // Destroy all sessions
    header("Location: ../index.php");
    exit();
}

include '../conn/db_conn.php';

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT research_id, title, cover, year FROM document";
if ($searchKeyword) {
    $searchKeywordEscaped = $conn->real_escape_string($searchKeyword);
    $sql .= " WHERE title LIKE '%$searchKeywordEscaped%' 
              OR author LIKE '%$searchKeywordEscaped%' 
              OR year LIKE '%$searchKeywordEscaped%'
              OR adviser LIKE '%$searchKeywordEscaped%'
              OR panel LIKE '%$searchKeywordEscaped%'";
}

$result = $conn->query($sql);

$documents = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }
}

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

    .header {
    }
    footer {
            margin-top: 50px;
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
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

<div id="main-content">
        <div class="header" style="display: block;">
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
    <header>
  </div>

    <div class="container">
        <div style="display: flex; justify-content: center; margin-top: 20px;">
            <input class="search-bar" type="text" placeholder="Search documents..." onkeyup="searchDocuments()">
            <a href="publish_research.php" style="margin-left: 20px;">
            </a>
        </div>

        <div class="documents" id="documents-section">
            <?php foreach ($documents as $document): ?>
                <div class="document">
                    <a href="research_details.php?research_id=<?php echo $document['research_id']; ?>">
                        <div class="thumbnail" style="background-image: url('data:image/jpeg;base64,<?php echo base64_encode($document['cover']); ?>');"></div>
                        <div class="title"><?php echo htmlspecialchars(trim($document['title'])); ?></div>
                        <div class="year"><?php echo htmlspecialchars(trim($document['year'])); ?></div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2023 EARIST College of Computing Studies Research Archiving System. All rights reserved.</p>
</footer>

<script>
    let debounceTimer;
    function searchDocuments() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchKeyword = document.querySelector('.search-bar').value;
            fetch(`research.php?search=${searchKeyword}`)
                .then(response => response.text())
                .then(data => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data, 'text/html');
                    const newContent = doc.querySelector('#documents-section').innerHTML;
                    document.getElementById('documents-section').innerHTML = newContent;
                })
                .catch(error => console.error('Error:', error));
        }, 300);
    }

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 0) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
</script>

</body>
</html>
