<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.header {
            background-image: url('images/kkkk.drawio.png'); /* Replace 'path/to/your/image.jpg' with the actual path to your image */
            background-size: cover;
            background-position: center;
            height: 200px; /* Adjust the height according to your image */
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff; /* Text color */
            text-align: center;
        }

.container {
    width: 100%;
    max-width: 400px;
    padding: 40px;
    background: #fff;
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
    margin-bottom: 50px;
    box-sizing: border-box;
    border-radius: 10px;
}

h2 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 28px;
    color: #333;
    text-align: center;
}

p {
    margin-bottom: 20px;
    color: #666;
    font-size: 16px;
    text-align: center;
}

form {
    text-align: center;
}

label {
    font-size: 16px;
    color: #555;
    margin-bottom: 8px;
    display: block;
    text-align: left;
}

input[type="text"],
input[type="password"],
select {
    width: 100%;
    padding: 14px;
    margin-bottom: 20px;
    border: 1px solid #ddd; /* Updated border */
    border-radius: 6px;
    font-size: 16px;
    background-color: #f5f5f5;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

input[type="text"]:focus,
input[type="password"]:focus,
select:focus {
    outline: none;
    background-color: #e0e0e0;
    border-color: #007bff; /* Updated border color */
}

input[type="submit"] {
    width: 100%;
    padding: 14px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

input[type="submit"]:hover {
    background-color: #0056b3;
}

a {
    text-decoration: none;
    color: #007bff;
    font-size: 14px;
}

a:hover {
    text-decoration: underline;
}

.form-group {
    position: relative;
    margin-bottom: 20px;
}

.input-group-append button {
    border: none;
    background-color: transparent;
    padding: 7px 10px;
    color: #666;
    transition: color 0.3s ease;
}

.input-group-append button:hover {
    color: #333;
}

footer {
    width: 100%;
    background-color: #333;
    color: #fff;
    padding: 20px 0;
    text-align: center;
    border-radius: 0 0 10px 10px; /* Updated border radius */
}

    </style>
</head>
<body>
    <div class="header"></div>
    <div class="container">
        <h2>USER AUTHENTICATION</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="conn/login.php" method="post">
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="Admin">Admin</option>
                    <option value="Instructor">Instructor</option>
                </select>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>    
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                            <i class="material-icons">visibility_off</i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div>
                <input type="submit" value="Login">
            </div>
        </form>
        <p><a href="register.php">Forgot Password?</a>.</p>
    </div>  
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById('password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
    </script>
</body>
</html>
