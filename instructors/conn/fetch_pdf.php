<?php
include '../../conn/db_conn.php';

// Retrieve the research_id from the URL query parameters
if (isset($_GET['research_id'])) {
    $research_id = $_GET['research_id'];

    // Fetch the PDF BLOB from the database
    $sql = "SELECT doc_soft_copy FROM unreleased_research WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $research_id);
    $stmt->execute();
    $stmt->bind_result($pdf_blob);
    $stmt->fetch();
    $stmt->close();

    // Check if PDF BLOB is retrieved
    if ($pdf_blob) {
        header('Content-Type: application/pdf');
        echo $pdf_blob;
    } else {
        echo "PDF not found.";
    }
} else {
    echo "Invalid research ID.";
}
?>
