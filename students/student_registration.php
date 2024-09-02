<?php
// Handle the AJAX request
if (isset($_POST['check_student_id'])) {
    // Database connection details
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "research";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $student_id = $_POST['student_id'];
    
    // Prepare a statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT COUNT(*) FROM student WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    // Output 'exists' if the Student ID is found in the database
    echo ($count > 0) ? 'exists' : 'not exists';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha384-**YourIntegrityCodeHere**" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin-top: 80px;
            padding: 0;
        }
       
        .header {
           
        }

        footer {
            margin-top: 50px;
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 90px; /* Adjust if necessary */
            width: 800px;
        }
        .form-group select {
            width: calc(100% - 10px);
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 50px;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-control {
            border-radius: 20px;
            border: 1px solid #ccc;
            padding: 12px 20px;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: none;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 20px;
            padding: 12px 30px;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
        .custom-file {
            margin-bottom: 20px;
        }

        .custom-file-input {
            cursor: pointer;
        }

        .custom-file-label {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .preview-image {
            width: 100%;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .profile-picture {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .profile-picture label {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .profile-picture input[type="file"] {
            display: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }

        .profile-picture img {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
          .password-container {
            position: relative;
        }
        .password-container input{
            width: 100%;
            padding: 5px;
            border-radius: 50px;
            border-radius: 1px solid gray;
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
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1;
            transition: background-color 0.3s;
            background-color: rgba(0, 0, 0, 0.8);
        }
        .navbar.scrolled {
            background-color: rgba(0, 0, 0, 0.8); /* Background color when scrolled */
        }
        .navbar .logo {
            display: flex;
            align-items: center;
        }
        .navbar .logo img {
            height: 60px; /* Increased the height */
            margin-right: 10px;
        }
        .navbar .logo h2 {
            color: white;
            margin: 0;
            font-family: 'Times New Roman', serif;
        }
        .navbar .menu {
            display: flex;
            align-items: center;
        }
        .navbar .menu a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            transition: color 0.3s;
        }
        .navbar .menu a:hover {
            color: #FF7575;
        }
        .navbar .profile {
            display: flex;
            align-items: center;
        }
        .navbar .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .navbar .profile p {
            color: #fff;
            margin: 0;
        }

    </style>
</head>
<body>
    <div class="main-content">
        <div class="header" style="display: block;">
            <nav class="navbar" id="navbar">
                <div class="logo">
                    <img src="../images/ccs.png" alt="Logo">
                    <h2>EARIST - College of Computing Studies Research Archiving System</h2>
                </div>   
            </nav>
        </div>
        <div class="container">
        
        <h2 class="mt-4">Registration Form</h2>
        <form action="conn/signup.php" method="post" enctype="multipart/form-data">
            <div class="profile-picture">
                <img id="preview" src="../images/profile.png" alt="Preview">
                <label for="profile-picture">Choose Picture</label>
                <input type="file" id="profile-picture" name="profile-picture" onchange="previewImage(event)" required>
            </div>
            <div class="form-group">
                <label for="student_id">Student ID:</label>
                <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter Student ID" onkeyup="checkStudentID()">
                <small id="studentIDFeedback" class="form-text text-danger" style="display: none;">Student ID already exists</small>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="first_name">First Name:</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter First Name">
                </div>
                <div class="form-group col-md-6">
                    <label for="last_name">Last Name:</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter Last Name">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter Username">
                </div>
                <div class="form-group col-md-6">
                    <label for="password">Password:</label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                        <i class="fas fa-eye-slash toggle-icon" id="toggleBtn" onclick="togglePassword()"></i>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email">
                </div>
                <div class="form-group col-md-6">
                    <label for="phone">Phone:</label>
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter Phone Number">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="course">Course:</label>
                    <select id="course" class="form-control" name="course">
                        <option selected>Choose</option>
                        <option>Bachelor of Science in Computer Science</option>
                        <option>Bachelor of Arts in English</option>
                        <!-- Add more options as needed -->
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="year">Year:</label>
                    <select id="year" class="form-control" name="year">
                        <option selected>Choose</option>
                        <option>1st Year</option>
                        <option>2nd Year</option>
                        <!-- Add more options as needed -->
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="section">Section:</label>
                    <select id="section" class="form-control" name="section">
                        <option selected>Choose</option>
                        <option>Section A</option>
                        <option>Section B</option>
                        <!-- Add more options as needed -->
                    </select>
                </div>
            </div>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="certificate" name="certificate" accept="image/*" required>
                <label class="custom-file-label" for="certificate">Choose Certificate of Registration</label>
                <small>Please upload file in .img .png format with a maximum size of 2MB</small>
            </div>
            <div id="preview-container" style="display: none;">
                <img id="preview-image" class="preview-image" src="#" alt="Preview">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
    </div>
    
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        document.getElementById('certificate').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.addEventListener('load', function() {
                    document.getElementById('preview-container').style.display = 'block';
                    document.getElementById('preview-image').setAttribute('src', this.result);
                });
                reader.readAsDataURL(file);
            } else {
                document.getElementById('preview-container').style.display = 'none';
            }
        });
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function () {
                var preview = document.getElementById('preview');
                preview.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

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

        function checkStudentID() {
            var studentID = $('#student_id').val();
            if (studentID.length > 0) {
                $.ajax({
                    url: '', // Same file
                    type: 'POST',
                    data: { check_student_id: true, student_id: studentID },
                    success: function(response) {
                        if (response == 'exists') {
                            $('#studentIDFeedback').show();
                        } else {
                            $('#studentIDFeedback').hide();
                        }
                    }
                });
            } else {
                $('#studentIDFeedback').hide();
            }
        }
        function checkStudentID() {
            var studentID = $('#student_id').val();
            if (studentID.length > 0) {
                $.ajax({
                    url: '', // Same file
                    type: 'POST',
                    data: { check_student_id: true, student_id: studentID },
                    success: function(response) {
                        if (response == 'exists') {
                            $('#studentIDFeedback').show();
                        } else {
                            $('#studentIDFeedback').hide();
                        }
                    }
                });
            } else {
                $('#studentIDFeedback').hide();
            }
        }
    </script>
</body>
</html>