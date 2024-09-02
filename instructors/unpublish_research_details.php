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
$account_id = $_SESSION['account_id'];

// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

if (isset($_POST['logout'])) {
    session_destroy(); // Destroy all sessions
    header("Location: ../index.php");
    exit();
}
include '../conn/db_conn.php';

// Retrieve the research_id from the URL query parameters
if(isset($_GET['research_id'])) {
    $research_id = $_GET['research_id'];
    
    // Fetch details of the specific research document from the database using $research_id
    $sql = "SELECT * FROM unreleased_research WHERE id = $research_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $doc = $result->fetch_assoc();
        // Display the title, author, author_id, cover, and abstract of the research document
        $title = htmlspecialchars($doc['title']);
        $author = htmlspecialchars($doc['author']);
        $keyword = htmlspecialchars($doc['keywords']);
        $abstract = htmlspecialchars($doc['abstract']);
        $adviser = htmlspecialchars($doc['adviser']);
        $system = htmlspecialchars($doc['system_link']);
    } else {
        $title = "Document not found";
        $author = "";
        $abstract = "";
    }
} else {
    $title = "Invalid research ID";
    $author = "";
    $abstract = "";
}

// Fetch comments and their status from the database based on account_id
$sql_comments = "SELECT comment_text, status FROM comments WHERE account_id = $account_id";
$result_comments = $conn->query($sql_comments);

