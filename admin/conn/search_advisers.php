<?php
include('../../conn/db_conn.php');

if (isset($_POST['query'])) {
    $query = trim($_POST['query']);
    $stmt = $conn->prepare("SELECT account_id, fname, lname, pic FROM instructors WHERE CONCAT(fname, ' ', lname) LIKE ? LIMIT 10");
    $searchTerm = '%' . $query . '%';
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result(); // Get result set from executed query

    while ($row = $result->fetch_assoc()) { // Fetch associative array of result
        $fullName = htmlspecialchars($row['fname'] . ' ' . $row['lname']);
        $profilePic = $row['pic']; // Raw BLOB data
        $accountId = htmlspecialchars($row['account_id']);

        // Convert BLOB data to base64
        $base64Pic = base64_encode($profilePic);
        $mimeType = 'image/jpeg'; // Adjust if you're using a different image format
        $base64Src = 'data:' . $mimeType . ';base64,' . $base64Pic;

        echo '<a href="#" class="dropdown-item adviser-option" data-fullname="' . $fullName . '" data-id="' . $accountId . '" data-profilepic="' . $base64Src . '">';
        echo '<img src="' . $base64Src . '" alt="' . $fullName . '" class="rounded-circle mr-2" width="30">';
        echo $fullName;
        echo '</a>';
    }

    $stmt->close();
}
?>
