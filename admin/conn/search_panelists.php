<?php
// Database connection
include('../../conn/db_conn.php');

// Get the search query
$query = $_POST['query'];

// Prepare and execute the query
$sql = "SELECT * FROM instructors WHERE CONCAT(fname, ' ', lname) LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $searchTerm);
$searchTerm = "%$query%";
$stmt->execute();
$result = $stmt->get_result();

// Fetch results and output HTML
$output = '';
while ($row = $result->fetch_assoc()) {
    $fullname = htmlspecialchars($row['fname'] . ' ' . $row['lname']);
    $account_id = htmlspecialchars($row['account_id']); // Ensure this is the correct field name
    $profile_pic = $row['pic']; // Assuming profile_pic is a field for profile image

    $output .= '<a href="#" class="dropdown-item panelist-option" data-fullname="' . $fullname . '" data-id="' . $row['account_id'] . '" data-accountid="' . $account_id . '">';
    if ($profile_pic) {
        $output .= '<img src="data:image/jpeg;base64,' . base64_encode($profile_pic) . '" alt="Profile Picture" style="width: 30px; height: 30px; border-radius: 50%;">';
    }
    $output .= $fullname . '</a>';
}

// Close connection
$stmt->close();
$conn->close();

// Output the results
echo $output;
?>
