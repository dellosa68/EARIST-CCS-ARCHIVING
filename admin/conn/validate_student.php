<?php
$host = 'localhost'; // or your database host
$db = 'research'; // your database name
$user = 'root'; // your database username
$pass = ''; // your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
$email = $_POST['email'];
$student_id = $_POST['student_id'];

// Initialize response array
$response = [
    'email_exists' => false,
    'student_id_exists' => false
];

// Check if email exists
$email_query = $pdo->prepare("SELECT COUNT(*) FROM student WHERE email = ?");
$email_query->execute([$email]);
if ($email_query->fetchColumn() > 0) {
    $response['email_exists'] = true;
}

// Check if student ID exists
$student_id_query = $pdo->prepare("SELECT COUNT(*) FROM student WHERE student_id = ?");
$student_id_query->execute([$student_id]);
if ($student_id_query->fetchColumn() > 0) {
    $response['student_id_exists'] = true;
}

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
