<?php
session_start();
include 'conn/db_conn.php';
require 'vendor/autoload.php'; // Ensure you include the Composer autoload file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['email']) && isset($_POST['student_no'])) {
    $email = $_POST['email'];
    $student_no = $_POST['student_no'];

    // Check if email and student number exist in the database
    $sql = "SELECT account_id FROM student WHERE email=? AND student_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $student_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Fetch account_id
        $row = $result->fetch_assoc();
        $account_id = $row['account_id'];

        // Generate a random PIN
        $pin = rand(100000, 999999);

        $_SESSION['pin'] = $pin;
        $_SESSION['pin_expiration'] = time() + 600; // PIN valid for 10 minutes
        $_SESSION['email'] = $email;
        $_SESSION['student_no'] = $student_no;
        $_SESSION['account_id'] = $account_id;
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
            $mail->setFrom('no-reply@yourdomain.com', 'Your App Name');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset PIN';
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
                    .pin {
                        font-size: 24px;
                        color: #e74c3c;
                        text-align: center;
                        margin: 20px 0;
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
                    <h1>Password Reset PIN</h1>
                    <p>Dear Student,</p>
                    <p>We received a request to reset your password. Please use the PIN below to verify your email address and reset your password. This PIN is valid for 10 minutes.</p>
                    <p><strong>Student ID:</strong> ' . htmlspecialchars($student_no) . '</p>
                    <p><strong>Account ID:</strong> ' . htmlspecialchars($account_id) . '</p>
                    <div class="pin">' . htmlspecialchars($pin) . '</div>
                    <p>If you did not request a password reset, please ignore this email or contact support if you have questions.</p>
                    <p>Best regards,<br>Your App Name Team</p>
                    <div class="footer">
                        &copy; ' . date('Y') . ' Your App Name. All rights reserved.
                    </div>
                </div>
            </body>
            </html>
            ';

            $mail->send();
            header("Location: verify_pin.php");
            exit();
        } catch (Exception $e) {
            header("Location: forgot_password.php?error=Failed to send email. Mailer Error: {$mail->ErrorInfo}");
            exit();
        }
    } else {
        header("Location: forgot_password.php?error=Invalid email or student number");
        exit();
    }
} else {
    header("Location: forgot_password.php?error=Please enter both email and student number");
    exit();
}
?>