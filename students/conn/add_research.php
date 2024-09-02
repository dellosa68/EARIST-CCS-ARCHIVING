<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include_once("../../conn/db_conn.php"); // Include database connection file

// Function to handle file upload and return the file content
function uploadFile($fileInputName) {
    if ($_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        // Handle file upload errors here
        return null;
    }
    
    $tmp_name = $_FILES[$fileInputName]['tmp_name'];
    $fp = fopen($tmp_name, 'rb');
    $content = fread($fp, filesize($tmp_name));
    fclose($fp);
    
    return $content;
}

// Get the username and profile picture from the session
$username = $_SESSION["username"];
$profile_pic = isset($_SESSION["profile_pic"]) ? $_SESSION["profile_pic"] : null;

// Convert the profile picture to base64 if it exists
$profile_pic_base64 = $profile_pic ? base64_encode($profile_pic) : null;

if (isset($_POST['logout'])) {
    session_destroy(); // Destroy all sessions
    header("Location: ../index.php");
    exit();
}

// Initialize variables to store IDs and names
$title = $abstract = $authors_display = $panelists_display = $advisers_display = '';
$authors_ids = $panelists_ids = $advisers_ids = [];
$authors_names = $panelists_names = $advisers_names = [];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user inputs for security
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $abstract = mysqli_real_escape_string($conn, $_POST['abstract']);

    // Handle authors
    if (is_array($_POST['authors'])) {
        $authors_ids = $_POST['authors']; // Array of author IDs
        $authors = implode(',', array_map('intval', $authors_ids));

        // Fetch author names from database based on IDs
        $authors_names = [];
        $sql_authors = "SELECT student_id, first_name, last_name FROM student WHERE student_id IN (" . implode(',', $authors_ids) . ")";
        $result_authors = mysqli_query($conn, $sql_authors);
        while ($row = mysqli_fetch_assoc($result_authors)) {
            $authors_names[$row['student_id']] = $row['first_name'] . ' ' . $row['last_name'];
        }
        $authors_display = implode(', ', $authors_names); // Comma-separated list of author names
    } else {
        $authors = intval($_POST['authors']); // Assuming it's a single value
        $authors_display = ''; // Handle case when no authors are selected
    }

    // Handle panelists
    if (is_array($_POST['panelists'])) {
        $panelists_ids = $_POST['panelists']; // Array of panelist IDs
        $panelists = implode(',', array_map('intval', $panelists_ids));

        // Fetch panelist names from database based on IDs
        $panelists_names = [];
        $sql_panelists = "SELECT account_id, fname, lname FROM instructors WHERE account_id IN (" . implode(',', $panelists_ids) . ")";
        $result_panelists = mysqli_query($conn, $sql_panelists);
        while ($row = mysqli_fetch_assoc($result_panelists)) {
            $panelists_names[$row['account_id']] = $row['fname'] . ' ' . $row['lname'];
        }
        $panelists_display = implode(', ', $panelists_names); // Comma-separated list of panelist names
    } else {
        $panelists = intval($_POST['panelists']); // Assuming it's a single value
        $panelists_display = ''; // Handle case when no panelists are selected
    }

    // Handle adviser (assuming it's a single value)
    $adviser = intval($_POST['adviser']);
    $advisers_display = '';

    if ($adviser > 0) {
        // Fetch adviser name from database based on ID
        $sql_adviser = "SELECT account_id, fname, lname FROM instructors WHERE account_id = $adviser";
        $result_adviser = mysqli_query($conn, $sql_adviser);
        $row_adviser = mysqli_fetch_assoc($result_adviser);
        $advisers_display = $row_adviser['fname'] . ' ' . $row_adviser['lname'];
    }

    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $keywords = mysqli_real_escape_string($conn, $_POST['keywords']);


    $document = uploadFile('document');
    $sourceCode = uploadFile('sourcecode');

    // Insert query with BLOB data
    $sql = "INSERT INTO unreleased_research (title, abstract, author_id, author, panel_id, panel, adviser_id, adviser, course, keywords, doc_soft_copy, source_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare statement
    $stmt = mysqli_prepare($conn, $sql);

    // Bind parameters
    mysqli_stmt_bind_param($stmt, "ssssssssssss", $title, $abstract, $authors, $authors_display, $panelists, $panelists_display, $adviser, $advisers_display, $course, $keywords, $document, $sourceCode);

    // Execute statement
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../publish_research.php");
            exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    // Close statement
    mysqli_stmt_close($stmt);

    // Close connection
    mysqli_close($conn);
}
?>
