<?php
session_start(); // Start the session

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

// Function to generate a unique 8-digit research_id
function generateUniqueResearchId($conn) {
    do {
        $research_id = mt_rand(10000000, 99999999); // Generate 8-digit number
        $sql = "SELECT research_id FROM document WHERE research_id = '$research_id'";
        $result = mysqli_query($conn, $sql);
    } while (mysqli_num_rows($result) > 0); // Ensure the ID is unique
    return $research_id;
}

// Generate unique research_id
$research_id = generateUniqueResearchId($conn);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user inputs for security
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $abstract = mysqli_real_escape_string($conn, $_POST['abstract']);
    $authors = mysqli_real_escape_string($conn, $_POST['authors']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);


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
    if ($adviser > 0) {
        // Fetch adviser name from database based on ID
        $sql_adviser = "SELECT account_id, fname, lname FROM instructors WHERE account_id = $adviser";
        $result_adviser = mysqli_query($conn, $sql_adviser);
        $row_adviser = mysqli_fetch_assoc($result_adviser);
        $advisers_display = $row_adviser['fname'] . ' ' . $row_adviser['lname'];
    } else {
        $advisers_display = ''; // Handle case when no adviser is selected
    }

    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $keywords = mysqli_real_escape_string($conn, $_POST['keywords']);

    // Handle file uploads
    $coverImage = uploadFile('cover_image');
    $approvalSheet = uploadFile('approval_sheet');

    // Insert query with BLOB data
    $sql = "INSERT INTO document (research_id, title, year, abstract, author, panel_id, panel, adviser_id, adviser, course, keywords, cover, approval_sheet)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

    // Prepare statement
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt === false) {
        die('MySQL prepare error: ' . mysqli_error($conn));
    }

    // Bind parameters
    mysqli_stmt_bind_param($stmt, "sssssssssssss", $research_id, $title, $year, $abstract, $authors, $panelists, $panelists_display, $adviser, $advisers_display, $course, $keywords, $coverImage, $approvalSheet);

    // Execute statement
    if (mysqli_stmt_execute($stmt)) {
        // Redirect to research.php after successful insertion
        header("Location: ../research.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    // Close statement
    mysqli_stmt_close($stmt);
}

// Close connection
mysqli_close($conn);
?>
