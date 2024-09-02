<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saved Checkbox List</title>
</head>
<body>
    <h2>Saved Checkbox List with Status:</h2>
    <?php
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

    // Query to fetch saved items and their statuses
    $sql = "SELECT item_name, status FROM selected_items";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<ul>";
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            $item_name = htmlspecialchars($row["item_name"]);
            $status = $row["status"];
            $checked = ($status === 'YES') ? "checked" : "";
            echo "<li>";
            echo "<input type='checkbox' disabled $checked>";
            echo "<label>$item_name</label>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "No items found.";
    }

    // Close connection
    $conn->close();
    ?>
    <br>
    <a href="index.html">Back to Form</a>
</body>
</html>
