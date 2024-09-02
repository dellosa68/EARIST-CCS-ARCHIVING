<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .registration-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 600px;
        }

        .registration-form h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
        }

        .form-group {
            margin-bottom: 15px;
            flex: 1;
            padding: 0 10px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: calc(100% - 10px);
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group input[type="file"] {
            border: none;
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
        }

        .profile-picture img {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        button:hover {
            background-color: #0056b3;
        }

        small {
            display: block;
            margin-top: 5px;
            color: #888;
        }

    </style>
</head>
<body>
    <div class="registration-form">
        <h2>Registration Form</h2>
        <form>
            <div class="profile-picture">
                <img id="preview" src="../images/profile.png" alt="Preview">
                <label for="profile-picture">Choose Picture</label>
                <input type="file" id="profile-picture" name="profile-picture" onchange="previewImage(event)">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="student-number">Student Number</label>
                    <input type="text" id="student-number" name="student-number">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="first-name">First Name</label>
                    <input type="text" id="first-name" name="first-name">
                </div>
                <div class="form-group">
                    <label for="middle-name">Middle Name</label>
                    <input type="text" id="middle-name" name="middle-name">
                </div>
                <div class="form-group">
                    <label for="last-name">Last Name</label>
                    <input type="text" id="last-name" name="last-name">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="phone-number">Phone Number</label>
                    <input type="text" id="phone-number" name="phone-number">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="course">Course</label>
                    <select id="course" name="course">
                        <option value="bsit">BSIT</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year">Year</label>
                    <select id="year" name="year">
                        <option value="bsit">BSIT</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="section">Section</label>
                    <select id="section" name="section">
                        <option value="bsit">BSIT</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="certificate">Certificate of Registration</label>
                <small>Please upload file in .jpg, .jpeg, .png format with maximum size of 5 MB</small>
                <input type="file" id="certificate" name="certificate" accept=".jpg, .jpeg, .png" onchange="previewCertificate(event)">
                <img id="certificate-preview" src="#" alt="Certificate Preview" style="display: none; max-width: 300px; max-height: 300px;">
            </div>
            <button type="submit">Submit</button>
        </form>
    </div>
<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function () {
            var preview = document.getElementById('preview');
            preview.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
    function previewCertificate(event) {
        var reader = new FileReader();
        reader.onload = function () {
            var preview = document.getElementById('certificate-preview');
            preview.style.display = 'block';
            preview.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
</body>
</html>
