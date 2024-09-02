<?php
include "../conn/db_conn.php";
if (isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    $sql = "SELECT * FROM student WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "exists";
    } else {
        echo "not_exists";
    }

    $stmt->close();
}

$conn->close();
?>