<?php
session_start(); // Start the session
include "../conn/db_conn.php";

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
$account_id = $_SESSION["account_id"];
// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

// Query to fetch published documents authored by the logged-in user
$query_published = "SELECT title, author, year, research_id FROM document WHERE author_id = ?";
$stmt_published = $conn->prepare($query_published);
$stmt_published->bind_param("i", $student_id);
$stmt_published->execute();
$result = $stmt_published->get_result();

$query_unreleased = "SELECT * FROM unreleased_research WHERE plagscan_cert IS NOT NULL AND grammarian_cert IS NOT NULL AND statistician_cert IS NOT NULL";
$result_unreleased = $conn->query($query_unreleased);

// Close the statements after fetching results
$stmt_published->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 150px;
            margin-bottom: 20px;
        }
        .table-responsive {
            margin-top: 20px;
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

    #main-content {
      transition: margin-left 0.5s;
      margin-left: 50px;
    }

    .open-btn {
      border: none;
      color: white;
      padding: 10px 20px;
      font-size: 16px;
      background-color: transparent; /* Set background color to transparent */
    }

    .open-btn:hover {
        background-color: #FF7575; /* Add background color on hover */
    }
    #sidebar.minimized a .text {
        display: none;
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
    .navbar {
        display: flex; /* Changed to flex for better alignment */
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
        position: fixed;
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
        .table-striped tbody tr:nth-of-type(odd) {
    background-color: #f2f2f2;
}

.table-striped tbody tr:hover {
    background-color: #e9e9e9;
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
            <img src="../images/default_profile.jpg" alt="Default Profile Picture">
          <?php endif; ?>
          <form method="post" action="conn/logout.php">
            <button type="submit" name="logout" class="open-btn"><i class="fas fa-sign-out-alt"></i></button>
          </form>
        </div>
    </nav>
        <div class="container">
            <h1 class="mt-5">Reports</h1>
            <div class="table-responsive">
                <table id="reportsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Keyword</th>
                            <th>Year</th>
                            <th>Adviser</th>
                            <th>Panel</th>
                            <th>Course</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Database connection
                        $host = 'localhost';
                        $db = 'research';
                        $user = 'root';
                        $pass = '';

                        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
                        $options = [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES   => false,
                        ];

                        try {
                            $pdo = new PDO($dsn, $user, $pass, $options);
                            $stmt = $pdo->query('SELECT title, author, keywords, year, adviser, panel, course FROM document');
                            while ($row = $stmt->fetch()) {
                                echo "<tr>
                                        <td>{$row['title']}</td>
                                        <td>{$row['author']}</td>
                                        <td>{$row['keywords']}</td>
                                        <td>{$row['year']}</td>
                                        <td>{$row['adviser']}</td>
                                        <td>{$row['panel']}</td>
                                        <td>{$row['course']}</td>
                                      </tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='7'>Error: " . $e->getMessage() . "</td></tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Keyword</th>
                            <th>Year</th>
                            <th>Adviser</th>
                            <th>Panel</th>
                            <th>Course</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#reportsTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'excelHtml5'
                ],
                initComplete: function () {
                    this.api().columns([3, 4, 6]).every(function () {
                        var column = this;
                        var select = $('<select class="form-control"><option value=""></option></select>')
                            .appendTo($(column.footer()).empty())
                            .on('change', function () {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column
                                    .search(val ? '^' + val + '$' : '', true, false)
                                    .draw();
                            });
                        column.data().unique().sort().each(function (d, j) {
                            select.append('<option value="' + d + '">' + d + '</option>')
                        });
                    });
                }
            });
        });
        function toggleNav() {
            var sidebarWidth = document.getElementById("sidebar").style.width;
            var sidebar = document.getElementById("sidebar");
            if (sidebarWidth === '50px') {
              // If the side panel is open, close it
              sidebar.style.width = "250px";
              document.getElementById("main-content").style.marginLeft = "250px";
              document.querySelector(".admin-info").style.display = "block";
              sidebar.classList.remove('minimized');
            } else {
              // If the side panel is closed, open it
              sidebar.style.width = "50px";
              document.getElementById("main-content").style.marginLeft = "50px";
              document.querySelector(".admin-info").style.display = "none"; 
              sidebar.classList.add('minimized');
            }
          }
    </script>
</body>
</html>
