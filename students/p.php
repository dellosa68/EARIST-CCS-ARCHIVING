<?php
session_start(); // Start the session

include_once("../conn/db_conn.php"); // Include database connection file

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Query to retrieve research data
$sql = "SELECT * FROM unreleased_research";
$result = mysqli_query($conn, $sql);

// Check if research data exists
if (mysqli_num_rows($result) > 0) {
    // Fetch and display research details
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<h2>Title: " . $row["title"] . "</h2>";
        echo "<p>Abstract: " . $row["abstract"] . "</p>";
        echo "<p>Author(s): " . $row["author"] . "</p>";
        echo "<p>Panel(s): " . $row["panel"] . "</p>";
        echo "<p>Adviser(s): " . $row["adviser"] . "</p>";
        echo "<p>Course: " . $row["course"] . "</p>";
        echo "<p>System Link: " . $row["system_link"] . "</p>";
        echo "<p>Keywords: " . $row["keywords"] . "</p>";
        
        // Add a link to download the source code zip file
        echo "<p>Source Code: <a href='download.php?research_id=" . $row["id"] . "'>Download</a></p>";

        echo "<hr>";
    }
} else {
    echo "No research data found.";
}

// Close connection
mysqli_close($conn);
?>
