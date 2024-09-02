<?php
session_start();
include "conn/db_conn.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

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

        // Retrieve recipient email from database
        $stmt = $conn->prepare("SELECT email FROM accounts WHERE account_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $stmt->bind_result($recipient_email);
        $stmt->fetch();
        $stmt->close();

        // Update credentials
        $stmt = $conn->prepare("UPDATE accounts SET username = ?, password = ? WHERE account_id = ?");
        $stmt->bind_param("sss", $username, $final_password, $account_id);

        if ($stmt->execute()) {
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
                header("Location: index.php?message=Credentials updated successfully.");
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
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required onkeyup="checkUsername()">
            <p id="usernameWarning" class="warning"></p>
            <label for="new_password">Password</label>
            <div class="password-container">
                <input type="password" name="new_password" id="new_password" placeholder="New Password" onkeyup="validatePassword()" required>
                <i class="fas fa-eye-slash toggle-icon" onclick="togglePassword('new_password', this)"></i>
            </div>
            <ul id="passwordValidation" class="validation-list">
                <li id="minLength" class="invalid">At least 8 characters</li>
                <li id="uppercase" class="invalid">At least one uppercase letter</li>
                <li id="lowercase" class="invalid">At least one lowercase letter</li>
                <li id="number" class="invalid">At least one number</li>
                <li id="specialChar" class="invalid">At least one special character</li>
            </ul>
            <label for="confirm_password">Confirm Password</label>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" onkeyup="checkPasswords()" required>
                <input type="hidden" name="final_password" id="final_password">
                <i class="fas fa-eye-slash toggle-icon" onclick="togglePassword('confirm_password', this)"></i>
            </div>
            <p id="passwordWarning" class="warning"></p>
            <button type="submit" id="submitButton" disabled>Reset Password</button>
        </form>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        function togglePassword(fieldId, icon) {
            var passwordField = document.getElementById(fieldId);
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                passwordField.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }

        function validatePassword() {
            var password = document.getElementById('new_password').value;
            var minLength = document.getElementById('minLength');
            var uppercase = document.getElementById('uppercase');
            var lowercase = document.getElementById('lowercase');
            var number = document.getElementById('number');
            var specialChar = document.getElementById('specialChar');
            var submitButton = document.getElementById('submitButton');
            var passwordValidation = document.getElementById('passwordValidation');

            // Regular expressions for validation
            var hasUppercase = /[A-Z]/;
            var hasLowercase = /[a-z]/;
            var hasNumber = /[0-9]/;
            var hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/;

            // Show or hide validation list based on whether there is a password value
            if (password.length > 0) {
                passwordValidation.style.display = 'block';
            } else {
                passwordValidation.style.display = 'none';
            }

            // Validation checks
            var isValid = true;

            // Check minimum length
            if (password.length >= 8) {
                minLength.classList.add('valid');
                minLength.classList.remove('invalid');
            } else {
                minLength.classList.add('invalid');
                minLength.classList.remove('valid');
                isValid = false;
            }

            // Check uppercase
            if (hasUppercase.test(password)) {
                uppercase.classList.add('valid');
                uppercase.classList.remove('invalid');
            } else {
                uppercase.classList.add('invalid');
                uppercase.classList.remove('valid');
                isValid = false;
            }

            // Check lowercase
            if (hasLowercase.test(password)) {
                lowercase.classList.add('valid');
                lowercase.classList.remove('invalid');
            } else {
                lowercase.classList.add('invalid');
                lowercase.classList.remove('valid');
                isValid = false;
            }

            // Check number
            if (hasNumber.test(password)) {
                number.classList.add('valid');
                number.classList.remove('invalid');
            } else {
                number.classList.add('invalid');
                number.classList.remove('valid');
                isValid = false;
            }

            // Check special character
            if (hasSpecialChar.test(password)) {
                specialChar.classList.add('valid');
                specialChar.classList.remove('invalid');
            } else {
                specialChar.classList.add('invalid');
                specialChar.classList.remove('valid');
                isValid = false;
            }

            // Enable/Disable the submit button
            submitButton.disabled = !isValid;
        }

        function checkPasswords() {
            var newPassword = document.getElementById('new_password').value;
            var confirmPassword = document.getElementById('confirm_password').value;
            var finalPassword = document.getElementById('final_password');

            if (newPassword === confirmPassword) {
                finalPassword.value = newPassword; // Set the value of final_password if passwords match
                document.getElementById('confirm_password').classList.remove('input-warning');
                document.getElementById('passwordWarning').textContent = '';
            } else {
                finalPassword.value = ''; // Clear final_password if passwords don't match
                document.getElementById('confirm_password').classList.add('input-warning');
                document.getElementById('passwordWarning').textContent = 'Passwords do not match';
            }
        }

        function checkUsername() {
            var username = document.getElementById('username').value;
            var usernameWarning = document.getElementById('usernameWarning');
            var submitButton = document.getElementById('submitButton');

            if (username.length > 0) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '', true); // Send request to the same page
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        if (xhr.responseText == 'exists') {
                            usernameWarning.textContent = 'Username already taken';
                            document.getElementById('username').classList.add('input-warning');
                            submitButton.disabled = true;
                        } else {
                            usernameWarning.textContent = '';
                            document.getElementById('username').classList.remove('input-warning');
                            submitButton.disabled = false;
                        }
                    }
                };
                xhr.send('check_username=true&username=' + encodeURIComponent(username));
            } else {
                usernameWarning.textContent = '';
                document.getElementById('username').classList.remove('input-warning');
                submitButton.disabled = false;
            }
        }
    </script>
</body>
</html>
<?php
} else {
    echo "Invalid request.";
}
?>
