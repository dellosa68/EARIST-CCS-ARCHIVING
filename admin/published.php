<?php
session_start(); 

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

// Get the new research ID from the URL query parameters
if (isset($_GET['research_id'])) {
    $new_research_id = $_GET['research_id'];
} else {
    // If research_id is not set, redirect to the home page
    header("Location: home.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Published</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .btn {
            margin-top: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>The research has been published</h2>
        <a href="home.php" class="btn btn-primary">Home</a>
        <a href="research_details.php?research_id=<?php echo $new_research_id; ?>" class="btn btn-secondary">View Research Details</a>
    </div>
</body>
</html>
