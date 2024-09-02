<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include database connection
    include 'db_conn.php';

    // Generate 9-digit account ID
    $account_id = sprintf("%09d", rand(1, 999999999));

    // Process form fields
    $first_name = $_POST['first-name'];
    $last_name = $_POST['last-name'];
    $email = $_POST['email'];
    $title = $_POST['title'];
    $middle = $_POST['middle'];
    $role = $_POST['role'];

    // File upload handling
    $image = $_FILES['instructor-picture']['tmp_name'];
    $image = addslashes(file_get_contents($image)); // Read image content and add slashes for binary data

   
    $account_sql = "INSERT INTO accounts (account_id, email, role) 
                    VALUES ('$account_id', '$email', '$role')";
    if (!mysqli_query($conn, $account_sql)) {
        echo "Error: " . $account_sql . "<br>" . mysqli_error($conn);
        mysqli_close($conn);
        exit;
    }

    // Insert data into instructors table with image stored as blob
    $instructor_sql = "INSERT INTO instructors (account_id, fname, lname, email, role, pic, panel_id, adviser_id, title, post_nominal) 
                        VALUES ('$account_id', '$first_name', '$last_name', '$email', 'Instructor', '$image', '$account_id', '$account_id', '$title', '$post_nominal')";
    if (mysqli_query($conn, $instructor_sql)) {
        // Close database connection
        mysqli_close($conn);
        // Show aesthetic alert
        echo "<script>
                alert('Instructor added successfully');
                window.location.href = '../admin/users.php';
              </script>";
        exit;
    } else {
        echo "Error: " . $instructor_sql . "<br>" . mysqli_error($conn);
    }

    // Close database connection
    mysqli_close($conn);
}
?>
