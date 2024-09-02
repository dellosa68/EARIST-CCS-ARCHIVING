<?php
session_start();
include '../conn/db_conn.php';

// Mock login (for demonstration purposes)
// In a real application, replace this with actual login code
if (!isset($_SESSION['account_id'])) {
    // Assuming account_id 1 and username 'demo_user' for demonstration
    $_SESSION['account_id'] = 1;
    $_SESSION['username'] = 'demo_user';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_content']) && isset($_POST['post_title'])) {
    $account_id = $_SESSION['account_id'];
    $title = $_POST['post_title'];
    $content = $_POST['post_content'];

    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $account_id, $title, $content);
    $stmt->execute();
    $stmt->close();
}

// Handle like submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['like_post_id'])) {
    $post_id = $_POST['like_post_id'];
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
    }
    $stmt->close();
}

// Fetch posts
$posts = $conn->query("SELECT p.*, a.username FROM posts p JOIN accounts a ON p.user_id = a.account_id ORDER BY p.created_at DESC");

// Fetch comments
$comments = $conn->query("SELECT c.*, a.username FROM post_comments c JOIN accounts a ON c.user_id = a.account_id");

// Fetch likes count
$likesResult = $conn->query("SELECT post_id, COUNT(*) as like_count FROM likes GROUP BY post_id");
$likes = [];
while ($row = $likesResult->fetch_assoc()) {
    $likes[$row['post_id']] = $row['like_count'];
}

// Fetch user likes
$userLikesResult = $conn->query("SELECT post_id FROM likes WHERE user_id = " . $_SESSION['account_id']);
$userLikes = [];
while ($row = $userLikesResult->fetch_assoc()) {
    $userLikes[] = $row['post_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post and Comment System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9fb;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .form-container, .post, .comment {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .form-container input, .form-container textarea, .form-container button, .comment-form textarea, .comment-form button {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .form-container textarea, .comment-form textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-container button, .comment-form button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .form-container button:hover, .comment-form button:hover {
            background-color: #0056b3;
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            flex-direction: column;
        }

        .post-header h2 {
            font-size: 24px;
            margin: 0;
            font-weight: 600;
        }

        .post-header .post-info {
            color: #999;
            font-size: 14px;
        }

        .post-content {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 10px;
            min-height: 50px;
            border-top: 1px solid #eee;
        }
        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .post-actions .action-buttons {
            display: flex;
            gap: 15px;
        }

        .action-buttons button {
            border: none;
            background: none;
            cursor: pointer;
            color: #007bff;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-buttons button:hover {
            color: #0056b3;
        }
        .comment-section {
    padding: 10px 20px;
    border-top: 1px solid #eee;
}

.comment {
    margin-bottom: 15px;
}

.comment p {
    margin: 0;
    font-size: 14px;
    color: #333;
}

.comment small {
    display: block;
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}

.comment-reply {
    margin-top: 10px;
}

.comment-reply textarea {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ddd;
    resize: vertical;
    min-height: 50px;
    font-size: 14px;
}

.comment-reply button {
    margin-top: 5px;
    padding: 8px 12px;
    border-radius: 8px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.comment-reply button:hover {
    background-color: #0056b3;
}



        @media (max-width: 768px) {
            .post-actions {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-container button, .comment-form button {
                font-size: 14px;
            }

            .post-actions button {
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
   <div class="container">
    <p>Logged in as: <strong><?php echo $_SESSION['username']; ?></strong></p>

    <!-- Form to create a post -->
    <div class="form-container">
        <form action="" method="POST">
            <input type="text" name="post_title" placeholder="Post title" required>
            <textarea name="post_content" placeholder="Write a post..." required></textarea>
            <button type="submit">Post</button>
        </form>
    </div>

    <!-- Display posts -->
    <?php while ($post = $posts->fetch_assoc()): ?>
        <div class="post">
            <div class="post-header">
                <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                <div class="post-info"><?php echo htmlspecialchars($post['username']); ?> &middot; <?php echo $post['created_at']; ?></div>
            </div>
            <div class="post-content">
                <p><?php echo htmlspecialchars($post['content']); ?></p>
            </div>
            <div class="post-actions">
                <div class="action-buttons">
                    <!-- Like button -->
                    <form action="" method="POST" style="display:inline;">
                        <input type="hidden" name="like_post_id" value="<?php echo $post['id']; ?>">
                        <button type="submit">
                            <i class="fas fa-heart"></i> Like (<?php echo $likes[$post['id']] ?? 0; ?>)
                        </button>
                    </form>
                    <!-- Comment button -->
                    <button onclick="toggleCommentForm(<?php echo $post['id']; ?>)">
                        <i class="fas fa-comment"></i> Comment
                    </button>
                </div>
                <div class="report-button">
                    <button><i class="fas fa-flag"></i> Report</button>
                </div>
            </div>

            <!-- Display comments -->
            <div class="comment-section">
                <?php foreach ($comments as $comment): ?>
                    <?php if ($comment['post_id'] == $post['id']): ?>
                        <div class="comment">
                            <p><?php echo htmlspecialchars($comment['content']); ?></p>
                            <small>By: <?php echo htmlspecialchars($comment['username']); ?> on <?php echo $comment['created_at']; ?></small>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Form to comment on the post -->
            <div id="comment-form-<?php echo $post['id']; ?>" class="comment-reply" style="display:none;">
                <form action="" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <textarea name="comment_content" placeholder="Write a comment..."></textarea>
                    <button type="submit">Comment</button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script>
    function toggleCommentForm(postId) {
        const form = document.getElementById('comment-form-' + postId);
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
</script>
</body>
</html>