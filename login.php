<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conn/db_conn.php';

if (isset($_POST['uname']) && isset($_POST['pass'])) {
    function validate($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $uname = validate($_POST['uname']);
    $pass = validate($_POST['pass']);

    if (empty($uname)) {
        header("Location: index.php?error=Username is required");
        exit();
    } else if (empty($pass)) {
        header("Location: index.php?error=Password is required");
        exit();
    } else {
        // Ensure to use password hashing in a real-world application
        $sql = "SELECT account_id, username, first_name, profile_pic, student_id FROM student WHERE username=? AND password=?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("ss", $uname, $pass);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            session_start();
            $_SESSION['username'] = $row['username'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['profile_pic'] = $row['profile_pic'];
            $_SESSION['account_id'] = $row['account_id'];
            $_SESSION['student_id'] = $row['student_id']; // Adding student_id to session
        
            header("Location: students/home.php");
            exit();
        } else {
            header("Location: index.php?error=Incorrect username or password");
            exit();
        }
    }
}
?>
