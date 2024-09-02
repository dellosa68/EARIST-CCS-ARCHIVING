<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Get the username and profile picture from the session
$username = isset($_SESSION["username"]) ? $_SESSION["username"] : null;
$profile_pic = isset($_SESSION["profile_pic"]) ? $_SESSION["profile_pic"] : null;

// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

if (isset($_POST['logout'])) {
    session_destroy(); // Destroy all sessions
    header("Location: ../index.php");
    exit();
}
include '../conn/db_conn.php';

// Retrieve the research_id from the URL query parameters
if (isset($_GET['research_id'])) {
    $research_id = $_GET['research_id'];

    // Fetch details of the specific research document from the database using $research_id
    $sql = "SELECT * FROM document WHERE research_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $research_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $doc = $result->fetch_assoc();
        // Display the title, author, author_id, cover, and abstract of the research document
        $title = htmlspecialchars($doc['title']);
        $author = htmlspecialchars($doc['author']);
        $author_ids = htmlspecialchars($doc['author_id']); // Assuming author_id is a comma-separated string
        $keyword = htmlspecialchars($doc['keywords']);
        $year = htmlspecialchars($doc['year']);
        $cover = base64_encode($doc['cover']);
        $abstract = htmlspecialchars($doc['abstract']);
        $course = htmlspecialchars($doc['course']);
        $date_published = htmlspecialchars($doc['date_published']);
        $panel = htmlspecialchars($doc['panel']);
        $approval_sheet = base64_encode($doc['approval_sheet']);
        $adviser = htmlspecialchars($doc['adviser']);

        // Check if the document is bookmarked by the current user
        $username = $_SESSION['username'];
        $bookmark_check_sql = "SELECT * FROM bookmarks WHERE username = ? AND research_id = ?";
        $bookmark_check_stmt = $conn->prepare($bookmark_check_sql);
        $bookmark_check_stmt->bind_param("si", $username, $research_id);
        $bookmark_check_stmt->execute();
        $bookmark_check_result = $bookmark_check_stmt->get_result();
        $isBookmarked = $bookmark_check_result->num_rows > 0;

        // Fetch details of each author
        $author_ids_array = explode(',', $author_ids);
        $authors = [];
        foreach ($author_ids_array as $author_id) {
            $student_sql = "SELECT * FROM student WHERE student_id = ?";
            $student_stmt = $conn->prepare($student_sql);
            $student_stmt->bind_param("i", $author_id);
            $student_stmt->execute();
            $student_result = $student_stmt->get_result();

            if ($student_result->num_rows > 0) {
                $student = $student_result->fetch_assoc();
                $authors[] = [
                    'student_id' => htmlspecialchars($student['student_id']),
                    'first_name' => htmlspecialchars($student['first_name']),
                    'last_name' => htmlspecialchars($student['last_name']),
                    'email' => htmlspecialchars($student['email']),
                    'phone' => htmlspecialchars($student['phone']),
                    'student_pic' => base64_encode($student['profile_pic']),
                ];
            }
        }

        // Fetch adviser_id
        $adviser_sql = "SELECT adviser_id FROM document WHERE research_id = ?";
        $adviser_stmt = $conn->prepare($adviser_sql);
        $adviser_stmt->bind_param("i", $research_id);
        $adviser_stmt->execute();
        $adviser_result = $adviser_stmt->get_result();
        $adviser_data = $adviser_result->fetch_assoc();
        $adviser_id = $adviser_data['adviser_id'];

        // Fetch adviser details from instructors table
        $instructor_sql = "SELECT * FROM instructors WHERE account_id = ?";
        $instructor_stmt = $conn->prepare($instructor_sql);
        $instructor_stmt->bind_param("i", $adviser_id);
        $instructor_stmt->execute();
        $instructor_result = $instructor_stmt->get_result();
        $adviser = $instructor_result->fetch_assoc();

        // Fetch panelists details
        $panelists_sql = "SELECT * FROM document WHERE research_id = ?";
        $panelists_stmt = $conn->prepare($panelists_sql);
        $panelists_stmt->bind_param("i", $research_id);
        $panelists_stmt->execute();
        $panelists_result = $panelists_stmt->get_result();
        $panelists = $panelists_result->fetch_all(MYSQLI_ASSOC);
    } else {
        $title = "Document not found";
        $author = "";
        $authors = [];
        $cover = "";
        $abstract = "";
        $adviser = "";
        $panelists = [];
        $course = "";
        $date_published = "";
    }
} else {
    $title = "Invalid research ID";
    $author = "";
    $authors = [];
    $cover = "";
    $abstract = "";
    $adviser = [];
    $panelists = [];
    $course = "";
    $date_published = "";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
    <title>Research Details</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        
        .open-btn {
            border: none;
            color: white;
            padding: 10px 10px;
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
        .breadcrumb-container {
    background-color: #f8f9fa;
    padding: 10px 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.breadcrumb {
    margin-bottom: 0;
    padding: 0;
    background-color: transparent;
    font-size: 1rem;
}

.breadcrumb-item {
    display: inline-block;
    font-size: 1rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "/";
    padding: 0 8px;
}

.breadcrumb-item a {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
}

.breadcrumb-item a:hover {
    text-decoration: underline;
}

.breadcrumb-item.active {
    color: #6c757d;
    font-weight: 400;
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
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .tabs a {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            color: #007bff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s, color 0.3s;
        }
        .tabs a.active, .tabs a:hover {
            background-color: #007bff;
            color: white;
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
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            color: black;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: #ddd;
        }
        .container {
            margin-top: 30px;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .title {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        .title img {
            max-width: 180px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .title-section h1 {
            margin: 0;
            font-size: 2rem;
            color: #333;
        }
        .title-section small {
            display: block;
            color: #6c757d;
        }
        .title-section a {
            color: #007bff;
            text-decoration: none;
        }
        .title-section a:hover {
            text-decoration: underline;
        }
        .author-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            text-align: center;
        }
        .author-container img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 10px;
            object-fit: cover;
        }
        .author-container h4 {
            margin: 10px 0;
            font-size: 1.2em;
            font-weight: 700;
        }
        .author-container p {
            margin: 5px 0;
            font-size: 0.9em;
            color: #555;
        }
        .bookmark-icon {
            font-size: 24px;
            cursor: pointer;
            transition: color 0.3s, transform 0.3s;
        }
        .bookmark-icon.bookmarked {
            color: #ff5722;
        }
        .bookmark-icon:hover {
            transform: scale(1.2);
        }
        small {
            display: block;
            margin-top: 5px;
            color: #888;
            font-size: 15px;
        }
        .title-section h1 {
            font-size: 2.5em;
            font-weight: 700;
            color: #333;
            margin: 0 0 10px;
        }
        .title-section small {
            font-size: 1em;
            color: #666;
        }
        .abstract-section {
            margin-bottom: 30px;
        }
        .abstract-section h3 {
            margin-top: 20px;
            font-size: 1.5rem;
            color: #333;
        }
        .abstract-section p {
            font-size: 1rem;
            line-height: 1.6;
            color: #555;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        .gallery img {
            border-radius: 12px;
            width: 100%;
            height: 200px;
        }
        .gallery a {
            display: inline-block;
            margin: 0 10px 10px 0;
        }
        .gallery a img {
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .gallery a:hover img {
            transform: scale(1.05);
        }

        footer {
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            font-family: 'Times New Roman', serif;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s, transform 0.3s;
        }
        .card:hover {
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }
        .card-img-top {
            width: 100%;
            height: 400px; /* Set a fixed height */
            object-fit: cover; /* Ensure the image covers the area without distortion */
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

    </style>
</head>
<body>
    
    <div class="main-content" id="main-content">
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
            <form method="post">
                <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
            </form>
        </div>
    </nav>
        
        <div class="breadcrumb-container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="home.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="research.php">Research List</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $title; ?></li>
                </ol>
            </nav>
        </div>
        <div class="container">
        <div class="title">
            <img src="data:image/jpeg;base64,<?php echo $cover; ?>" alt="Cover Image">
            <div class="title-section">
                <h1><?php echo htmlspecialchars($title); ?></h1>
                <small>by: <?php echo htmlspecialchars($author); ?> • <?php echo htmlspecialchars($year); ?></small>
                <small>Keywords: 
                    <?php 
                    $keywordsArray = explode(',', $keyword);
                    foreach ($keywordsArray as $kw) {
                        echo '<a href="keywords.php?keyword=' . urlencode(trim($kw)) . '">' . htmlspecialchars(trim($kw)) . '</a> ';
                    }
                    ?>
                </small>
                <i id="bookmark-icon" class="fas fa-bookmark bookmark-icon <?php echo $isBookmarked ? 'bookmarked' : ''; ?>" onclick="bookmarkResearch(<?php echo $research_id; ?>, '<?php echo addslashes($title); ?>')"></i>
            </div>
        </div>
        <div class="tabs">
            <a href="#overview" class="active">Overview</a>
            <a href="#authors">Authors</a>
            <a href="#panelist">Panelist</a>
            <a href="#more">More</a>
        </div>
        <div id="overview" class="content active">
            <div class="abstract-section">
                <h4><strong>Panelists: </strong> <?php echo htmlspecialchars($panel); ?></h4>
                <h4><strong>Course: </strong> <?php echo htmlspecialchars($course); ?></h4>
                <h4><strong>Date Published: </strong> <?php echo htmlspecialchars($date_published); ?></h4>
                <h3>Abstract</h3>
                <p><?php echo htmlspecialchars($abstract); ?></p>
            </div>
        </div>
        <div id="authors" class="content">
            <div class="row">
                <?php foreach ($authors as $author): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card">
                            <img src="data:image/jpeg;base64,<?php echo $author['student_pic']; ?>" class="card-img-top" alt="Student Picture">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($author['first_name'] . ' ' . $author['last_name']); ?></h5>
                                <p class="card-text"><strong>Student ID:</strong> <?php echo htmlspecialchars($author['student_id']); ?></p>
                                <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($author['email']); ?></p>
                                <p class="card-text"><strong>Phone:</strong> <?php echo htmlspecialchars($author['phone']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div id="panelist" class="content">
            <div id="adviser">
                <h3>Adviser</h3>
                <?php if ($adviser): ?>
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-4">
                            <a href="instructor_details.php?account_id=<?= htmlspecialchars($adviser['account_id']) ?>" style="text-decoration: none; color: inherit;">
                                <div class="card">
                                    <img src="data:image/jpeg;base64,<?= base64_encode($adviser['pic']) ?>" class="card-img-top" alt="Adviser Picture">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($adviser['title'] . ' ' . $adviser['fname'] . ' ' . $adviser['lname'] . ' ' . $adviser['post_nominal']) ?></h5>
                                        <p class="card-text"><strong>Email:</strong> <?= htmlspecialchars($adviser['email']) ?></p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <p>No adviser assigned.</p>
                <?php endif; ?>
            </div>
            <div id="panelists">
                <h3>Panelists</h3>
                <?php if (!empty($panelists)): ?>
                    <div class="row">
                        <?php
                        // Split panelist IDs into an array
                        $panelist_ids_array = explode(',', $doc['panel_id']);
                        
                        // Loop through each panelist ID
                        foreach ($panelist_ids_array as $panelist_id):
                            // Fetch panelist details from database
                            $panelist_sql = "SELECT * FROM instructors WHERE account_id = ?";
                            $panelist_stmt = $conn->prepare($panelist_sql);
                            $panelist_stmt->bind_param("i", $panelist_id);
                            $panelist_stmt->execute();
                            $panelist_result = $panelist_stmt->get_result();

                            if ($panelist_result->num_rows > 0):
                                $panelist = $panelist_result->fetch_assoc();
                                ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <a href="instructor_details.php?account_id=<?= htmlspecialchars($panelist['account_id']) ?>" style="text-decoration: none; color: inherit;">
                                        <div class="card">
                                            <img src="data:image/jpeg;base64,<?= base64_encode($panelist['pic']) ?>" class="card-img-top" alt="Panelist Picture">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($panelist['fname'] . ' ' . $panelist['lname']); ?></h5>
                                                <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($panelist['email']); ?></p>
                                                <!-- Add more fields as needed -->
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No panelists assigned.</p>
                <?php endif; ?>
            </div>
        </div>
        <div id="more" class="content">
    <h3>Gallery</h3>
    <div class="container">
        <div class="row">
            <?php if ($cover): ?>
                <div class="col-md-4 mb-4">
                    <a href="data:image/jpeg;base64,<?= $cover ?>" data-lightbox="gallery" data-title="Cover Image">
                        <img src="data:image/jpeg;base64,<?= $cover ?>" alt="Cover" class="img-thumbnail">
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($approval_sheet): ?>
                <div class="col-md-4 mb-4">
                    <a href="data:image/jpeg;base64,<?= $approval_sheet ?>" data-lightbox="gallery" data-title="Approval Sheet">
                        <img src="data:image/jpeg;base64,<?= $approval_sheet ?>" alt="Approval Sheet" class="img-thumbnail">
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    </div>
    </div>
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.1/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.tabs a').click(function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                $('.tabs a').removeClass('active');
                $(this).addClass('active');
                $('.content').removeClass('active');
                $(target).addClass('active');
            });
        });

        function bookmarkResearch(id, title) {
            // Handle bookmarking functionality
            console.log('Bookmarking:', id, title);
            // Example AJAX request
            $.post('bookmark.php', { id: id, title: title }, function(response) {
                $('#bookmark-icon').toggleClass('bookmarked');
                // Handle response
            });
        }
       
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });
    </script>
</body>
</html>
