<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <title>Research and IEEE Documentation System</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
        }
        #sidebar {
            height: 100%;
            width: 50px;
            position: fixed;
            z-index: 1;
            top: 0;
            left: 0;
            background-color: #111;
            overflow-x: hidden;
            transition: 0.5s;
        }
        #sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 20px;
            color: #818181;
            display: block;
            transition: 0.3s;
        }
        #sidebar a:hover {
            color: #FF7575;
        }
        #sidebar .close-btn {
            position: absolute;
            top: 0;
            right: 25px;
            font-size: 36px;
            margin-left: 50px;
            color: #fff;
        }
        .open-btn {
            border: none;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            background-color: transparent;
        }
        .open-btn:hover {
            background-color: #FF7575;
        }
        .admin-info {
            padding: 20px;
            text-align: center;
            display: none;
        }
        .admin-info img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        #sidebar.minimized a .text {
            display: none;
        }
        .main-content {
            flex-grow: 1;
            transition: margin-left 0.5s;
            margin-left: 50px;
        }
        .header {
            background-color: #d32f2f;
            color: white;
            padding: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .header img {
            height: 60px;
            margin-right: 20px;
        }
        .header .title h3 {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            margin: 0;
        }
        .container {
            background-color: white;
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
        }
        .profile {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
        }
        .profile .info {
            flex-grow: 1;
        }
        .tabs {
            margin-bottom: 20px;
        }
        .tabs a {
            padding: 10px 20px;
            display: inline-block;
            text-decoration: none;
            color: #d32f2f;
            border-bottom: 2px solid transparent;
            margin-right: 10px;
        }
        .tabs a.active {
            border-bottom: 2px solid #d32f2f;
        }
        .content {
            display: none;
        }
        .content.active {
            display: block;
        }
        .work-list {
            list-style-type: none;
            padding: 0;
        }
        .work-list li {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .work-list li .title {
            font-weight: bold;
        }
        .work-list li .status {
            background-color: #d32f2f;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div id="sidebar" class="minimized">
        <button class="open-btn" onclick="toggleNav()"><i class="fas fa-bars"></i></button>
        <div class="admin-info">
            <img src="../images/kkkk.drawio.png" alt="Admin Picture">
            <h3>TITE</h3>
        </div>
        <a href="#" class="minimized"><i class="fas fa-home"></i><span class="text"> Dashboard</span></a>
        <a href="#" class="minimized"><i class="fas fa-user"></i><span class="text"> User</span></a>
        <a href="#" class="minimized"><i class="fas fa-file-alt"></i><span class="text"> Documents</span></a>
        <a href="#" class="minimized"><i class="fas fa-cog"></i><span class="text"> Settings</span></a>
    </div>
    <div class="main-content" id="main-content">
        <div class="header">
            <img src="logo.png" alt="Logo">
            <div class="title">
                <h3>EARIST - College of Computing Studies Research Archiving System</h3>
            </div>
        </div>
        <div class="container">
            <div class="profile">
                <img src="../images/profile.png" alt="Instructor Photo">
                <div class="info">
                    <h2>Instructor Name</h2>
                    <p>Position</p>
                </div>
            </div>
            <div class="tabs">
                <a href="#about" class="active">About</a>
                <a href="#works">Works</a>
            </div>
            <div id="about" class="content active">
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam eget sodales erat. Curabitur aliquet risus ac mi vehicula, quis sollicitudin ligula cursus. Donec convallis urna eu lectus dictum, vel facilisis mi pretium.</p>
            </div>
            <div id="works" class="content">
                <ul class="work-list">
                    <li>
                        <span class="title">Title</span>
                        <span class="status">Status</span>
                    </li>
                    <li>
                        <span class="title">Title</span>
                        <span class="status">Status</span>
                    </li>
                    <li>
                        <span class="title">Title</span>
                        <span class="status">Status</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.tabs a').forEach(tab => {
            tab.addEventListener('click', function(event) {
                event.preventDefault();
                document.querySelectorAll('.tabs a').forEach(a => a.classList.remove('active'));
                document.querySelectorAll('.content').forEach(content => content.classList.remove('active'));
                this.classList.add('active');
                document.querySelector(this.getAttribute('href')).classList.add('active');
            });
        });

        function toggleNav() {
            var sidebar = document.getElementById("sidebar");
            var mainContent = document.getElementById("main-content");
            if (sidebar.classList.contains('minimized')) {
                sidebar.style.width = "250px";
                mainContent.style.marginLeft = "250px";
                document.querySelector(".admin-info").style.display = "block";
                sidebar.classList.remove('minimized');
            } else {
                sidebar.style.width = "50px";
                mainContent.style.marginLeft = "50px";
                document.querySelector(".admin-info").style.display = "none";
                sidebar.classList.add('minimized');
            }
        }
    </script>
</body>
</html>
