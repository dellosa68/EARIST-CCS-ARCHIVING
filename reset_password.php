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
            padding: 0;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
    </style>
</head>
<body>
    <div class="container">
        <form action="process_reset_password.php" method="post">
            <h2>Reset Password</h2>
            <?php if (isset($_GET['error'])) { ?>
            <p class="error"><?php echo $_GET['error']; ?></p>
            <?php } ?>
            <label>New Password</label>
            <div class="password-container">
                <input type="password" name="new_password" id="new_password" placeholder="New Password" onkeyup="checkPasswords()">
                <i class="fas fa-eye-slash toggle-icon" onclick="togglePassword('new_password', this)"></i>
            </div>
            <label>Confirm New Password</label>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" onkeyup="checkPasswords()">
                <input type="hidden" name="final_password" id="final_password">
                <i class="fas fa-eye-slash toggle-icon" onclick="togglePassword('confirm_password', this)"></i>
            </div>
            <button type="submit">Reset Password</button>
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

        function checkPasswords() {
            var newPassword = document.getElementById('new_password').value;
            var confirmPassword = document.getElementById('confirm_password').value;
            var finalPassword = document.getElementById('final_password');

            if (newPassword === confirmPassword) {
                finalPassword.value = newPassword; // Set the value of final_password if passwords match
            } else {
                finalPassword.value = ''; // Clear final_password if passwords don't match
            }
        }
    </script>
</body>
</html>
