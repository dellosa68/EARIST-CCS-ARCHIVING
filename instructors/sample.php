<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkbox List Maker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #444;
        }
        form {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 10px;
            width: calc(100% - 22px);
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            padding: 10px 15px;
            background-color: #007bff;
            border: none;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        #checkboxList {
            margin-top: 20px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            margin: 5px 0;
        }
        .checkbox-item label {
            margin-left: 10px;
            flex-grow: 1;
        }
        .delete-button {
            background: #dc3545;
            border: none;
            color: #fff;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .delete-button:hover {
            background: #c82333;
        }
        ul {
            list-style-type: none; /* Remove bullets */
            padding: 0; /* Remove default padding */
        }
        li {
            margin: 10px 0;
        }
    </style>
    <script>
        // JavaScript function to add checkboxes dynamically
        function addCheckbox() {
            var inputValue = document.getElementById('itemInput').value;

            if (inputValue === '') return; // Prevent adding empty items

            // Create checkbox element
            var checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'selected_items[]';
            checkbox.value = inputValue;

            // Create hidden input to track checkbox status
            var hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'checkbox_status[]';
            hiddenInput.value = 'NO';

            // Update hidden input when checkbox is checked/unchecked
            checkbox.addEventListener('change', function() {
                hiddenInput.value = this.checked ? 'YES' : 'NO';
            });

            // Create label for checkbox
            var label = document.createElement('label');
            label.appendChild(document.createTextNode(inputValue));

            // Create delete button
            var deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'delete-button';
            deleteButton.textContent = 'Delete';
            deleteButton.onclick = function() {
                container.removeChild(checkbox);
                container.removeChild(label);
                container.removeChild(deleteButton);
                updateItemList(); // Update the comma-separated input field
            };

            // Append checkbox, hidden input, label, and delete button to the container
            var container = document.getElementById('checkboxList');
            var itemDiv = document.createElement('div');
            itemDiv.className = 'checkbox-item';
            itemDiv.appendChild(checkbox);
            itemDiv.appendChild(label);
            itemDiv.appendChild(deleteButton);
            container.appendChild(itemDiv);

            // Update the comma-separated input field
            updateItemList();

            // Clear the input field after adding item
            document.getElementById('itemInput').value = '';
        }

        // Function to update the comma-separated input field
        function updateItemList() {
            var checkboxes = document.getElementsByName('selected_items[]');
            var items = [];
            for (var i = 0; i < checkboxes.length; i++) {
                items.push(checkboxes[i].value);
            }
            document.getElementById('itemList').value = items.join(',');
        }
    </script>
</head>
<body>
    <form action="process_form.php" method="post">
        <h2>Enter Items:</h2>
        <input type="text" id="itemInput" placeholder="Enter item">
        <button type="button" onclick="addCheckbox()">Add Item</button>
        <br><br>
        <div id="checkboxList">
            <!-- Checkboxes will be dynamically added here -->
        </div>
        <br>
        <input type="hidden" id="itemList" name="item_list">
        <input type="submit" value="Submit">
    </form>
    <h2>Saved Checkbox List with Status:</h2>
    <style>
        ul {
            list-style-type: none; /* Remove bullets */
            padding: 0; /* Remove default padding */
        }
    </style>
    <?php
    // Database connection parameters
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

    // Query to fetch saved items and their statuses
    $sql = "SELECT item_name, status FROM selected_items";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<ul>";
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            $item_name = htmlspecialchars($row["item_name"]);
            $status = $row["status"];
            $checked = ($status === 'YES') ? "checked" : "";
            echo "<li>";
            echo "<input type='checkbox' disabled $checked>";
            echo "<label>$item_name</label>";
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "No items found.";
    }

    // Close connection
    $conn->close();
    ?>
    <br>
    <a href="index.html">Back to Form</a>
    <a href="index.html">Back to Form</a>
</body>
</html>
