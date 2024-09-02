<?php
include_once("../conn/db_conn.php"); // Include database connection file

// Check if research ID is provided
if (isset($_GET["research_id"])) {
    $research_id = $_GET["research_id"];

    // Query to retrieve source code data
    $sql = "SELECT source_code FROM unreleased_research WHERE id = $research_id";
    $result = mysqli_query($conn, $sql);

    // Check if source code data exists
    if (mysqli_num_rows($result) == 1) {
        // Fetch the source code data
        $row = mysqli_fetch_assoc($result);
        $source_code = $row["source_code"];

        // Set headers for file download
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=source_code.zip");
        
        // Output the source code content
        echo $source_code;
    } else {
        echo "Source code not found.";
    }

    // Close connection
    mysqli_close($conn);
} else {
    echo "Invalid request.";
}
?>
