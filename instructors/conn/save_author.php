<?php
session_start();
include "../../conn/db_conn.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php'; // Adjust the path if needed

// Function to generate a unique 8-digit account ID
function generateUniqueAccountId($conn) {
    do {
        $account_id = mt_rand(10000000, 99999999);
        $stmt = $conn->prepare("SELECT COUNT(*) FROM student WHERE account_id = ?");
        $stmt->bind_param("s", $account_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    } while ($count > 0);
    
    return $account_id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $student_id = $_POST["student_id"];
    $course = $_POST["course"];
    $year = $_POST["year"];
    $section = $_POST["section"];
    $profile_picture = $_FILES["profile_picture"];

    // Validate and process the profile picture
    if ($profile_picture["error"] === UPLOAD_ERR_OK) {
        $profile_picture_tmp = $profile_picture["tmp_name"];
        $profile_picture_content = file_get_contents($profile_picture_tmp);
    } else {
        echo "Error uploading the profile picture.";
        exit();
    }

    // Generate a unique 8-digit account ID
    $account_id = generateUniqueAccountId($conn);

    // Insert into the student table
    $stmt = $conn->prepare("INSERT INTO student (account_id, first_name, last_name, email, student_id, course, year, section, profile_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $account_id, $first_name, $last_name, $email, $student_id, $course, $year, $section, $profile_picture_content);

    if ($stmt->execute()) {
        // Insert into the accounts table with role "Student"
        $role = "Student";
        $stmt_account = $conn->prepare("INSERT INTO accounts (account_id, email, profile_pic, role) VALUES (?, ?, ?, ?)");
        $stmt_account->bind_param("ssss", $account_id, $email, $profile_picture_content, $role);
        
        if ($stmt_account->execute()) {
            // Send confirmation email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'dellosa.j.bsinfotech@gmail.com';
                $mail->Password = 'yuoj shlu jdwo cyhh';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('dellosa.j.bsinfotech@gmail.com', 'CCS Research Dept');
                $mail->addAddress($email); // Add recipient

$mail->isHTML(true);
$mail->Subject = 'Account Successfully Created';
$mail->Body    = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .header {
            text-align: center;
            padding: 10px;
            background: #007bff;
            color: #fff;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .content h2 {
            color: #007bff;
        }
        .content p {
            font-size: 16px;
            line-height: 1.5;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            padding: 10px;
            background: #f4f4f4;
            border-radius: 0 0 8px 8px;
            font-size: 14px;
            color: #666;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Our System!</h1>
        </div>
        <div class="content">
            <h2>Hello ' . htmlspecialchars($first_name) . ' ' . htmlspecialchars($last_name) . ',</h2>
            <p>We are excited to inform you that your account has been successfully created. You are now registered as an <strong>Author</strong> in our system.</p>
            <p>You can update your credentials by visiting the following link:</p>
            <p><a href="localhost/research/students/update_credentials.php">Update Your Credentials</a></p>
            <p>If you have any questions or need further assistance, feel free to contact us.</p>
            <p>Thank you for joining us!</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 Your Company Name. All rights reserved.</p>
            <p><a href="https://www.yourcompanywebsite.com">Visit our website</a></p>
        </div>
    </div>
</body>
</html>';

$mail->AltBody = 'Hello ' . htmlspecialchars($first_name) . ' ' . htmlspecialchars($last_name) . ',\n\nWe are excited to inform you that your account has been successfully created. You are now registered as an Author in our system.\n\nYou can update your credentials by visiting the following link: http://www.yourwebsite.com/update_credentials.php\n\nThank you for joining us!\n\nVisit our website: https://www.yourcompanywebsite.com';

                $mail->send();
                echo 'Confirmation email has been sent.';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

            header("Location: ../success.php");
            exit();
        } else {
            echo "Error inserting into the accounts table: " . $stmt_account->error;
        }
    } else {
        echo "Error inserting into the student table: " . $stmt->error;
    }

    $stmt->close();
    $stmt_account->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
