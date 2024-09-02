<?php
session_start(); // Start the session
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are set and not empty
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Perform database query to check credentials
        // This is a basic example, you should use prepared statements to prevent SQL injection
        // Also, you should hash the password in the database and compare hashed values for security
        
        // Assuming you have a database table named 'accounts' with columns: username, password, and role
        $query = "SELECT * FROM accounts WHERE username = '$username' AND password = '$password' AND role = '$role'";
        $result = mysqli_query($conn, $query);

        if (!$result) {
            // Query failed
            echo "Error: " . mysqli_error($conn);
            exit();
        }

        $row = mysqli_fetch_assoc($result);
        if ($row) {
            // User found, redirect based on role
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['account_id'] = $row['account_id']; // Assuming account_id is a column in the accounts table
            $_SESSION['profile_pic'] = $row['profile_pic']; // Assuming 'pic' is the column storing profile picture blob

            if ($role == 'Admin') {
                // Redirect to admin page if credentials are correct
                header("Location: ../admin/home.php");
                exit();
            } elseif ($role == 'Instructor') {
                // Redirect to instructor page if credentials are correct
                header("Location: ../instructors/home.php");
                exit();
            }
        } else {
            // User not found or invalid credentials
            echo "Invalid username, password, or role!";
            exit();
        }
    } else {
        // Username or password is not set
        echo "Username or password is missing!";
        exit();
    }
} else {
    // Redirect back to login page if form is not submitted
    header("Location: ../admin.php");
    exit();
}
?>
