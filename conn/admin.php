<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include your database connection file
    include 'db_conn.php'; // Replace with your actual database connection file

    // Get username, password, and role from form
    $username = $_POST['username'];
    $password = $_POST['password'];
    $account_id = $_POST['account_id'];
    $role = $_POST['role'];

    // Perform SQL query to check if username and password match
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND role = '$role'";
    $result = mysqli_query($conn, $sql);

    // Check if query returned any rows
    if (mysqli_num_rows($result) == 1) {
        // Login successful, redirect based on role
        if ($role == 'Admin') {
            header("Location: ../admin/home.php"); // Redirect to admin page
            exit;
        } elseif ($role == 'Instructor') {
            header("Location: ../instructor/home.php"); // Redirect to instructor page
            exit;
        }
    } else {
        // Login failed, redirect back to login page with error message
        header("Location: ../index.php?error=login_failed");
        exit;
    }
} else {
    // Redirect to login page if accessed directly without submitting form
    header("Location: ../index.php");
    exit;
}
?>
