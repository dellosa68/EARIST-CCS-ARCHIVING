<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
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
      background-image: url('../images/kkkk.drawio.png');
      background-size: cover;
      background-position: center;
      height: 200px;
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #fff; /* Text color */
      text-align: center;
    }

    .content-section {
      display: flex; /* Create two columns */
      width: 100%; /* Adjust width as needed */
      margin: 20px 0; /* Add some margin for spacing */
    }

  
    .content-column:first-child { /* Target the first child only */
      width: 80%; /* Set width to 66.66% for the first column */
      padding: 15px 80px;
      border: 1px solid #ddd;
    }

    .content-column:last-child { /* Target the last child only */
      width: 20%; /* Set width to 33.33% for the second column */
      padding: 15px, 15px;
      border: 1px solid #ddd;

    }


    footer {
      width: 100%;
      background-color: #333;
      color: #fff;
      padding: 20px 0;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="header"></div>

  <section class="content-section">
    <div class="content-column">
      <h2 style="font-size: 30px;">This is content for the left column.<h2>
    </div>

    <div class="content-column" style="height: 150px;">
      <p>This is content for the right column.</p>
    </div>
  </section>

  <footer>
    &copy; 2024 Your Company Name. All rights reserved.
  </footer>
</body>
</html>
