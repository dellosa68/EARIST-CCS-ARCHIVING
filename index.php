<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha384-**YourIntegrityCodeHere**" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .header {
            background-image: url('images/kkkk.drawio.png');
            background-size: cover;
            background-position: center;
            height: 200px;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            text-align: center;
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
        .error, .success {
            background: #e74c3c; /* Red color for error */
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .success {
            background: #2ecc71; /* Green color for success */
        }
        .ca, .forgot {
            font-size: 14px;
            color: #555;
            text-decoration: none;
            margin-top: 10px;
            display: inline-block;
        }
        .ca:hover, .forgot:hover {
            text-decoration: underline;
        }
        .password-container {
            position: relative;
        }
        #password {
            padding-right: 35px;
        }
        .toggle-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 50%;
            background-color: #333;
        }
        footer {
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
        .main-container {
            display: flex;
            flex-direction: row;
            height: 100vh;
        }
        .details {
            background-image: url('images/1.jpg');
            background-size: cover;
            background-position: center;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            text-align: center;
            width: 50%;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="container">
            <form action="login.php" method="post">
                <h2>LOGIN</h2>
                <?php if (isset($_GET['error'])) { ?>
                <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
                <?php } ?>
                <?php if (isset($_GET['message'])) { ?>
                <p class="success"><?php echo htmlspecialchars($_GET['message']); ?></p>
                <?php } ?>
                <label>Username</label>
                <input type="text" name="uname" placeholder="Username"><br>
                <label>Password</label>
                <div class="password-container">
                    <input type="password" name="pass" id="password" placeholder="Password">
                    <i class="fas fa-eye-slash toggle-icon" id="toggleBtn" onclick="togglePassword()"></i>
                </div>
                <button type="submit">Login</button>
                <a href="students/student_registration.php" class="ca">Create an Account</a>
                <a href="forgot_password.php" class="forgot">Forgot Password?</a>
            </form>
        </div>
        <div class="details">
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var toggleBtn = document.getElementById("toggleBtn");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleBtn.classList.remove("fa-eye-slash");
                toggleBtn.classList.add("fa-eye");
            } else {
                passwordField.type = "password";
                toggleBtn.classList.remove("fa-eye");
                toggleBtn.classList.add("fa-eye-slash");
            }
        }
    </script>
</body>
</html>
