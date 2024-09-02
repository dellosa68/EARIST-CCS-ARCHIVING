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
        $cover_image = isset($doc['cover']) ? $doc['cover'] : null;
        $approval_sheet = isset($doc['approval_sheet']) ? $doc['approval_sheet'] : null;
        if (isset($doc['panel'])) {
            // Split the panelists and panel_ids strings by comma and space
            $panelists = explode(", ", $doc['panel']);
        }
        if (isset($doc['panel_id'])) {
            $panel_ids = explode(", ", $doc['panel_id']);
        }
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

// Check the latest remarks from the consultation table
$remarks_status = "";
$remarks_sql = "SELECT remarks FROM consultation WHERE research_id = ? ORDER BY date DESC LIMIT 1";
$remarks_stmt = $conn->prepare($remarks_sql);
$remarks_stmt->bind_param("i", $research_id);
$remarks_stmt->execute();
$remarks_stmt->bind_result($remarks_status);
$remarks_stmt->fetch();
$remarks_stmt->close();

// Handle approval button click
if (isset($_POST['approve'])) {
    // Generate a unique 8-digit consultation ID
    $consultation_id = rand(10000000, 99999999);
    $remarks = "Approved";
    $date = date('Y-m-d H:i:s'); // Current date and time

    // Insert the data into the consultations table
    $stmt = $conn->prepare("INSERT INTO consultation (consultation_id, research_id, remarks, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $consultation_id, $research_id, $remarks, $date);

    if ($stmt->execute()) {
        echo "Research approved successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

// Handle resubmit button click
if (isset($_POST['resubmit'])) {
    // Delete the specified certificates
    $stmt_delete = $conn->prepare("UPDATE unreleased_research SET grammarian_cert = NULL, statistician_cert = NULL, plagscan_cert = NULL WHERE id = ?");
    $stmt_delete->bind_param("i", $research_id);

    if ($stmt_delete->execute()) {
        echo "Certificates deleted successfully.";
    } else {
        echo "Error: " . $stmt_delete->error;
    }

    $stmt_delete->close();

    // Generate a unique 8-digit consultation ID
    $consultation_id = rand(10000000, 99999999);
    $remarks = "Resubmit";
    $date = date('Y-m-d H:i:s'); // Current date and time

    // Insert the data into the consultations table
    $stmt_insert = $conn->prepare("INSERT INTO consultation (consultation_id, research_id, remarks, date) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("iiss", $consultation_id, $research_id, $remarks, $date);

    if ($stmt_insert->execute()) {
        echo "Research resubmitted successfully.";
    } else {
        echo "Error: " . $stmt_insert->error;
    }

    $stmt_insert->close();
    $conn->close();
}

// Handle publish button click
if (isset($_POST['publish'])) {
    // Generate a new unique 8-digit research ID
    $new_research_id = rand(10000000, 99999999);
    $current_year = date('Y');
    $current_date = date('Y-m-d');

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Transfer the data to the documents table
        $insert_sql = "INSERT INTO document (research_id, title, abstract, course, system_link, keywords, doc_soft_copy, source_code, author_id, panel, adviser, author, adviser_id, panel_id, cover, approval_sheet, year, date_published, date_issued) 
                       SELECT ?, title, abstract, course, system_link, keywords, doc_soft_copy, source_code, author_id, panel, adviser, author, adviser_id, panel_id, cover, approval_sheet, ?, ?, ? 
                       FROM unreleased_research 
                       WHERE id = ?";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iissi", $new_research_id, $current_year, $current_date, $current_date, $research_id);

        if (!$insert_stmt->execute()) {
            throw new Exception("Error inserting data: " . $insert_stmt->error);
        }

        // Delete the row from unreleased_research table
        $delete_sql = "DELETE FROM unreleased_research WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $research_id);

        if (!$delete_stmt->execute()) {
            throw new Exception("Error deleting data: " . $delete_stmt->error);
        }

        // Commit the transaction
        $conn->commit();
         header("Location: published.php?research_id=$new_research_id");
        echo "Research published successfully.";
    } catch (Exception $e) {
        // Rollback the transaction if any statement fails
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    $insert_stmt->close();
    $delete_stmt->close();
    $conn->close();
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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
     <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
         #main-content {
      transition: margin-left 0.5s;
      margin-left: 50px;
    }

    h1 {
      text-align: center;
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
      font-size: 20px;
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
      /* Set background color to transparent */
    }

    .open-btn:hover {
      background-color: #FF7575;
      /* Add background color on hover */
    }


    .admin-info {
      text-align: center;
      display: none;
    }

    .admin-info img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      margin-bottom: 10px;
    }

    .admin-info h3 {
      color: #fff;
      margin-bottom: 0;
    }

    #sidebar.minimized a .text {
      display: none;
    }
        .container {
            width: 100%;
            max-width: 95%;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
            min-height: 100vh;
        }
         h3 {
            text-align: center;
            color: #333;
        }
        
        .btn-submit {
            display: block;
            margin: 60px auto 0;
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
.recommendation {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.recommendation th,
.recommendation td {
    padding: 10px;
    border: 2px solid #ddd;
    text-align: left;
}

.recommendation th {
    background-color: #f2f2f2;
    font-weight: bold;
    color: #333;
}

.recommendation tr:last-child td {
    border-bottom: none;
}

.recommendation tr:nth-child(even) {
    background-color: #f9f9f9;
}

.recommendation tr:hover {
    background-color: #f2f2f2;
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
        .form-group label {
    font-weight: bold;
}

.form-control-file {
    border: 1px solid #ccc;
    padding: 8px;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0069d9;
    border-color: #0062cc;
}
.pdf-preview {
    margin-top: 10px;
    max-width: 100%;
    height: auto;
    border: 1px solid #ccc;
    padding: 10px;
}
.hide-number .accordion-number {
    display: none;
}
.remarks-section {
    margin-top: 20px;
}

.remarks-card {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-left: 5px solid #007bff; /* Accent color */
}

.remarks-card p {
    margin: 0;
    font-size: 16px;
    color: #333;
}
footer {
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            font-family: 'Times New Roman', serif;
        }
        /* General Button Styles */
.btn-submit, .btn-resubmit {
    display: inline-block;
    padding: 10px 20px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s, box-shadow 0.3s;
}

/* Approve Button */
.btn-submit {
    background-color: #28a745; /* Green */
    color: #fff;
}

.btn-submit:hover {
    background-color: #218838; /* Darker green */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Resubmit Button */
.btn-resubmit {
    background-color: #ffc107; /* Yellow */
    color: #333;
}

.btn-resubmit:hover {
    background-color: #e0a800; /* Darker yellow */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Publish Button */
.btn-publish {
    background-color: #007bff; /* Blue */
    color: #fff;
}

.btn-publish:hover {
    background-color: #0056b3; /* Darker blue */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}


    </style>
</head>
<body>
    <div id="sidebar" class="minimized">
    <button class="open-btn" onclick="toggleNav()"><i class="fas fa-bars"></i></button>
    <div class="admin-info">
        <?php if ($profile_pic_base64): ?>
                <a href="personal_details.php">
                    <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
                </a>
            <?php else: ?>
                <a href="personal_details.php">
                    <img src="../images/default_profile.jpg" alt="Default Profile Picture">
                </a>
            <?php endif; ?>
      <h3 style="color: white;"><?php echo htmlspecialchars($username); ?></h3>ss
    </div>
    <a href="home.php" class="minimized"><i class="fas fa-home"></i><span class="text">   Dashboard</span></a>
    <a href="users.php" class="minimized"><i class="fas fa-user"></i><span class="text">   User</span></a>
    <a href="research.php" class="minimized"><i class="fas fa-file-alt"></i><span class="text">    Research Documents</span></a>
    <a href="research_status.php" class="minimized"><i class="fa fa-dashboard"></i><span class="text">   Consultation</span></a>
    <a href="calendar.php" class="minimized"><i class="fa fa-calendar"></i><span class="text">   Settings</span></a>
    <a href="reports.php" class="minimized"><i class="fas fa-chart-bar"></i><span class="text">   Reports</span></a>
</div>
    <div class="main-content" id="main-content">
        <nav class="navbar" id="navbar">
        <div class="logo">
            <img src="../images/ccs.png" alt="Logo">
            <h2>EARIST - College of Computing Studies Research Archiving System</h2>
        </div>
       
        <div class="profile">
            <?php if ($profile_pic_base64): ?>
    <a href="personal_details.php">
        <img src="data:image/jpeg;base64,<?php echo $profile_pic_base64; ?>" alt="Profile Picture">
    </a>
<?php else: ?>
    <a href="personal_details.php">
        <img src="../images/default_profile.jpg" alt="Default Profile Picture">
    </a>
<?php endif; ?>

            <form method="post" style="margin-left: 10px;">
                <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
            </form>
        </div>
    </nav>   
        <div class="breadcrumb-container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="home.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="publish_research.php">Research List</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $title; ?></li>
                </ol>
            </nav>
        </div>
        <div class="container">
            <div class="accordion active" >
                <div class="accordion-header">
                    <span class="accordion-number">1</span>
                    <h3 class="accordion-title">Upload of Certificates</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-content">
                    <form method="post">
                        <div class="certificate">
                            <?php if (isset($research_id)): ?>
                                <h4>Plagscan Certificate</h4>
                                <iframe src="conn/fetch_pdf.php?research_id=<?php echo $research_id; ?>&cert_type=plagscan_cert" width="100%" height="600px">
                                    This browser does not support PDFs. Please download the PDF to view it: <a href="conn/fetch_pdf.php?research_id=<?php echo $research_id; ?>&cert_type=plagscan_cert">Download Plagscan PDF</a>.
                                </iframe>
                                
                                <!-- Display Grammarian Certificate -->
                                <h4>Grammarian Certificate</h4>
                                <iframe src="conn/fetch_pdf.php?research_id=<?php echo $research_id; ?>&cert_type=grammarian_cert" width="100%" height="600px">
                                    This browser does not support PDFs. Please download the PDF to view it: <a href="conn/fetch_pdf.php?research_id=<?php echo $research_id; ?>&cert_type=grammarian_cert">Download Grammarian PDF</a>.
                                </iframe>
                                
                                <!-- Display Statistician Certificate -->
                                <h4>Statistician Certificate</h4>
                                <iframe src="conn/fetch_pdf.php?research_id=<?php echo $research_id; ?>&cert_type=statistician_cert" width="100%" height="600px">
                                    This browser does not support PDFs. Please download the PDF to view it: <a href="conn/fetch_pdf.php?research_id=<?php echo $research_id; ?>&cert_type=statistician_cert">Download Statistician PDF</a>.
                                </iframe>
                            <?php else: ?>
                                <p>No certificates available for display.</p>
                            <?php endif; ?>
                        </div>
                        <?php if ($remarks_status != "Approved"): ?>
                            <div class="button-container">
                                <button type="submit" name="approve" class="btn-submit">Approve</button>
                                <button type="submit" name="resubmit" class="btn-resubmit">Resubmit</button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div> 
            <div class="accordion" <?php echo $remarks_status == "Approved" ? 'active' : ''; ?>>
                <div class="accordion-header">
                    <span class="accordion-number">2</span>
                    <h3 class="accordion-title">Proof of Hardbound</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-content">
                    <?php if ($cover_image): ?>
                        <h4>Cover Image</h4>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($cover_image); ?>" alt="Cover Image" style="max-width: 100%; height: auto;">
                    <?php else: ?>
                        <p>No cover image available.</p>
                    <?php endif; ?>
                    
                    <?php if ($approval_sheet): ?>
                        <h4>Approval Sheet</h4>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($approval_sheet); ?>"alt="Approval Sheet" style="max-width: 100%; height: auto;">
                            
                        </img>
                    <?php else: ?>
                        <p>No approval sheet available.</p>
                    <?php endif; ?>
                    <form method="POST">
                    <button type="submit" name="repeat" class="btn btn-warning">Resubmit</button>
                    <button type="submit" name="publish" class="btn-publish">Publish</button>
                </form>
                </div>
            </div>
        </div>
<footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>

        $(document).ready(function(){
            $('.accordion-header').click(function(){
                $(this).parent().toggleClass('active');
            });
        });
        function toggleNav() {
    var sidebar = document.getElementById("sidebar");
    if (sidebar.style.width === '50px') {
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

    </script>
</body>
</html>
