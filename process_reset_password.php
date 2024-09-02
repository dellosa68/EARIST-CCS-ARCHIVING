<?php
session_start();
include 'conn/db_conn.php';
require 'vendor/autoload.php'; // Ensure you include the Composer autoload file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['final_password']) && !empty($_POST['final_password'])) {
    $final_password = $_POST['final_password'];
    $email = $_SESSION['email'];
    $account_id = $_SESSION['account_id'];

    // Update password in the database
    $sql = "UPDATE student SET password=? WHERE account_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $final_password, $account_id);

    if ($stmt->execute()) {
        // Fetch the username associated with the account
        $sql = "SELECT username FROM student WHERE account_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $account_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $username = $user['username'];

        // Send email with new password
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'dellosa.j.bsinfotech@gmail.com'; // SMTP username
            $mail->Password = 'yuoj shlu jdwo cyhh'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('no-reply@yourdomain.com', 'Your App Name');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Confirmation';
            $mail->Body    = '
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        color: #333;
                        line-height: 1.6;
                    }
                    .container {
                        width: 100%;
                        max-width: 600px;
                        margin: 0 auto;
                        background: #fff;
                        padding: 20px;
                        border-radius: 10px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    h1 {
                        text-align: center;
                        color: #3498db;
                    }
                    p {
                        font-size: 16px;
                        margin-bottom: 20px;
                    }
                    .footer {
                        text-align: center;
                        margin-top: 20px;
                        font-size: 14px;
                        color: #999;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Password Reset Successful</h1>
                    <p>Dear ' . htmlspecialchars($username) . ',</p>
                    <p>Your password has been successfully updated. Here are your new login credentials:</p>
                    <p><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>
                    <p><strong>New Password:</strong> ' . htmlspecialchars($final_password) . '</p>
                    <p>If you did not request this change, please contact support immediately.</p>
                    <p>Best regards,<br>Your App Name Team</p>
                    <div class="footer">
                        &copy; ' . date('Y') . ' Your App Name. All rights reserved.
                    </div>
                </div>
            </body>
            </html>
            ';

            $mail->send();
            header("Location: index.php?message=Password successfully updated. An email with your new credentials has been sent.");
            exit();
        } catch (Exception $e) {
            header("Location: reset_password.php?error=Failed to send email. Mailer Error: {$mail->ErrorInfo}");
            exit();
        }
    } else {
        header("Location: reset_password.php?error=Failed to update password");
        exit();
    }
} else {
    header("Location: reset_password.php?error=Invalid submission");
    exit();
}
?>
