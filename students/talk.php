<?php
session_start();
include '../conn/db_conn.php';

$username = $_SESSION["username"];
$profile_pic = isset($_SESSION["profile_pic"]) ? $_SESSION["profile_pic"] : null;
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}
if (!isset($_SESSION['account_id'])) {
    $_SESSION['account_id'] = 1;
    $_SESSION['username'] = 'demo_user';
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['account_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);

    // Insert post into database
    $sql = "INSERT INTO posts (user_id, title, content, created_at) VALUES ('$user_id', '$title', '$content', NOW())";

    if ($conn->query($sql) === TRUE) {
        header("Location: talk.php"); // Redirect to the main page after successful post
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch posts
$posts = $conn->query("SELECT p.*, a.username FROM posts p JOIN accounts a ON p.user_id = a.account_id ORDER BY p.created_at DESC");

// Fetch likes count
$likesResult = $conn->query("SELECT post_id, COUNT(*) as like_count FROM likes GROUP BY post_id");
$likes = [];
while ($row = $likesResult->fetch_assoc()) {
    $likes[$row['post_id']] = $row['like_count'];
}

// Fetch comments count
$commentsResult = $conn->query("SELECT post_id, COUNT(*) as comment_count FROM post_comments GROUP BY post_id");
$comments = [];
while ($row = $commentsResult->fetch_assoc()) {
    $comments[$row['post_id']] = $row['comment_count'];
}

$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'latest'; // Default sort by 'latest'

// Prepare SQL query based on sorting option
switch ($sort_by) {
    case 'likes_asc':
        $order_by = 'ORDER BY like_count ASC, created_at DESC';
        break;
    case 'likes_desc':
        $order_by = 'ORDER BY like_count DESC, created_at DESC';
        break;
    case 'comments_asc':
        $order_by = 'ORDER BY comment_count ASC, created_at DESC';
        break;
    case 'comments_desc':
        $order_by = 'ORDER BY comment_count DESC, created_at DESC';
        break;
    case 'latest':
    default:
        $order_by = 'ORDER BY created_at DESC';
        break;
}

$sql_posts = "
    SELECT p.*, a.username, 
    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) as like_count,
    (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = p.id) as comment_count
    FROM posts p
    JOIN accounts a ON p.user_id = a.account_id
    $order_by
";

$posts = $conn->query($sql_posts);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POSTS</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    h1 {
        text-align: center;
    }
    .container {
        max-width: 900px;
        margin: 20px auto;
        padding: 20px;
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
    .post-list-item {
        font-family: 'Poppins', sans-serif;
        background: #ffffff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        text-decoration: none;
        color: #333;
        display: block;
        transition: background-color 0.3s ease;
    }
    .post-list-item:hover {
        background: #f1f1f1;
    }
    .post-list-item h2 {
        font-size: 26px;
        margin: 0;
        font-weight: 600;
    }
    .post-list-item .post-info {
        color: #888;
        font-size: 14px;
        margin-bottom: 10px;
    }
    .post-list-item .post-content {
        font-size: 18px;
        line-height: 1.6;
        margin-bottom: 10px;
    }
    .post-list-item .post-stats {
        display: flex;
        justify-content: space-between;
        color: #888;
    }
    .post-list-item .post-stats span {
        display: flex;
        align-items: center;
    }
    .post-list-item .post-stats i {
        margin-right: 5px;
    }
    .write-post-btn {
        display: block;
        width: 220px;
        margin: 20px auto;
        padding: 12px 24px;
        background: #007bff;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.3s ease;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .write-post-btn:hover {
        background: #0056b3;
        transform: scale(1.05);
    }
    .post-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .post-modal.active {
        display: flex;
    }

    .post-modal-content {
    background: #f9f9f9;
    padding: 30px;
    border-radius: 16px;
    max-width: 600px;
    width: 100%;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.5s ease-out;
}

.post-modal-content h2 {
    margin-top: 0;
    font-size: 32px;
    font-weight: 700;
    color: #333;
    margin-bottom: 20px;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

.post-modal-content input,
.post-modal-content textarea {
    width: 100%;
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    font-family: 'Poppins', sans-serif;
    transition: border-color 0.3s ease;
}

.post-modal-content input:focus,
.post-modal-content textarea:focus {
    border-color: #007bff;
    outline: none;
}

.post-modal-content button {
    display: block;
    padding: 15px;
    color: #0e0e0e;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.post-modal-content button:hover {
    background: #0056b3;
    transform: scale(1.03);
}

.post-modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 32px;
    color: #333;
    cursor: pointer;
    transition: color 0.3s ease;
}

.post-modal-close:hover {
    color: #007bff;
}

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .navbar {
        display: flex;
        padding: 10px 20px;
        width: 100%;
        top: 0;
        z-index: 1;
        transition: background-color 0.3s;
        background-color: rgba(0, 0, 0, 0.8);
    }
    .navbar.scrolled {
        background-color: rgba(0, 0, 0, 0.8);
    }
    .navbar .logo {
        display: flex;
        align-items: center;
    }
    .navbar .logo img {
        height: 60px;
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
    .navbar .profile form {
        margin-left: 10px;
    }
    footer {
        width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
    }
    .sort-btn-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap; 
    }
    .sort-btn-group .sort-btn {
        border: none;
        background: transparent;
        color: #007bff;
        padding: 10px 20px;
        margin: 0 5px;
        border-radius: 5px;
        font-size: 18px;
        cursor: pointer;
        transition: color 0.3s ease, background-color 0.3s ease;
        display: flex;
        align-items: center;
    }
    .sort-btn-group .sort-btn:hover {
        color: #0056b3;
        background: rgba(0, 0, 0, 0.1);
    }
    .sort-btn-group .sort-btn.active {
        color: #0056b3;
        font-weight: bold;
        background: rgba(0, 0, 0, 0.1);
    }
    .sort-btn-group .sort-btn i {
        margin-right: 8px;
    }
    .sort-btn-group .sort-btn span {
        font-size: 16px;
    }
    .write-post-btn {
        border: none;
        background: #007bff;
        color: #fff;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.3s ease;
        margin-left: auto;
    }
    .write-post-btn:hover {
        background: #0056b3;
        transform: scale(1.05);
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
    </div>
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <button class="write-post-btn" onclick="openModal()">Write a Post</button>
            <div class="sort-btn-group">
                <button class="sort-btn <?php echo in_array($sort_by, ['likes_asc', 'likes_desc']) ? 'active' : ''; ?>" onclick="toggleSort('likes')">
                    <i class="fas fa-thumbs-up"></i><span>Likes</span>
                </button>
                <button class="sort-btn <?php echo in_array($sort_by, ['comments_asc', 'comments_desc']) ? 'active' : ''; ?>" onclick="toggleSort('comments')">
                    <i class="fas fa-comments"></i><span>Comments</span>
                </button>
                <!-- Sorting by Date -->
                <button class="sort-btn <?php echo $sort_by === 'latest' ? 'active' : ''; ?>" onclick="location.href='?sort_by=latest'">
                    <i class="fas fa-calendar-day"></i><span>Latest</span>
                </button>
                <button class="sort-btn <?php echo $sort_by === 'oldest' ? 'active' : ''; ?>" onclick="location.href='?sort_by=oldest'">
                    <i class="fas fa-calendar-alt"></i><span>Oldest</span>
                </button>
            </div>
        </div>

        <!-- Display posts -->
        <?php while ($post = $posts->fetch_assoc()): ?>
            <a href="post_details.php?id=<?php echo $post['id']; ?>" class="post-list-item">
                <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                <div class="post-info"><?php echo htmlspecialchars($post['username']); ?> &middot; <?php echo $post['created_at']; ?></div>
                <div class="post-content">
                    <?php echo $post['content']; ?>
                </div>
                <div class="post-stats">
                    <span><i class="fas fa-comment"></i> <?php echo $post['comment_count']; ?> comments</span>
                    <span><i class="fas fa-heart"></i> <?php echo $post['like_count']; ?> likes</span>
                </div>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Post Modal remains unchanged -->
    <div class="post-modal" id="postModal">
        <div class="post-modal-content">
            <button class="post-modal-close" onclick="closeModal()">&times;</button>
            <h2>Write a Post</h2>
            <form action="" method="post">
                <input type="text" name="title" placeholder="Title" required>
                <textarea name="content" rows="5" placeholder="Write your post here..." required></textarea>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
</div>

<footer>
    &copy; 2024 Your Company Name. All rights reserved.
</footer>

<script>
    function openModal() {
        document.getElementById('postModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('postModal').classList.remove('active');
    }

    function toggleSort(type) {
        const currentSort = '<?php echo $sort_by; ?>';
        const newSort = currentSort.includes('asc') || currentSort.includes('desc') 
            ? (type === 'likes' ? (currentSort === 'likes_desc' ? 'likes_asc' : 'likes_desc') 
            : (currentSort === 'comments_desc' ? 'comments_asc' : 'comments_desc')) 
            : `${type}_desc`;
        location.href = `?sort_by=${newSort}`;
    }
</script>

</body>
</html>