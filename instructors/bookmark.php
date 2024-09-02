<?php
include '../conn/db_conn.php';
session_start();

if (!isset($_SESSION["username"])) {
    echo json_encode(['message' => 'You must be logged in to bookmark.']);
    exit();
}

function generateUniqueID($length = 8) {
    return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $length)), 0, $length);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'];
    $research_id = $_POST['research_id'];
    $title = $_POST['title'];
    $bookmark_id = generateUniqueID();

    // Check if the bookmark already exists
    $sql = "SELECT * FROM bookmarks WHERE username = ? AND research_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $username, $research_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['message' => 'This research is already bookmarked.', 'success' => false]);
    } else {
        // Insert the bookmark
        $sql = "INSERT INTO bookmarks (bookmark_id, username, research_id, title) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssis", $bookmark_id, $username, $research_id, $title);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Research successfully bookmarked.', 'success' => true]);
        } else {
            echo json_encode(['message' => 'Failed to bookmark research.', 'success' => false]);
        }
    }

    $stmt->close();
}

$conn->close();
?>
