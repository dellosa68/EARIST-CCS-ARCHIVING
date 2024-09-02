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

$sql = "SELECT account_id, fname, lname, pic FROM instructors";
if ($searchKeyword) {
    $sql .= " WHERE fname LIKE '%" . $conn->real_escape_string($searchKeyword) . "%' 
              OR lname LIKE '%" . $conn->real_escape_string($searchKeyword) . "%'";
}

$result = $conn->query($sql);

$instructors = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $instructors[] = $row;
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
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f0f0f0;
    }

    h1 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 2rem;
        color: #333;
    }

    .header {
        height:150px;
            }

    .header h2 {
        font-size: 1.5rem;
        margin: 0;
    }

    .logo img {
        height: 60px;
        margin-right: 10px;
    }

    .container {
        margin: 0 auto;
        padding: 0 15px;
    }

    .search-bar {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ced4da;
        border-radius: 30px;
        margin-bottom: 20px;
        transition: all 0.3s;
    }

    .search-bar:focus {
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
    }

    .documents {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }

    .document {
        background-color: #ffffff;
        border: 1px solid #ced4da;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .document:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .thumbnail {
        width: 100%;
        height: 200px;
        background-color: #e9ecef;
        background-size: cover;
        background-position: center;
    }

    .thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .info {
        padding: 15px;
    }

    .info h4 {
        margin-top: 0;
        font-size: 1.2rem;
        color: #333;
    }

    .info p {
        margin-bottom: 0;
        font-size: 0.9rem;
        color: #666;
    }

    .details-link {
        display: block;
        text-align: center;
        padding: 10px 0;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 0 0 8px 8px;
        transition: background-color 0.3s;
    }

    .details-link:hover {
        background-color: #0056b3;
    }

    .profile-img {
        width: 100%;
        height: auto;
        border-radius: 50%;
    }

    
    .footer p {
        margin: 0;
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
                        <a href="talk.php">Talk</a>
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
                    <img src="../images/default_profile.jpg" alt="Default Profile Picture">
                </a>
            <?php endif; ?>
            <form method="post" style="margin-left: 10px;">
                <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
            </form>
        </div>

    </nav>
<div id="main-content">
    <div class="header">
        
    </div>
    <div class="container">
        <h1>Instructors</h1>
        <input type="text" placeholder="Search by first name or last name" class="search-bar" oninput="searchInstructors()">
        <section class="documents" id="instructors-section">
            <?php if (count($instructors) > 0): ?>
                <?php foreach ($instructors as $instructor): ?>
                    <div class="document">
                        <div class="thumbnail">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($instructor['pic']); ?>" alt="Profile Picture">
                        </div>
                        <div class="info">
                            <h4><?php echo htmlspecialchars($instructor['fname'] . ' ' . $instructor['lname']); ?></h4>
                            <p>Instructor</p>
                        </div>
                        <a href="instructor_details.php?account_id=<?php echo $instructor['account_id']; ?>" class="details-link">View Details</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No instructors found</p>
            <?php endif; ?>
        </section>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p>&copy; 2024 Your Company Name. All rights reserved.</p>
    </div>
</footer>

<script>
    function searchInstructors() {
        const searchKeyword = document.querySelector('.search-bar').value;
        fetch(`instructors.php?search=${searchKeyword}`)
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/html');
                const newContent = doc.querySelector('#instructors-section').innerHTML;
                document.getElementById('instructors-section').innerHTML = newContent;
            })
            .catch(error => console.error('Error:', error));
    }
</script>

</body>
</html>
