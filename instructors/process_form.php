<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve selected items and their statuses
    $selected_items = isset($_POST['selected_items']) ? $_POST['selected_items'] : [];
    $checkbox_statuses = isset($_POST['checkbox_status']) ? $_POST['checkbox_status'] : [];

    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "research";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL statement to insert items with default status
    $stmt = $conn->prepare("INSERT INTO comments (comment_text, status) VALUES (?, ?)");

    // Bind parameters and execute for each item
    foreach ($selected_items as $index => $item) {
        $status = $checkbox_statuses[$index] ?? 'NO'; // Default to 'NO' if not set
        $stmt->bind_param("ss", $item, $status);
        $stmt->execute();
    }

    // Handle unchecked items
    $all_items = explode(',', $_POST['item_list']);
    foreach ($all_items as $item) {
        $item = trim($item);
        if (!in_array($item, $selected_items)) {
            // Save unchecked items as well with status 'NO'
            $status = 'NO';
            $stmt->bind_param("ss", $item, $status);
            $stmt->execute();
        }
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Redirect to the display page
    header("Location: sample.php");
    exit();
} else {
    echo "Error: Form submission method not allowed.";
}
?>
