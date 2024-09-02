<?php
include('../../conn/db_conn.php');

if (isset($_POST['query'])) {
    $query = trim($_POST['query']);
    $stmt = $conn->prepare("SELECT student_id, first_name, last_name, profile_pic FROM student WHERE CONCAT(first_name, ' ', last_name) LIKE ? LIMIT 10");
    $searchTerm = '%' . $query . '%';
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
        $profilePic = $row['profile_pic']; 
        $accountId = $row['student_id'];
        $base64Pic = base64_encode($profilePic);
        $mimeType = 'image/jpeg'; 
        $base64Src = 'data:' . $mimeType . ';base64,' . $base64Pic;

        echo '<a href="#" class="dropdown-item author-option" data-fullname="' . $fullName . '" data-id="' . $accountId . '" data-accountid="' . $accountId . '" data-profilepic="' . $base64Src . '">';
        echo '<img src="' . $base64Src . '" alt="' . $fullName . '" class="rounded-circle mr-2" width="30">';
        echo $fullName;
        echo '</a>';
    }

    $stmt->close();
}
?>
