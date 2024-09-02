<?php
// db_conn.php should be included to establish the database connection
include '../../conn/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $account_id = $_POST['account_id'];
    $title = $_POST['title'];
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $post = $_POST['post'];
    $role = $_POST['role'];
    $email = $_POST['email'];

    // Prepare the SQL query to update instructor details
    $sql = "UPDATE instructors SET title = ?, fname = ?, mi = ?, lname = ?, post_nominal = ?, role = ?, email = ? WHERE account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $title, $fname, $mname, $lname, $post, $role, $email, $account_id);

    // Execute the query
    if ($stmt->execute()) {
        // Check if a file was uploaded
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
            // Retrieve the file data
            $file_tmp = $_FILES['profile_pic']['tmp_name'];
            $file_data = file_get_contents($file_tmp);

            // Prepare the SQL query to update the profile picture
            $sql_pic = "UPDATE instructors SET pic = ? WHERE account_id = ?";
            $stmt_pic = $conn->prepare($sql_pic);
            $stmt_pic->bind_param("bi", $null, $account_id);

            // Send the binary data
            $stmt_pic->send_long_data(0, $file_data);

            if ($stmt_pic->execute()) {
                echo 'success';
            } else {
                echo 'Error updating profile picture: ' . $stmt_pic->error;
            }
            $stmt_pic->close();
        } else {
            echo 'success';
        }
    } else {
        echo 'Error updating instructor details: ' . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo 'Invalid request method';
}
?>
