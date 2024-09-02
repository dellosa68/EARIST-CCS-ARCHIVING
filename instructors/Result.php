<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
       
        .header {
            background-image: url('../images/kkkk.drawio.png'); /* Replace 'path/to/your/image.jpg' with the actual path to your image */
            background-size: cover;
            background-position: center;
            height: 200px; /* Adjust the height according to your image */
            width: 100%;
            color: #fff; /* Text color */
            text-align: center;
        }

        h2 {
            margin-top: 20px;
        }

        .filter-form {
            margin-bottom: 20px;
        }

        .filter-form label {
            margin-right: 10px;
        }

        .filter-form select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #fff;
            font-size: 16px;
        }

        .filter-form button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        .result-container {
            max-width: 90%;
            margin: 20px;
            padding: 20px;
        }

        .results {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .result-item {
            border: 1px solid #ddd;
            padding: 10px;
            width: calc(20% - 20px); /* Adjust the width to fit five items in a row */
            margin-bottom: 20px;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .result-item img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
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
    <?php
    // Retrieve the search query from the URL parameter
    $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

    // Display the search query below the header
    echo '<h2>Search Results for: ' . htmlspecialchars($searchQuery) . '</h2>';
    ?>
    <form action="" method="GET">
        <label for="category">Filter by Category:</label>
        <select name="category" id="category">
            <option value="">All</option>
            <option value="Category A">Category A</option>
            <option value="Category B">Category B</option>
            <option value="Category C">Category C</option>
            <!-- Add more options if needed -->
        </select>
        <button type="submit">Apply Filter</button>
    </form>

    <div class="result-container">
        <?php
// Sample results (replace with actual data retrieval logic)
$results = array(
    array("id" => 1, "title" => "Result 1", "category" => "Category A", "image" => "../images/ccs header.png"),
    array("id" => 2, "title" => "Result 2", "category" => "Category B", "image" => "../images/ccs header.png"),
    array("id" => 3, "title" => "Result 3", "category" => "Category A", "image" => "../images/ccs header.png"),
    array("id" => 4, "title" => "Result 4", "category" => "Category C", "image" => "../images/ccs header.png"),
    array("id" => 5, "title" => "Result 5", "category" => "Category A", "image" => "../images/ccs header.png")
);

// Retrieve the search query from the URL parameter
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';

// Filter results based on the search query and category filter
$filteredResults = array_filter($results, function ($result) use ($searchQuery, $categoryFilter) {
    // Perform case-insensitive search in the title and category
    $titleMatches = stripos($result['title'], $searchQuery) !== false;
    $categoryMatches = $categoryFilter === '' || $result['category'] === $categoryFilter;
    return $titleMatches && $categoryMatches;
});

// Display filtered results
if (empty($filteredResults)) {
    echo '<p>No results found.</p>';
} else {
    echo '<div class="results">';
    foreach ($filteredResults as $result) {
    // Wrap each result item in an <a> tag with a link to details.php
    echo '<a href="details.php?id=' . $result['id'] . '" class="result-item">'; // Assuming $result['id'] exists and represents the unique identifier of each result
    echo '<img src="' . $result['image'] . '" alt="' . $result['title'] . '">';
    echo '<div>';
    echo '<strong>' . $result['title'] . '</strong><br>';
    echo 'Category: ' . $result['category'];
    echo '</div>';
    echo '</a>';
}

    echo '</div>';
}
?>

    </div>
    
    <footer>
        &copy; 2024 Your Company Name. All rights reserved.
    </footer>

</body>
</html>
