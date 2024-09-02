<!DOCTYPE html>
<html>
<head>
    <title>File Upload and Display</title>
</head>
<body>
    <h2>Upload ZIP or RAR File</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
        Select file to upload:
        <input type="file" name="fileToUpload" id="fileToUpload" accept=".zip, .rar">
        <input type="submit" value="Upload File" name="submit">
    </form>
    <hr>
    <h2>Saved Files</h2>
    <ul>
        <?php
        // Check if form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileToUpload"])) {
            // Check if file was uploaded without errors
            if ($_FILES["fileToUpload"]["error"] == 0) {
                $allowed_extensions = array("zip", "rar");
                $file_extension = pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION);

                // Check file extension
                if (in_array($file_extension, $allowed_extensions)) {
                    // Read the file content
                    $file_content = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);

                    // Store file content in database
                    $conn = new mysqli("localhost", "root", "", "research");
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }
                    $file_name = $_FILES["fileToUpload"]["name"];
                    $sql = "INSERT INTO uploaded_files (FileName, FileData) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $file_name, $file_content);
                    if ($stmt->execute()) {
                        echo "<li>The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded and saved.</li>";
                    } else {
                        echo "<li>Error uploading file: " . $_FILES["fileToUpload"]["error"] . "</li>";
                    }
                    $stmt->close();
                    $conn->close();
                } else {
                    echo "<li>Sorry, only ZIP and RAR files are allowed.</li>";
                }
            } else {
                echo "<li>Error uploading file: " . $_FILES["fileToUpload"]["error"] . "</li>";
            }
        }

        // Display saved files
        $conn = new mysqli("localhost", "root", "", "research");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Query to fetch saved files
        $sql = "SELECT * FROM files";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo "<li><a href='download.php?id=" . $row['ID'] . "'>" . $row['FileName'] . "</a></li>";
            }
        } else {
            echo "<li>0 results</li>";
        }

        $conn->close();
        ?>
    </ul>
</body>
</html>
