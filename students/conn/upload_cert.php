<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

include '../../conn/db_conn.php';

// Function to handle file upload and return file contents as binary data
function uploadFileAndConvertToBlob($fileInputName) {
    $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
    $fileName = $_FILES[$fileInputName]['name'];
    $fileType = $_FILES[$fileInputName]['type'];

    $fp = fopen($fileTmpPath, 'rb');
    $content = fread($fp, filesize($fileTmpPath));
    fclose($fp);

    $blobData = addslashes($content); // Escape special characters
    return $blobData;
}

// Handle each certificate upload and convert to BLOB
$statistician_cert_blob = uploadFileAndConvertToBlob("statistician_cert");
$plagscan_cert_blob = uploadFileAndConvertToBlob("plagscan_cert");
$grammarian_cert_blob = uploadFileAndConvertToBlob("grammarian_cert");

// Prepare and execute SQL query to insert BLOB data into database
if ($statistician_cert_blob && $plagscan_cert_blob && $grammarian_cert_blob) {
    $research_id = $_POST['research_id']; // Ensure you retrieve this securely
    $sql = "UPDATE unreleased_research SET statistician_cert = ?, plagscan_cert = ?, grammarian_cert = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $statistician_cert_blob, $plagscan_cert_blob, $grammarian_cert_blob, $research_id);
    
    if ($stmt->execute()) {
        echo "<script>window.location.reload();</script>";
    } else {
        echo "Error updating certificates: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error uploading one or more certificates.";
}

$conn->close();
?>
