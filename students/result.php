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

require_once '../conn/db_conn.php'; // Include database connection

$category = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';

$sql = "SELECT * FROM document WHERE ";
$params = [];
$types = '';

if ($category == "all") {
    $sql .= "(title LIKE ? OR author LIKE ? OR keywords LIKE ? OR adviser LIKE ? OR panel LIKE ?)";
    $params = [$searchQuery, $searchQuery, $searchQuery, $searchQuery, $searchQuery];
    $types = str_repeat('s', count($params));
} elseif ($category == "research") {
    $sql .= "title LIKE ?";
    $params = [$searchQuery];
    $types = 's';
} elseif ($category == "instructors") {
    $sql .= "(adviser LIKE ? OR panel LIKE ?)";
    $params = [$searchQuery, $searchQuery];
    $types = 'ss';
} elseif ($category == "authors") {
    $sql .= "author LIKE ?";
    $params = [$searchQuery];
    $types = 's';
} elseif ($category == "keywords") {
    $sql .= "keywords LIKE ?";
    $params = [$searchQuery];
    $types = 's';
}

$stmt = $conn->prepare($sql);
$searchParam = "%" . $searchQuery . "%";

// Dynamically bind parameters
if (!empty($params)) {
    $stmt->bind_param($types, ...array_fill(0, count($params), $searchParam));
}

$stmt->execute();
$result = $stmt->get_result();

$filteredResults = [];
while ($row = $result->fetch_assoc()) {
    $filteredResults[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <!-- External CSS libraries -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Custom styles */
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #d32f2f;
            color: white;
            padding: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
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
            width: 100%;
            margin: 20px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .container h2 {
            font-size: 28px;
            color: #333;
            text-align: center;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .search-form {
            margin-bottom: 20px;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        .card {
            background-color: #fff;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: box-shadow 0.3s, transform 0.3s;
        }
        .card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }
        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #ddd;
        }
        .card-body {
            padding: 15px;
        }
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .card-text {
            font-size: 14px;
            color: #777;
            text-align: center;
        }
        .no-results {
            text-align: center;
            color: #666;
            font-size: 18px;
            margin-top: 50px;
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
    <div class="main-content" id="main-content">
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
                    <img src="../images/default_profile.jpgg" alt="Default Profile Picture">
                </a>
            <?php endif; ?>

            <form method="post" style="margin-left: 10px;">
                <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
            </form>
        </div>
    </nav>
        <div class="container">
            <form action="result.php" method="GET" class="search-form">
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category" class="form-control">
                        <option value="all" <?php echo ($category == 'all') ? 'selected' : ''; ?>>All</option>
                        <option value="research" <?php echo ($category == 'research') ? 'selected' : ''; ?>>Research Title</option>
                        <option value="instructors" <?php echo ($category == 'instructors') ? 'selected' : ''; ?>>Instructors</option>
                        <option value="authors" <?php echo ($category == 'authors') ? 'selected' : ''; ?>>Authors</option>
                        <option value="keywords" <?php echo ($category == 'keywords') ? 'selected' : ''; ?>>Keywords</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="search">Search:</label>
                    <input type="text" name="search" id="search" class="form-control" value="<?php echo htmlspecialchars($searchQuery); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>
            <?php if (empty($filteredResults)): ?>
                <p class="no-results">No results found.</p>
            <?php else: ?>
                <div class="grid-container">
                    <?php foreach ($filteredResults as $result): ?>
                        <a href="research_details.php?research_id=<?php echo $result['research_id']; ?>" title="<?php echo htmlspecialchars($result['title']); ?>">
                        <div class="card" data-research-id="<?php echo htmlspecialchars($result['research_id']); ?>">
                            <?php if (!empty($result['cover'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($result['cover']); ?>" alt="Cover Image">
                            <?php else: ?>
                                <img src="../images/default_cover.png" alt="Default Cover Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($result['title']); ?>
                                </h5>
                                <p class="card-text">Year: <?php echo htmlspecialchars($result['year']); ?></p>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
<script>
    // Add any additional JavaScript here if needed
</script>
</body>
</html>
