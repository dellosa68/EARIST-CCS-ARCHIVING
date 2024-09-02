<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify PIN</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <div class="container">
        <form action="process_verify_pin.php" method="post">
            <h2>Verify PIN</h2>
            <?php if (isset($_GET['error'])) { ?>
            <p class="error"><?php echo $_GET['error']; ?></p>
            <?php } ?>
            <label>Enter PIN</label>
            <input type="text" name="pin" placeholder="PIN"><br>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>
