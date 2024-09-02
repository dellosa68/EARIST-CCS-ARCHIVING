<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include '../../conn/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $research_id = isset($_POST['research_id']) ? $conn->real_escape_string($_POST['research_id']) : null;

    if ($research_id) {
        $cover_image = null;
        $approval_sheet = null;

        // Handle cover_image upload
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == UPLOAD_ERR_OK) {
            $cover_image = file_get_contents($_FILES['cover_image']['tmp_name']);
        }

        // Handle approval_sheet upload
        if (isset($_FILES['approval_sheet']) && $_FILES['approval_sheet']['error'] == UPLOAD_ERR_OK) {
            $approval_sheet = file_get_contents($_FILES['approval_sheet']['tmp_name']);
        }

        // Prepare SQL to update the BLOB fields
        $sql = "UPDATE unreleased_research SET cover = ?, approval_sheet = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $null = NULL;
        $stmt->bind_param('bbi', $null, $null, $research_id);

        // Send the BLOB data
        $stmt->send_long_data(0, $cover_image);
        $stmt->send_long_data(1, $approval_sheet);

        // Execute the query
        if ($stmt->execute()) {
            echo "<script>window.location.reload();</script>";
        } else {
            echo "Error uploading files: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Invalid research ID.";
    }

    $conn->close();
}
?>
