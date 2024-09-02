<?php
// Database connection
include '../../conn/db_conn.php';

// Fetch form data
$research_id = $_POST['research_id'];
$title = isset($_POST['title']) ? htmlspecialchars(trim($_POST['title'])) : null;
$abstract = isset($_POST['abstract']) ? htmlspecialchars(trim($_POST['abstract'])) : null;
$keywords = isset($_POST['keywords']) ? htmlspecialchars(trim($_POST['keywords'])) : null;
$year = isset($_POST['year']) ? htmlspecialchars(trim($_POST['year'])) : null;
$course = isset($_POST['course']) ? htmlspecialchars(trim($_POST['course'])) : null;
$authors = isset($_POST['author_ids']) ? htmlspecialchars(trim($_POST['author_ids'])): null;
$authorsDisplay = isset($_POST['authorsDisplay']) ? htmlspecialchars(trim($_POST['authorsDisplay'])) : null;
$panelists = isset($_POST['panel_ids']) ? htmlspecialchars(trim($_POST['panel_ids'])) : null;
$panelistsDisplay = isset($_POST['panelistsDisplay']) ? htmlspecialchars(trim($_POST['panelistsDisplay'])) : null;
$adviser_id = isset($_POST['adviser_id']) ? $_POST['adviser_id'] : null;
$adviser = isset($_POST['adviser']) ? htmlspecialchars(trim($_POST['adviser'])) : null;

// Handle cover image upload
if (isset($_FILES['cover']) && $_FILES['cover']['error'] == UPLOAD_ERR_OK) {
    $cover_image = $_FILES['cover']['tmp_name'];
    $cover_image_data = file_get_contents($cover_image);
    $cover_image_base64 = base64_encode($cover_image_data);
} else {
    $cover_image_base64 = null;
}

// Construct SQL query dynamically
$updates = [];
$params = [];
$types = '';

if ($title !== null) {
    $updates[] = "title = ?";
    $params[] = $title;
    $types .= 's';
}
if ($abstract !== null) {
    $updates[] = "abstract = ?";
    $params[] = $abstract;
    $types .= 's';
}
if ($keywords !== null) {
    $updates[] = "keywords = ?";
    $params[] = $keywords;
    $types .= 's';
}
if ($year !== null) {
    $updates[] = "year = ?";
    $params[] = $year;
    $types .= 's';
}
if ($course !== null) {
    $updates[] = "course = ?";
    $params[] = $course;
    $types .= 's';
}
if ($authors !== null && $authors !== '') { // Handle non-empty authors
    $updates[] = "author_id = ?";
    $params[] = $authors;
    $types .= 's';
}
if ($authorsDisplay !== null) {
    $updates[] = "author = ?";
    $params[] = $authorsDisplay;
    $types .= 's';
}
if ($panelists !== null && $panelists !== '') { // Handle non-empty panelists
    $updates[] = "panel_id = ?";
    $params[] = $panelists;
    $types .= 's';
}
if ($panelistsDisplay !== null) {
    $updates[] = "panel = ?";
    $params[] = $panelistsDisplay;
    $types .= 's';
}
if ($adviser_id !== null) {
    $updates[] = "adviser_id = ?";
    $params[] = $adviser_id;
    $types .= 's';
}
if ($adviser !== null) {
    $updates[] = "adviser = ?";
    $params[] = $adviser;
    $types .= 's';
}
if ($cover_image_base64 !== null) {
    $updates[] = "cover = ?";
    $params[] = $cover_image_base64;
    $types .= 's';
}

// Check if there are any updates to be made
if (empty($updates)) {
    die('No fields to update.');
}

// Construct the SQL query
$sql = "UPDATE document SET " . implode(", ", $updates) . " WHERE research_id = ?";
$params[] = $research_id;
$types .= 's';

// Debugging: Output SQL query and parameters
echo "<h3>SQL Query:</h3>";
echo "<pre>";
echo "SQL: " . $sql . "<br>";
echo "Params: ";
var_dump($params);
echo "Types: " . $types;
echo "</pre>";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

// Bind parameters
$stmt->bind_param($types, ...$params);

// Execute the statement
if (!$stmt->execute()) {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
} else {
     header("Location: ../research_details.php?research_id=" . urlencode($research_id));
    exit(); 
}

$stmt->close();
$conn->close();
?>
