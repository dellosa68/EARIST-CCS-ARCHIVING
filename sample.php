<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Titles</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 300px;
            text-align: center;
            transition: transform 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .card-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
            padding: 0 15px;
        }
    </style>
</head>
<body>
    <h1>Thesis Titles</h1>
    <div class="container">
        <a href="details.php" class="card">
            <img src="cover1.jpg" alt="Thesis Cover 1">
            <div class="card-title">Thesis Title 1</div>
        </a>
        <a href="details2.html" class="card">
            <img src="cover2.jpg" alt="Thesis Cover 2">
            <div class="card-title">Thesis Title 2</div>
        </a>
        <a href="details3.html" class="card">
            <img src="cover3.jpg" alt="Thesis Cover 3">
            <div class="card-title">Thesis Title 3</div>
        </a>
        <!-- Add more cards as needed -->
    </div>
</body>
</html>
