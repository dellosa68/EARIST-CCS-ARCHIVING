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
    header("Location: ../admin.php");
    exit();
}

$pdo = new PDO('mysql:host=localhost;dbname=research', 'root', '');

// Fetch the account ID of the logged-in user
$account_id = $_SESSION['account_id'];

// Query to fetch the student ID associated with the account ID
$stmt = $pdo->prepare('SELECT student_id FROM student WHERE account_id = ?');
$stmt->execute([$account_id]);
$student_id = $stmt->fetchColumn();

// Query to fetch panelists and advisers from database
$stmt = $pdo->query('SELECT account_id, fname, lname, pic FROM instructors');
$panelists = $stmt->fetchAll(PDO::FETCH_ASSOC);
$advisers = $panelists; // Assuming advisers are in the same table for this example

// Query to fetch authors from student table
$stmt = $pdo->query('SELECT student_id, first_name, last_name, profile_pic FROM student');
$authors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to convert BLOB to base64
function base64EncodeImage($imageBlob) {
    return 'data:image/jpeg;base64,' . base64_encode($imageBlob);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Research</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" rel="stylesheet">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.6.0/jszip.min.js"></script>

  <style>
    body {
      font-family: 'Roboto', Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    #main-content {
      transition: margin-left 0.5s;
      margin-left: 50px;
    }

    h1 {
        padding: 20px;
        font-weight: bold;
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

    .header {
      height: 130px;
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

    .main-content {
      margin-left: 250px;
      transition: margin-left 0.5s;
    }

    .bootstrap-tagsinput {
      width: 100%;
      padding: 8px 12px;
      font-size: 14px;
      line-height: 22px;
      border: 1px solid #ced4da;
      border-radius: 4px;
      background-color: #fff;
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .bootstrap-tagsinput .tag {
      margin-right: 2px;
      color: white;
      background-color: #007bff;
      

            border-radius: 3px;
            padding: 0.2em 0.6em;
            font-size: 14px;
            font-weight: 500;
        }

        .bootstrap-tagsinput input {
            width: auto !important;
            max-width: inherit;
        }

        .keyword-btn {
            margin-bottom: 5px;
        }

        .form-group label {
            font-weight: 600;
        }

        .step-section {
            display: none;
        }

        .step-section.active {
            display: block;
        }

        .form-container {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            margin-bottom: 30px;
        }

        .btn-primary,
        .btn-secondary {
            margin-right: 10px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .select2-container .select2-selection--single {
            height: 48px;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .select2-container .select2-selection--multiple {
            min-height: 38px;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .selected-ids {
            margin-top: 10px;
            display: none;
        }
         .select2-results__option img {
        border-radius: 50%;
        margin-right: 10px; 
    }

        .selected-ids label {
            font-weight: 600;
        }

        .selected-ids p {
            margin-bottom: 0;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            position: relative;
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
        /* Custom CSS for enhanced form inputs */

.form-group label {
    font-weight: 600;
    color: #333; /* Adjust color as needed */
}

.form-group input[type=file] {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #f9f9f9;
}

.form-group input[type=url] {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 100%;
    max-width: 100%;
}

/* Optional: Adjust margins and padding for better spacing */
.form-container .form-group {
    margin-bottom: 20px;
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
    
    <div id="main-content">
        <nav class="navbar" id="navbar">
        <div class="logo">
            <img src="../images/ccs.png" alt="Logo">
            <h2>EARIST - College of Computing Studies Research Archiving System</h2>
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
        <div class="container">
            <h1>Add Old Research</h1>
            <form method="post" action="conn/add_old_research.php" enctype="multipart/form-data">
                <div class="form-container">
                    <div class="form-title">
                        <h4>Basic Information</h4>
                    </div>
                    <div class="form-group">
                        <label for="title">Research Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Research Abstract</label>
                        <textarea class="form-control" id="abstract" name="abstract" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="year">Year Published</label>
                        <input type="text" class="form-control" id="year" name="year" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Course</label>
                        <select class="form-control" id="course" name="course" required>
                            <option value="BSCS">BS in Computer Science</option>
                            <option value="BSIT">BS in Information Technology</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="keywords">Keywords</label>
                        <input type="text" class="form-control" id="keywords" name="keywords" data-role="tagsinput" placeholder="Add keywords">
                    </div>
                </div>
                <!-- Research Team -->
                <div class="form-container mt-4">
                    <div class="form-title">
                        <h4>Research Team</h4>
                    </div>
                    <div class="form-group">
                        <label for="authors">Authors:</label>
                         <input type="text" class="form-control" id="authors" name="authors" placeholder="Add Authors separate by comma">
                        <small>Add Authors names and separate by comma(,)</small>
                    </div>
                    <div class="form-group">
                        <label for="panelists">Choose Panelists:</label>
                        <select id="panelists" name="panelists[]" class="form-control select2" multiple="multiple" required>
                            <?php foreach ($panelists as $panelist) : ?>
                                <?php if (!empty($panelist['fname']) && !empty($panelist['lname'])) : ?>
                                    <option value="<?php echo $panelist['account_id']; ?>"
                                            data-pic="<?php echo base64EncodeImage($panelist['pic']); ?>">
                                        <?php echo htmlspecialchars($panelist['fname'] . ' ' . $panelist['lname']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="advisers">Choose Advisers:</label>
                        <select id="advisers" name="adviser" class="form-control select2" required>
                            <?php foreach ($advisers as $adviser) : ?>
                                <?php if (!empty($adviser['fname']) && !empty($adviser['lname'])) : ?>
                                    <option value="<?php echo $adviser['account_id']; ?>"
                                            data-pic="<?php echo base64EncodeImage($adviser['pic']); ?>">
                                        <?php echo htmlspecialchars($adviser['fname'] . ' ' . $adviser['lname']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Selected IDs Display -->
                <div class="form-container mt-4 selected-ids">
                    <div class="form-title">
                        <h4>Selected IDs</h4>
                    </div>
                    <div class="form-group">
                        <label for="selected-panelist-ids">Selected Panelist IDs</label>
                        <input type="text" class="form-control" id="selected-panelist-ids" name="selected-panelist-ids" readonly>
                    </div>
                    <div class="form-group">
                        <label for="selected-adviser-id">Selected Adviser ID</label>
                        <input type="text" class="form-control" id="selected-adviser-id" name="selected-adviser-id" readonly>
                    </div>
                </div>
                <!-- Updated HTML with enhanced styling -->
<div class="form-container mt-4">
    <div class="form-title">
        <h4>Document</h4>
    </div>
    <div class="form-group">
        <label for="cover_image">Cover Image (jpg, png, jpeg only)</label>
        
            <input type="file" class="form-control-file"  id="cover_image" name="cover_image" accept=".jpg,.jpeg,.png" required>
      
        <img id="cover_image_preview" src="#" alt="Cover Image Preview" style="display: none; max-width: 20%; margin-top: 10px; border-radius: 5px;">
    </div>
    <div class="form-group">
        <label for="approval_sheet">Approval Sheet (jpg, png, jpeg only)</label>
            <input type="file" class="form-control-file" id="approval_sheet" name="approval_sheet" accept=".jpg,.jpeg,.png" required>
        <img id="approval_sheet_preview" src="#" alt="Approval Sheet Preview" style="display: none; max-width: 20%; margin-top: 10px; border-radius: 5px;">
    </div>
 


                <!-- Submission Button -->
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-secondary" onclick="history.back();">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.select2').select2({
            templateResult: formatState,
            templateSelection: formatState
        });

        // Function to format the display of the select2 options with images
        function formatState(opt) {
            if (!opt.id) {
                return opt.text;
            }
            var optimage = $(opt.element).attr('data-pic');
            if (!optimage) {
                return opt.text;
            } else {
                var $opt = $('<span><img src="' + optimage + '" width="30" height="30" /> ' + opt.text + '</span>');
                return $opt;
            }
        }

        // Function to update selected IDs in hidden inputs
        function updateSelectedIds() {
            var authorIds = $('#authors').val();
            var panelistIds = $('#panelists').val();
            var adviserId = $('#advisers').val();

            $('#selected-author-id').val(authorIds ? authorIds.join(', ') : '');
            $('#selected-panelist-ids').val(panelistIds ? panelistIds.join(', ') : '');
            $('#selected-adviser-id').val(adviserId);
        }

        // Update selected IDs on change
        $('#authors, #panelists, #advisers').on('change', function() {
            updateSelectedIds();
        });

        // Initial update when page loads
        updateSelectedIds();
    });

    $(document).ready(function() {
    // Function to preview image when selected
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                $(previewId).attr('src', e.target.result);
                $(previewId).css('display', 'block'); // Show the image preview
            }

            reader.readAsDataURL(input.files[0]); // Read the file as data URL
        }
    }

    // Trigger image preview on file input change
    $('#cover_image').change(function() {
        previewImage(this, '#cover_image_preview');
    });

    $('#approval_sheet').change(function() {
        previewImage(this, '#approval_sheet_preview');
    });
});
    $(document).ready(function() {
    // Function to preview PDF when selected
    function previewPDF(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function(e) {
                $('#pdf_preview').attr('src', e.target.result);
                $('#pdf_preview').css('display', 'block'); // Show the PDF preview
            }

            reader.readAsDataURL(input.files[0]); // Read the file as data URL
        }
    }

    // Trigger PDF preview on file input change
    $('#document').change(function() {
        previewPDF(this);
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
