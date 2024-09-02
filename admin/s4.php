<?php
session_start();
include '../conn/db_conn.php';

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
    header("Location: s4.php?id=$post_id");
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f2f4f6;
    color: #333;
    margin: 0;
    padding: 0;
}

.container {
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

    </style>
</head>
<body>
    <div class="container">
        <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>

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
    <script>
        function toggleCommentForm(postId) {
            // Add your JavaScript logic to toggle the comment form if needed
        }
    </script>
</body>
</html>