$comments = [];
if ($result_comments->num_rows > 0) {
    while ($row = $result_comments->fetch_assoc()) {
        $comments[] = [
            'comment' => htmlspecialchars($row['comment_text']),
            'status' => $row['status'] === 'YES' ? true : false // Convert status to boolean
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Status</title>
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
            font-size: 15px;
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
        .admin-info p {
            color: #fff;
            margin-bottom: 0;
        }
        #sidebar.minimized a .text {
            display: none;
        }
        .main-content {
            transition: margin-left 0.5s;
            margin-left: 50px;
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
            max-width: 95%;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
        }
        h3 {
            text-align: center;
            color: #333;
        }
        .btn-submit {
            display: block;
            margin-bottom: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
        .icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 10px; /* Adjusted right position */
            color: #888;
        }
        .icon i {
            font-size: 10px;
        }
        footer {
            margin-top: 50px;
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
        .header, .sidebar {
            font-family: 'Roboto', sans-serif;
        }
        .header h3, .sidebar a {
            font-weight: 700; /* Bold font weight */
        }
        /* Container and Content */
        .container, .content {
            font-family: 'Roboto', Arial, sans-serif;
        }
        h1, h3, .title, .status, .btn-submit {
            font-weight: 700; /* Bold font weight */
        }
        .accordion {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .accordion-header {
            background-color: #f9f9f9;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .accordion-number {
            background-color: #007bff;
            color: #fff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 10px;
        }
        .accordion-title {
            flex: 1;
            margin: 0;
            text-align: left;
        }
        .accordion-content {
            display: none;
            padding: 10px;
        }
        .accordion.active .accordion-content {
            display: block;
        }
        .research-details {
            font-size: 16px;
            color: #333;
        }
        .research-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .research-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .research-table td:first-child {
            width: 200px; /* Set a fixed width for the first column */
            font-weight: bold;
            color: #555;
        }
        .research-table tr:last-child td {
            border-bottom: none; /* Remove bottom border from the last row */
        }
        .research-table tr:nth-child(even) {
            background-color: #f9f9f9; /* Alternate row background color */
        }
        .research-table tr:hover {
            background-color: #f2f2f2; /* Hover background color */
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f9f9f9;
            transition: background 0.3s;
        }
        .checkbox-item:hover {
            background: #e9e9e9;
        }
        .delete-button {
            background: #dc3545;
            border: none;
            color: #fff;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .delete-button:hover {
            background: #c82333;
        }
        .checkbox-item input[type="checkbox"] {
            margin-right: 10px;
        }
        input[type="text"] {
            padding: 10px;
            width: calc(100% - 22px);
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            padding: 10px 15px;
            background-color: #007bff;
            border: none;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        ul {
            list-style-type: none; /* Remove bullets */
            padding: 0; /* Remove default padding */
        }
        .existing-comments {
    margin-top: 30px;
}

.comment-list {
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #fff;
    padding: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.comment-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
    transition: background-color 0.3s;
}

.comment-item:last-child {
    border-bottom: none; /* Remove border from the last item */
}

.comment-item:hover {
    background-color: #f2f2f2; /* Highlight on hover */
}

.comment-item input[type="checkbox"] {
    margin-right: 10px;
    transform: scale(1.2); /* Make checkbox larger */
}

.comment-item label {
    flex: 1;
    font-size: 16px;
    color: #333;
}

.comment-item input[type="checkbox"]:checked + label {
    text-decoration: line-through; /* Strike-through for checked items */
    color: #aaa; /* Dimmed color for checked items */
}
.new-section {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.section-left, .section-right {
    width: 50%; /* Adjust the width as needed */
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
}

.section-left {
    background-color: #f9f9f9; /* Optional: Different background color for differentiation */
}

.section-right {
    background-color: #e9e9e9; /* Optional: Different background color for differentiation */
}
.navbar {
        display: flex; /* Changed to flex for better alignment */
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

    .navbar .profile {
        display: flex;
        align-items: center;
        margin-left: auto;
        margin-right: 40px; /* Pushes the profile to the right */
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

    .navbar .profile form {
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
                    <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
                <?php else: ?>
                    <img src="../images/default_profile.png" alt="Default Profile Picture">
                <?php endif; ?>
                <form method="post" style="margin-left: 10px;">
                    <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
                </form>
            </div>
        </nav>
        <div class="container">
            <div class="accordion active">
                <div class="accordion-header">
                    <span class="accordion-number">1</span>
                    <h3 class="accordion-title">Consultation</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-content">
                    <div class="research-details">
                        <table class="research-table">
                            <tr>
                                <td><strong>Title:</strong></td>
                                <td><?php echo $title; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Author:</strong></td>
                                <td><?php echo $author; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Keywords:</strong></td>
                                <td><?php echo $keyword; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Thesis Adviser:</strong></td>
                                <td><?php echo $adviser; ?></td>
                            </tr>
                            <tr>
                                <td><strong>System Link/Preview:</strong></td>
                                <td><?php echo $system; ?></td>
                            </tr>
                        </table>
                    </div>
                     <div class="pdf-viewer">
                        <iframe src="conn/fetch_pdf.php?research_id=<?php echo $research_id; ?>" width="100%" height="600px"></iframe>
                    </div>
                    <div class="new-section">
                        <div class="section-left">
                            <form action="conn/upload_comments.php" method="post">
                                <h4>Add Comments/Recommendations</h4>
                                <input type="text" id="itemInput" placeholder="Enter Comment">
                                <button type="button" onclick="addCheckbox()">Add Comment</button>
                                <br><br>
                                <div id="checkboxList">
                                    
                                </div>
                                <input type="submit" class="btn-submit" value="Submit">
                                <input type="hidden" id="itemList" name="item_list">
                                <input type="hidden" name="account_id" value="<?php echo $account_id; ?>">
                                <input type="hidden" name="research_id" value="<?php echo $research_id; ?>">
                            </form>
                        </div>
                        <div class="section-right">
                            <div class="existing-comments">
                                <h4>Existing Comments</h4>
                                <div class="comment-list">
                                    <?php if (!empty($comments)): ?>
                                        <?php foreach ($comments as $item): ?>
                                            <div class="comment-item">
                                                <input type="checkbox" name="selected_items[]" value="<?php echo $item['comment']; ?>" <?php echo $item['status'] ? 'checked' : ''; ?> disabled>
                                                <label><?php echo $item['comment']; ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>No comments available.</p>
                                    <?php endif; ?>
                                </div>
                                <button class="btn-submit" id="edit-comments-btn" onclick="toggleCheckboxes()">Edit Comments</button>
            <button class="btn-submit" id="save-comments-btn" onclick="saveComments()" disabled>Save Comments</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function toggleNav() {
        var sidebarWidth = document.getElementById("sidebar").style.width;
        var sidebar = document.getElementById("sidebar");
        if (sidebarWidth === '50px') {
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

    $(document).ready(function(){
        $('.accordion-header').click(function(){
            $(this).parent().toggleClass('active');
        });
    });
     function addCheckbox() {
    var input = document.getElementById("itemInput");
    var comment = input.value.trim();

    if (comment !== "") {
        // Create a new div for the checkbox item
        var checkboxItem = document.createElement("div");
        checkboxItem.className = "checkbox-item";

        // Create a new checkbox input
        var checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.name = "comments[]";
        checkbox.value = comment;
        checkbox.checked = false;

        // Create a new label for the checkbox
        var label = document.createElement("label");
        label.innerHTML = comment;

        // Create a delete button
        var deleteButton = document.createElement("button");
        deleteButton.className = "delete-button";
        deleteButton.innerHTML = "Delete";
        deleteButton.onclick = function() {
            checkboxItem.remove(); // Remove the checkbox item when delete button is clicked
            updateItemList(); // Update the hidden input field
        };

        // Append the checkbox, label, and delete button to the div
        checkboxItem.appendChild(checkbox);
        checkboxItem.appendChild(label);
        checkboxItem.appendChild(deleteButton);

        // Append the new checkbox item to the checkbox list
        document.getElementById("checkboxList").appendChild(checkboxItem);

        // Clear the input field
        input.value = "";

        // Update the hidden input field to store the list of comments
        updateItemList();
    }
}

    function updateItemList() {
        var checkboxes = document.querySelectorAll("#checkboxList input[type='checkbox']");
        var items = [];
        checkboxes.forEach(function(checkbox) {
            items.push(checkbox.value);
        });

        document.getElementById("itemList").value = items.join(";");
    }

    function toggleCheckboxes() {
        var checkboxes = document.querySelectorAll(".comment-item input[type='checkbox']");
        checkboxes.forEach(function(checkbox) {
            checkbox.disabled = !checkbox.disabled;
        });
        document.getElementById("save-comments-btn").disabled = !document.getElementById("save-comments-btn").disabled;
    }

    function saveComments() {
    var checkedComments = [];
    var checkboxes = document.querySelectorAll(".comment-item input[type='checkbox']:checked");

    checkboxes.forEach(function(checkbox) {
        checkedComments.push(checkbox.value);
    });

    if (checkedComments.length > 0) {
        $.ajax({
            type: "POST",
            url: "conn/update_comment.php",
            data: { checked_comments: checkedComments },
            success: function(response) {
                alert(response); // Show success message
                // Optionally, you can reload the page or update the comments section
                location.reload();
            },
            error: function() {
                alert('An error occurred while saving comments.');
            }
        });
    } else {
        alert('No comments selected to save.');
    }
}

</script>

</body>
</html>
