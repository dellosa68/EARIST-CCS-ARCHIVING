<?php
// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

// Database connection
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'research';

$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate form data (example)
if (empty($_POST['student_id']) || empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email']) || empty($_POST['phone']) || empty($_POST['course']) || empty($_POST['year']) || empty($_POST['section'])) {
    die("All form fields are required.");
}

// Sanitize and escape form data
$student_id = $conn->real_escape_string($_POST['student_id']);
$first_name = $conn->real_escape_string($_POST['first_name']);
$last_name = $conn->real_escape_string($_POST['last_name']);
$username = $conn->real_escape_string($_POST['username']);
$password = $conn->real_escape_string($_POST['password']);
$email = $conn->real_escape_string($_POST['email']);
$phone = $conn->real_escape_string($_POST['phone']);
$course = $conn->real_escape_string($_POST['course']);
$year = $conn->real_escape_string($_POST['year']);
$section = $conn->real_escape_string($_POST['section']);

// File upload handling
$profilePicture = $_FILES['profile-picture']['tmp_name'];
$profilePictureName = $_FILES['profile-picture']['name'];
$profilePictureType = $_FILES['profile-picture']['type'];
$certificate = $_FILES['certificate']['tmp_name'];
$certificateName = $_FILES['certificate']['name'];
$certificateType = $_FILES['certificate']['type'];

$allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
$profilePictureInfo = getimagesize($profilePicture);
$certificateInfo = getimagesize($certificate);

if (!in_array($profilePictureInfo[2], $allowedTypes) || !in_array($certificateInfo[2], $allowedTypes)) {
    die("Profile picture and certificate must be an image (PNG, JPEG, or GIF).");
}

$profilePictureData = file_get_contents($profilePicture);
$certificateData = file_get_contents($certificate);

$account_id = generateUniqueAccountId($conn);

$sql_student = "INSERT INTO student (account_id, student_id, first_name, last_name, username, password, email, phone, course, year, section, profile_pic, cor)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param("isssssssssssb", $account_id, $student_id, $first_name, $last_name, $username, $password, $email, $phone, $course, $year, $section, $profilePictureData, $certificateData);

if ($stmt_student->execute()) {
    $role = "Student";
    $sql_accounts = "INSERT INTO accounts (account_id, username, password, email, phone, role, profile_pic)
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_accounts = $conn->prepare($sql_accounts);
    $stmt_accounts->bind_param("issssss", $account_id, $username, $password, $email, $phone, $role, $profilePictureData);
    
    if ($stmt_accounts->execute()) {
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'dellosa.j.bsinfotech@gmail.com';
                $mail->Password = 'yuoj shlu jdwo cyhh';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom('your_email@example.com', 'Account Registration');
            $mail->addAddress($email, $first_name . ' ' . $last_name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to EARIST CCS Research Archiving System - Registration Successful';

            // Aesthetic and formal email content
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <div style='background-color: #f4f4f4; padding: 20px;'>
                    <h2 style='color: #0073e6; text-align: center;'>Welcome to EARIST CCS Research Archiving System</h2>
                </div>
                <div style='padding: 20px;'>
                    <p>Dear <strong>$first_name $last_name</strong>,</p>
                    <p>We are delighted to inform you that your registration with [Your University Name] has been successfully completed.</p>
                    <p>Below are your registration details:</p>
                    <table style='width: 100%; border: 1px solid #ccc; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ccc;'><strong>Account ID:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ccc;'>$account_id</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ccc;'><strong>Username:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ccc;'>$username</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ccc;'><strong>Course:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ccc;'>$course</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ccc;'><strong>Year & Section:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ccc;'>Year $year, Section $section</td>
                        </tr>
                    </table>
                    <p>If you have any questions or need further assistance, please do not hesitate to contact our support team.</p>
                    <p>We wish you all the best in your academic journey with us.</p>
                    <p>Sincerely,<br>The [Your University Name] Team</p>
                </div>
                <div style='background-color: #f4f4f4; padding: 20px; text-align: center;'>
                    <p style='font-size: 12px; color: #777;'>This email was sent by Welcome to EARIST CCS Research Archiving System. If you did not register with us, please disregard this email.</p>
                </div>
            </div>";

            $mail->send();
            header("Location: ../../index.php");
            exit();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error: " . $sql_accounts . "<br>" . $conn->error;
    }

    $stmt_accounts->close();
} else {
    echo "Error: " . $sql_student . "<br>" . $conn->error;
}

$stmt_student->close();
$conn->close();

// Function to generate unique 8-digit account_id
function generateUniqueAccountId($conn) {
    $account_id = mt_rand(10000000, 99999999);
    $query = "SELECT account_id FROM student WHERE account_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        return generateUniqueAccountId($conn);
    } else {
        return $account_id;
    }
}
?>
