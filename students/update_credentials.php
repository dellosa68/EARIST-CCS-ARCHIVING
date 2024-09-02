<?php
session_start();
include "../conn/db_conn.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["check_username"])) {
        // Handle AJAX request to check if username exists
        $username = $_POST["username"];
        $stmt = $conn->prepare("SELECT COUNT(*) FROM accounts WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        echo $count > 0 ? 'exists' : 'available';
        $stmt->close();
        $conn->close();
        exit; // Exit after handling AJAX request
    } else {
        // Handle form submission to update credentials
        $account_id = $_POST["account_id"];
        $username = $_POST["username"];
        $final_password = $_POST["final_password"];

        // Retrieve recipient email from accounts table
        $stmt = $conn->prepare("SELECT email FROM accounts WHERE account_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $stmt->bind_result($recipient_email);
        $stmt->fetch();
        $stmt->close();

        // Update credentials in accounts table
        $stmt = $conn->prepare("UPDATE accounts SET username = ?, password = ? WHERE account_id = ?");
        $stmt->bind_param("sss", $username, $final_password, $account_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Also update credentials in students table
            $stmt = $conn->prepare("UPDATE students SET username = ?, password = ? WHERE account_id = ?");
            $stmt->bind_param("sss", $username, $final_password, $account_id);
            $stmt->execute();
            $stmt->close();

            $conn->close();

            // Send email with new credentials
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
                $mail->SMTPAuth = true;
                $mail->Username = 'dellosa.j.bsinfotech@gmail.com'; // SMTP username
                $mail->Password = 'yuoj shlu jdwo cyhh'; // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                //Recipients
                $mail->setFrom('your_email@example.com', 'Your Name');
                $mail->addAddress($recipient_email); 

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your Credentials Have Been Updated';
                $mail->Body = '
                <html>
                <head>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f2f2f2; }
                            .container { width: 80%; margin: 0 auto; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
                            .header { background: #3498db; color: #ffffff; padding: 10px 20px; border-radius: 8px 8px 0 0; }
                            .content { padding: 20px; }
                            .footer { background: #f1f1f1; padding: 10px 20px; text-align: center; color: #888; }
                            h1 { font-size: 24px; margin: 0; }
                            p { font-size: 16px; line-height: 1.5; margin: 10px 0; }
                            .button { background: #3498db; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                            .button:hover { background: #217dbb; }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <div class="header">
                                <h1>Credentials Updated</h1>
                            </div>
                            <div class="content">
                                <p>Hello,</p>
                                <p>Your credentials have been successfully updated.</p>
                                <p><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>
                                <p><strong>Password:</strong> ' . htmlspecialchars($final_password) . '</p>
                                <p>If you would like to update your personal information, please visit your profile page.</p>
                                <p>Thank you for using our service.</p>
                                <a href="http://example.com/login" class="button">Login Now</a>
                            </div>
                            <div class="footer">
                                <p>&copy; ' . date('Y') . ' Your Company. All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>';

                $mail->send();
                header("Location: ../index.php?message=Credentials updated successfully.");
                exit();
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error updating credentials: " . $stmt->error;
        }
    }
} elseif (isset($_GET["account_id"])) {
    $account_id = $_GET["account_id"];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha384-**YourIntegrityCodeHere**" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 10px;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 120vh;
        }
        form {
            width: 400px;
            border: 1px solid #ccc;
            padding: 30px;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        input {
            display: block;
            border: 1px solid #ccc;
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
        }
        label {
            color: #555;
            font-size: 16px;
            margin-bottom: 8px;
        }
        button {
            background: #3498db;
            padding: 12px 20px;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #217dbb;
        }
        .error {
            background: #e74c3c;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .warning {
            color: #e74c3c;
            font-size: 14px;
            margin: 5px 0;
        }
        .input-warning {
            border-color: #e74c3c;
        }
        .password-container {
            position: relative;
        }
        input[type="password"] {
            padding-right: 35px; /* Space for the icon */
        }
        .toggle-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .validation-list {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 14px;
            display: none; /* Initially hidden */
        }
        .validation-list li {
            margin: 5px 0;
        }
        .valid {
            color: #28a745; /* Green color for valid criteria */
        }
        .invalid {
            color: #e74c3c; /* Red color for invalid criteria */
        }
    </style>
</head>
<body>
    <div class="container">
        <form id="updateForm" method="post">
            <h2>Update Credentials</h2>
            <?php if (isset($_GET['error'])) { ?>
            <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php } ?>
            <input type="hidden" name="account_id" value="<?php echo htmlspecialchars($account_id); ?>">
            <label for="username">New Username:</label>
            <input type="text" id="username" name="username" class="form-control" required>
            <p class="warning" id="usernameWarning" style="display: none;">Username is already taken.</p>
            <label for="password">New Password:</label>
            <div class="password-container">
                <input type="password" id="final_password" name="final_password" class="form-control" required>
                <i class="toggle-icon fa fa-eye" id="togglePassword"></i>
            </div>
            <ul class="validation-list" id="validationList">
                <li id="length" class="invalid">At least 8 characters long</li>
                <li id="uppercase" class="invalid">At least one uppercase letter</li>
                <li id="number" class="invalid">At least one number</li>
            </ul>
            <button type="submit">Update</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Check if username exists
            $('#username').on('blur', function() {
                var username = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: '',
                    data: { check_username: true, username: username },
                    success: function(response) {
                        if (response === 'exists') {
                            $('#usernameWarning').show();
                        } else {
                            $('#usernameWarning').hide();
                        }
                    }
                });
            });

            // Toggle password visibility
            $('#togglePassword').on('click', function() {
                var passwordField = $('#final_password');
                var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });

            // Password validation
            $('#final_password').on('keyup', function() {
                var password = $(this).val();
                var lengthValid = password.length >= 8;
                var uppercaseValid = /[A-Z]/.test(password);
                var numberValid = /[0-9]/.test(password);

                $('#length').toggleClass('valid', lengthValid).toggleClass('invalid', !lengthValid);
                $('#uppercase').toggleClass('valid', uppercaseValid).toggleClass('invalid', !uppercaseValid);
                $('#number').toggleClass('valid', numberValid).toggleClass('invalid', !numberValid);

                $('#validationList').show();
            });
        });
    </script>
</body>
</html>
<?php
}
?>
