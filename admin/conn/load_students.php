<?php
include "../../conn/db_conn.php";

// Fetch course filter value if set
$courseFilter = isset($_POST['courseFilter']) ? $_POST['courseFilter'] : '';

// Modify the SQL query based on the selected course
$sql = "SELECT first_name, last_name, pic FROM student";
if ($courseFilter) {
    $sql .= " WHERE course = '$courseFilter'";
}
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($student = mysqli_fetch_assoc($result)) {
        echo '<div class="instructor-box">';
        if (!empty($student['pic'])) {
            $profile_pic_base64 = base64_encode($student['pic']);
            echo '<img src="data:image/jpeg;base64,' . $profile_pic_base64 . '" alt="Student Profile">';
        } else {
            echo '<img src="https://via.placeholder.com/100" alt="Placeholder">';
        }
        echo '<p>' . $student['first_name'] . ' ' . $student['last_name'] . '</p>';
        echo '</div>';
    }
} else {
    echo "No students found.";
}
?>
