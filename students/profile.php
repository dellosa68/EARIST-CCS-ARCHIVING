<?php
session_start(); // Start the session

// Check if the user is logged in
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Include database connection
include('../conn/db_conn.php');

// Get the username from the session
$username = $_SESSION["username"];
$account_id = $_SESSION["account_id"] ?? 1; // Default account_id for demo user

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['title']) && isset($_POST['content'])) {
        // Handle post creation
        $user_id = $_SESSION['account_id'];
        $title = $conn->real_escape_string($_POST['title']);
        $content = $conn->real_escape_string($_POST['content']);

        $sql = "INSERT INTO posts (user_id, title, content, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iss', $user_id, $title, $content);

        if ($stmt->execute()) {
            header("Location: profile.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } elseif (isset($_POST['student_id'])) {
        // Handle profile update
        $student_id = $_POST['student_id'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $course = $_POST['course'];
        $year = $_POST['year'];
        $section = $_POST['section'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];

        $update_query = "UPDATE student SET first_name = ?, last_name = ?, email = ?, phone = ?, password = ?, course = ?, year = ?, section = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('ssssssss', $first_name, $last_name, $email, $phone, $password, $course, $year, $section, $username);


        if ($update_stmt->execute()) {
            $_SESSION['message'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update profile!";
        }
    }
}

$query = "SELECT * FROM student WHERE account_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $account_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

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
    WHERE p.user_id = ?
    $order_by
";

$posts_stmt = $conn->prepare($sql_posts);
$posts_stmt->bind_param('i', $user['account_id']);
$posts_stmt->execute();
$posts_result = $posts_stmt->get_result();

// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $user['profile_pic'] ? base64_encode($user['profile_pic']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
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
        .main-content {
        }
        .header {
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
            
        }
        .profile-pic {
            text-align: center;
            margin: 20px;
            display: flex;
            justify-content: center;
        }
        .profile-pic img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }
        .profile-pic img:hover {
            transform: scale(1.05);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
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
            padding: 10px 20px;
            width: 100%;
            position: relative;
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
        .card{
            position: relative;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid rgba(0, 0, 0, .125);
            border-radius: .25rem;
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
            text-align: left;
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
                        <img src="../images/default_profile.jpg" alt="Default Profile Picture">
                    </a>
                <?php endif; ?>
                <form method="post">
                    <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
                </form>
            </div>
        </nav>
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="profile-pic">
                            <?php if ($profile_pic_base64): ?>
                                <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
                            <?php else: ?>
                                <img src="../images/default_profile.jpg" alt="Default Profile Picture">
                            <?php endif; ?>
                        </div>
                        <div>
                            <h2 style="padding: 0"><?php echo htmlspecialchars($user['first_name'].' '. $user['last_name']); ?> <br> <small style="font-size: 15px; font-weight: lighter; color: gray;"><?php echo htmlspecialchars($user['username']); ?></small></h2>
                        </div>
                        <p style="margin: 10px;"><strong>Student No: </strong><?php echo htmlspecialchars($user['account_id']); ?></p>
                        <p style="margin: 10px;"><strong>Student No: </strong><?php echo htmlspecialchars($user['student_id']); ?></p>
                        <p style="margin: 10px;"><strong>Email: </strong><?php echo htmlspecialchars($user['email']); ?></p>
                        <button class='btn btn-primary' data-toggle='modal' data-target='#editModal'>Edit</button>
                    </div>
                </div>
                <div class="col-md-8">
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
                    <?php
                    if ($posts_result->num_rows > 0): 
                        while ($post = $posts_result->fetch_assoc()): ?>
                            <a href="post_details.php?id=<?php echo $post['id']; ?>" class="post-list-item">
                                <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                                <div class="post-info"><?php echo htmlspecialchars($post['username']); ?> &middot; <?php echo $post['created_at']; ?></div>
                                <div class="post-content">
                                    <?php echo htmlspecialchars($post['content']); ?>
                                </div>
                                <div class="post-stats">
                                    <span><i class="fas fa-comment"></i> <?php echo isset($post['comment_count']) ? $post['comment_count'] : 0; ?> comments</span>
                                    <span><i class="fas fa-heart"></i> <?php echo isset($post['like_count']) ? $post['like_count'] : 0; ?> likes</span>
                                </div>
                            </a>
                        <?php endwhile; 
                    else: ?>
                        <p>No posts found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Profile Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm" action="" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="student_id">Student ID</label>
                            <input type="text" id="student_id" name="student_id" class="form-control" value="<?php echo htmlspecialchars($user['student_id']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" class="form-control">
                            <small class="text-muted">Leave blank to keep current password.</small>
                        </div>
                        <div class="form-group">
                            <label for="course">Course</label>
                            <input type="text" id="course" name="course" class="form-control" value="<?php echo htmlspecialchars($user['course']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="year">Year</label>
                            <input type="text" id="year" name="year" class="form-control" value="<?php echo htmlspecialchars($user['year']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="section">Section</label>
                            <input type="text" id="section" name="section" class="form-control" value="<?php echo htmlspecialchars($user['section']); ?>" required>
                        </div>
                        <input type="submit" value="Update Profile" class="btn btn-primary">
                    </form>
                </div>
            </div>
        </div>
    </div>
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
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>