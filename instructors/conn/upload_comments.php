<?php
session_start();
include '../../conn/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $item_list = isset($_POST['item_list']) ? $_POST['item_list'] : '';
    $account_id = isset($_POST['account_id']) ? $_POST['account_id'] : '';
    $research_id = isset($_POST['research_id']) ? $_POST['research_id'] : '';

    // Fetch the user's full name from the account_id
    $stmt = $conn->prepare("SELECT fname, lname FROM instructors WHERE account_id = ?");
    $stmt->bind_param('i', $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $fullname = $user['first_name'] . ' ' . $user['last_name'];
    $stmt->close();

    // Split the item_list into individual comments
    $comments = explode(';', $item_list);

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO comments (account_id, research_id, comment_text, full_name, status) VALUES (?, ?, ?, ?, ?)");

    // Loop through comments and insert each one
    foreach ($comments as $comment) {
        if (!empty(trim($comment))) {
            $status = 'NO'; // Default status for new comments
            $stmt->bind_param('iisss', $account_id, $research_id, $comment, $fullname, $status);
            $stmt->execute();
        }
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Redirect or show a success message
    header("Location: ../research.php?research_id=" . $research_id); // Redirect back to the research page
    exit();
} else {
    echo "Invalid request method.";
}
?>
