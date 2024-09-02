<?php
// Include your database connection file
include '../../conn/db_conn.php';

// Ensure the user is logged in
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Check if the research_id and cert_type are provided
if (isset($_GET['research_id']) && isset($_GET['cert_type'])) {
    $research_id = intval($_GET['research_id']); // Sanitize input
    $cert_type = $_GET['cert_type']; // Sanitize input
    
    // Validate cert_type
    $valid_cert_types = ['plagscan_cert', 'grammarian_cert', 'statistician_cert'];
    if (!in_array($cert_type, $valid_cert_types)) {
        echo "Invalid certificate type.";
        exit();
    }

    // Query to get the PDF BLOB data
    $sql = "SELECT $cert_type FROM unreleased_research WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $research_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($pdf_data);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && !empty($pdf_data)) {
        // Set headers to display the PDF in the browser
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename={$cert_type}.pdf");
        // Ensure no extra output before binary data
        echo $pdf_data;
    } else {
        echo "No PDF found.";
    }
    $stmt->close();
} else {
    echo "No research ID or certificate type provided.";
}
$conn->close();
?>
