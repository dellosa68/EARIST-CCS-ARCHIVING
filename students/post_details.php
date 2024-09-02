<?php
session_start();
include '../conn/db_conn.php';

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
// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

if (!isset($_SESSION['account_id'])) {
    // Assuming account_id 1 and username 'demo_user' for demonstration
    $_SESSION['account_id'] = 1;
    $_SESSION['username'] = 'demo_user';
}

$post_id = intval($_GET['id']);

// Fetch post details
$stmt = $conn->prepare("SELECT p.*, a.username FROM posts p JOIN accounts a ON p.user_id = a.account_id WHERE p.id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch comments
$comments = $conn->query("SELECT c.*, a.username FROM post_comments c JOIN accounts a ON c.user_id = a.account_id WHERE c.post_id = $post_id ORDER BY c.created_at DESC");

// Fetch likes count
$likesResult = $conn->query("SELECT COUNT(*) as like_count FROM likes WHERE post_id = $post_id");
$likeCount = $likesResult->fetch_assoc()['like_count'];

$commentResult = $conn->query("SELECT COUNT(*) as comment_count FROM post_comments WHERE post_id = $post_id");
$commentCount = $commentResult->fetch_assoc()['comment_count'];

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_content'])) {
    $account_id = $_SESSION['account_id'];
    $comment_content = $conn->real_escape_string($_POST['comment_content']);

    $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $account_id, $comment_content);
    $stmt->execute();
    $stmt->close();

    // Refresh page to show new comment
    header("Location: post_details.php?id=$post_id");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['like_post_id'])) {
    $post_id = intval($_POST['like_post_id']);
    $account_id = $_SESSION['account_id'];

    // Check if the user has already liked this post
    $stmt = $conn->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // User has not liked the post yet, insert the like
        $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $post_id, $account_id);
        $stmt->execute();
        
        // Update like count
        $likeCount++;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f2f4f6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            font-family: 'Poppins', sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .post {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .post h2 {
            font-size: 28px;
            margin: 0;
            font-weight: 600;
            color: #333;
        }

        .post .post-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .post .post-content {
            font-size: 18px;
            line-height: 1.8;
            color: #444;
        }

        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
        }

        .action-buttons button {
            border: none;
            background: none;
            cursor: pointer;
            color: #007bff;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }

        .action-buttons button:hover {
            color: #0056b3;
        }

        .report-button {
            display: flex;
            align-items: center;
        }

        .report-button button {
            border: none;
            background: none;
            color: #dc3545;
            font-size: 16px;
            cursor: pointer;
        }

        .report-button button:hover {
            color: #c82333;
        }

        .comment-section {
            padding: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .comment {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .comment p {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .comment small {
            display: block;
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .comment-reply {
            margin-top: 30px;
        }

        .comment-reply textarea {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            resize: vertical;
            min-height: 60px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .comment-reply button {
            margin-top: 10px;
            padding: 10px 15px;
            border-radius: 8px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .comment-reply button:hover {
            background-color: #0056b3;
        }
        .navbar {
            display: block;
            padding: 10px 20px;
            width: 100%;
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
            padding: 10px 20px;
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

        .back-button {
            margin-top: 20px;
            display: flex;
            justify-content: flex-start;
        }

        .back-button button {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }

        .back-button button:hover {
            background-color: #0056b3;
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
            <div class="row">
                <div class="col-md-9">
                    <div class="menu">
                        <a href="home.php">Home</a>
                        <a href="profile.php">Profile</a>
                        <a href="talk.php">Talk</a>
                        <a href="research.php">Documents</a>
                        <a href="publish_research.php">Publish a Research</a>
                        <a href="bookmarks.php">Bookmarks</a>
                    </div>
                </div>
                <div class="col-md-3" style="display: flex; justify-content: flex-end;">
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
                </div>
            </div>
        </nav>
        <div class="container">
            <div class="back-button">
                <button onclick="window.history.back();">Back</button>
            </div>
            <div class="post">
                <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                <div class="post-info"><?php echo htmlspecialchars($post['username']); ?> &middot; <?php echo htmlspecialchars($post['created_at']); ?></div>
                <div class="post-content">
                    <p><?php echo htmlspecialchars($post['content']); ?></p>
                </div>
                <div class="post-actions">
                    <div class="action-buttons">
                        <!-- Like button -->
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="like_post_id" value="<?php echo htmlspecialchars($post['id']); ?>">
                            <button type="submit">
                                <i class="fas fa-heart"></i> Like (<?php echo htmlspecialchars($likeCount); ?>)
                            </button>
                        </form>
                        <!-- Comment button -->
                        <button onclick="toggleCommentForm(<?php echo htmlspecialchars($post['id']); ?>)">
                            <i class="fas fa-comment"></i> Comment (<?php echo htmlspecialchars($commentCount); ?>)
                        </button>
                    </div>
                    <div class="report-button">
                        <button><i class="fas fa-flag"></i> Report</button>
                    </div>
                </div>
            </div>
            <!-- Display comments -->
            <div class="comment-section">
                <?php while ($comment = $comments->fetch_assoc()): ?>
                    <div class="comment">
                        <p><?php echo htmlspecialchars($comment['content']); ?></p>
                        <small>By: <?php echo htmlspecialchars($comment['username']); ?> on <?php echo htmlspecialchars($comment['created_at']); ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
            <!-- Form to comment on the post -->
            <div class="comment-reply">
                <form action="" method="POST">
                    <textarea name="comment_content" placeholder="Write a comment..." required></textarea>
                    <button type="submit">Comment</button>
                </form>
            </div>
        </div>
    </div>
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
    <script>
        function toggleCommentForm(postId) {
            // Add your JavaScript logic to toggle the comment form if needed
        }
    </script>
</body>
</html>