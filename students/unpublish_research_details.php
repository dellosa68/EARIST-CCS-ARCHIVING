<?php
session_start(); 
if (!isset($_SESSION["username"])) {
    header("Location: ../login.php");
    exit();
}
$username = isset($_SESSION["username"]) ? $_SESSION["username"] : null;
$profile_pic = isset($_SESSION["profile_pic"]) ? $_SESSION["profile_pic"] : null;
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

if (isset($_POST['logout'])) {
    session_destroy(); 
    header("Location: ../index.php");
    exit();
}
include '../conn/db_conn.php';
if (isset($_GET['research_id'])) {
    $research_id = $conn->real_escape_string($_GET['research_id']);

    // Retrieve the most recent entry based on the date
    $status_query = "SELECT remarks FROM consultation WHERE research_id = '$research_id' ORDER BY date DESC LIMIT 1";
    $status_result = $conn->query($status_query);

    if ($status_result && $status_result->num_rows > 0) {
        $status_row = $status_result->fetch_assoc();
        $certificate_status = $status_row['remarks']; 
        if ($certificate_status === "Approved") {
            $last_accordion_style = ''; // Show accordion
        } elseif ($certificate_status === "Resubmit") {
            $last_accordion_style = 'style="display: none;"'; // Hide accordion
        } else {
            $last_accordion_style = 'style="display: none;"'; // Default to hidden if no match
        }
    } else {
        $last_accordion_style = 'style="display: none;"'; 
    }

    // Retrieve certificates data
    $certificates_query = "SELECT statistician_cert, plagscan_cert, grammarian_cert FROM unreleased_research WHERE id = '$research_id'";
    $certificates_result = $conn->query($certificates_query);
    
    if ($certificates_result) {
        $existing_certificates = $certificates_result->fetch_assoc();
        $statistician_exists = !empty($existing_certificates['statistician_cert']);
        $plagscan_exists = !empty($existing_certificates['plagscan_cert']);
        $grammarian_exists = !empty($existing_certificates['grammarian_cert']);

        $all_certificates_exist = $statistician_exists && $plagscan_exists && $grammarian_exists;
    }
    $sql = "SELECT * FROM unreleased_research WHERE id = '$research_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $doc = $result->fetch_assoc();
        $title = htmlspecialchars($doc['title']);
        $author = htmlspecialchars($doc['author']);
        $keyword = htmlspecialchars($doc['keywords']);
        $abstract = htmlspecialchars($doc['abstract']);
        $adviser = htmlspecialchars($doc['adviser']);
        $system = htmlspecialchars($doc['system_link']);
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
$remark_message = '';
if (isset($certificate_status)) {
    if ($certificate_status === "Approved") {
        $remark_message = "Your uploaded certificates have been approved. You can now proceed to hardbound.";
    } else if ($certificate_status === "Resubmit") {
        $remark_message = "Certificates need to be resubmitted.";
    } else {
        $remark_message = "Pending review.";
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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
     <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .no-print { display: none; }
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
        }
        .header {
            display: block;
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
            min-height: 100vh;
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
    margin-bottom: 15px;
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


    </style>
</head>
<body>
       
    <div class="main-content" id="main-content">
        <div class="header">
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
                                <td>Title:</td>
                                <td><?php echo $title; ?></td>
                            </tr>
                            <tr>
                                <td>Author:</td>
                                <td><?php echo $author; ?></td>
                            </tr>
                            <tr>
                                <td>Keywords:</td>
                                <td><?php echo $keyword; ?></td>
                            </tr>
                            <tr>
                                <td>Adviser:</td>
                                <td><?php echo $adviser; ?></td>
                            </tr>
                            <tr>
                                <td>System Link:</td>
                                <td><?php echo $system; ?></td>
                            </tr>
                            <tr>
                                <td>Abstract:</td>
                                <td><?php echo $abstract; ?></td>
                            </tr>
                            <table class="recommendation" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="width: 30%; text-align: left;">Panelist</th>
                                        <th style="width: 50%; text-align: left;">Recommendation</th>
                                        <th style="width: 20%; text-align: left;">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $total_rows = 0;
                                $complete_count = 0;
                                $has_revision_remarks = false;

                                // Split the panelists and panel IDs by commas
                                $panelists = explode(", ", $doc['panel']);
                                $panel_ids = explode(",", $doc['panel_id']);

                                $all_complete = true; 
                                foreach ($panelists as $index => $panelist) {
                                    $panel_id = isset($panel_ids[$index]) ? htmlspecialchars(trim($panel_ids[$index])) : 'N/A';
                                    
                                    // Fetch comments and statuses from the database for the current panel_id
                                    $comment_sql = "SELECT comment_text, status FROM comments WHERE account_id = '$panel_id' AND research_id = '$research_id'";
                                    $comment_result = $conn->query($comment_sql);
                                    
                                    $comment_texts = [];
                                    $yes_count = 0;

                                    if ($comment_result->num_rows > 0) {
                                        while ($row = $comment_result->fetch_assoc()) {
                                            $comment_text = htmlspecialchars($row['comment_text']);
                                            if ($row['status'] === 'YES') {
                                                $comment_text = "<span style='text-decoration: line-through;'>$comment_text</span>"; 
                                                $yes_count++;
                                            }
                                            $comment_texts[] = $comment_text;
                                            if ($row['status'] === 'Complete') {
                                                $complete_count++;
                                            }
                                        }
                                    }
                                    
                                    // Join all comments into a single string for display
                                    $comments_display = !empty($comment_texts) ? '<ul><li>' . implode('</li><li>', $comment_texts) . '</li></ul>' : 'No comments';
                                    $total_comments = count($comment_texts);
                                    if ($total_comments > 0) {
                                        if ($yes_count < $total_comments) {
                                            $remarks = "For Revision";
                                            $has_revision_remarks = true;
                                            $all_complete = false; 
                                        } else {
                                            $remarks = "Complete";
                                        }
                                    } else {
                                        $remarks = "No comments";
                                        $all_complete = false; 
                                    }
                                    
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars(trim($panelist)) . "</td>"; 
                                    echo "<td>$comments_display</td>"; 
                                    echo "<td>$remarks</td>";
                                    echo "</tr>";
                                }

                                $second_accordion_style = $all_complete ? '' : 'style="display: none;"'; 
                                ?>
                                </tbody>
                            </table>
                        </table>
                    </div>
                    <a href="conn/generate_pdf.php?research_id=<?php echo $research_id; ?>" class="btn btn-primary">Download PDF</a>
                </div>
            </div>
            <div class="accordion active" <?php echo $second_accordion_style; ?>>
                <div class="accordion-header">
                    <span class="accordion-number">2</span>
                    <h3 class="accordion-title">Upload of Certificates</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-content">
                    <div class="certificate">
                        <?php if ($statistician_exists || $plagscan_exists || $grammarian_exists): ?>
                            <div class="remarks-section">
                                <h4>Remarks</h4>
                                <div class="remarks-card">
                                    <p><?php echo htmlspecialchars($remark_message); ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <form action="conn/upload_cert.php" method="post" enctype="multipart/form-data" class="mt-3">
                                <input type="hidden" name="research_id" value="<?php echo isset($research_id) ? htmlspecialchars($research_id) : ''; ?>">
                                
                                <div class="form-group">
                                    <label for="statistician_cert">Upload Statistician Certificate (PDF/Picture):</label>
                                    <input type="file" class="form-control-file" id="statistician_cert" name="statistician_cert" accept=".pdf" required>
                                    <div id="statistician_preview" class="pdf-preview"></div>
                                </div>

                                <div class="form-group">
                                    <label for="plagscan_cert">Upload Plagscan Certificate (PDF/Picture):</label>
                                    <input type="file" class="form-control-file" id="plagscan_cert" name="plagscan_cert" accept=".pdf" required>
                                    <div id="plagscan_preview" class="pdf-preview"></div>
                                </div>

                                <div class="form-group">
                                    <label for="grammarian_cert">Upload Grammarian Certificate (PDF/Picture):</label>
                                    <input type="file" class="form-control-file" id="grammarian_cert" name="grammarian_cert" accept=".pdf" required>
                                    <div id="grammarian_preview" class="pdf-preview"></div>
                                </div>

                                <button type="submit" class="btn btn-primary">Upload Certificates</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="accordion" <?php echo $last_accordion_style; ?>>
                <div class="accordion-header">
                    <span class="accordion-number">3</span>
                    <h3 class="accordion-title">Proof of Hardbound</h3>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="accordion-content">
                    <div>
                        <form action="conn/upload.php" method="post" enctype="multipart/form-data" class="mt-3">
                             <input type="hidden" name="research_id" value="<?php echo htmlspecialchars($research_id); ?>">
                            <div class="form-group">
                                <label for="cover_image">Harbound Image (PDF/Picture):</label>
                                <input type="file" class="form-control-file" id="cover_image" name="cover_image" accept=".jpg,.jpeg,.png" required>
                                <img id="cover_image_preview" src="#" alt="Cover Image Preview" style="display: none; max-width: 50%; margin-top: 10px; border-radius: 5px;">
                            </div>
                            
                            <div class="form-group">
                                <label for="approval_sheet">Approval Sheet</label>
                                <input type="file" class="form-control-file" id="approval_sheet" name="approval_sheet" accept=".jpg,.jpeg,.png" required>
                                <img id="approval_sheet_preview" src="#" alt="Approval Sheet Preview" style="display: none; max-width: 50%; margin-top: 10px; border-radius: 5px;">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            function readLargeFile(input, preview) {
                const file = input.files[0];
                const chunkSize = 100 * 1024 * 1024; 
                let offset = 0;

                const reader = new FileReader();
                reader.onload = function() {
                    const blob = new Blob([reader.result], { type: 'application/pdf' });
                    const url = URL.createObjectURL(blob);
                    $(preview).html(`<embed src="${url}" type="application/pdf" width="100%" height="600px" />`);
                };

                function readNextChunk() {
                    const slice = file.slice(offset, offset + chunkSize);
                    reader.readAsArrayBuffer(slice);
                    offset += chunkSize;
                }

                readNextChunk();
            }

            $("#statistician_cert").change(function() {
                readLargeFile(this, "#statistician_preview");
            });

            $("#plagscan_cert").change(function() {
                readLargeFile(this, "#plagscan_preview");
            });

            $("#grammarian_cert").change(function() {
                readLargeFile(this, "#grammarian_preview");
            });
        });

        $(document).ready(function(){
            $('.accordion-header').click(function(){
                $(this).parent().toggleClass('active');
            });
        });
        $(document).ready(function() {
            // Function to preview image when selected
            function previewImage(input, previewId) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $(previewId).attr('src', e.target.result);
                        $(previewId).css('display', 'block'); 
                    }
                }
            }
            $('#cover_image').change(function() {
                previewImage(this, '#cover_image_preview');
            });

            $('#approval_sheet').change(function() {
                previewImage(this, '#approval_sheet_preview');
            });
        });
    </script>
</body>
</html>
