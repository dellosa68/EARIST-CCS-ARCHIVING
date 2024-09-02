<?php
session_start();
include '../../conn/db_conn.php';

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the checked comments from the request
    $checked_comments = isset($_POST['checked_comments']) ? $_POST['checked_comments'] : [];

    // Get the account_id from session
    $account_id = $_SESSION['account_id'];

    // Prepare and execute the update query for each checked comment
    foreach ($checked_comments as $comment) {
        $stmt = $conn->prepare("UPDATE comments SET status = 'YES' WHERE account_id = ? AND comment_text = ?");
        $stmt->bind_param('is', $account_id, $comment);
        $stmt->execute();
        $stmt->close();
    }

    // Close connection
    $conn->close();

    // Redirect or send a success response
    echo "Comments updated successfully!";
    exit();
} else {
    echo "Invalid request method.";
    exit();
}
?>
