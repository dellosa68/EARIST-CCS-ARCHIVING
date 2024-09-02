<?php
include "../conn/db_conn.php";

if (isset($_POST['course'])) {
    $selected_course = $conn->real_escape_string($_POST['course']);
    $query = "SELECT * FROM student WHERE course = '$selected_course' ORDER BY first_name";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $output = "<table border='1'><tr><th>Name</th><th>Course</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $output .= "<tr><td>" . htmlspecialchars($row['first_name']) . "</td><td>" . htmlspecialchars($row['course']) . "</td></tr>";
        }
        $output .= "</table>";
    } else {
        $output = "No results found.";
    }

    echo $output;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabbed Interface</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .tabs { display: flex; cursor: pointer; }
        .tab { padding: 10px 20px; border: 1px solid #ccc; margin-right: 5px; }
        .tab-content { display: none; padding: 20px; border: 1px solid #ccc; border-top: none; }
        .active { display: block; }
        .tab.active { background-color: #f0f0f0; }
    </style>
    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tabs;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tabs = document.getElementsByClassName("tab");
            for (i = 0; i < tabs.length; i++) {
                tabs[i].className = tabs[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        function fetchStudents(course) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "fetch_students.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById("students-table").innerHTML = xhr.responseText;
                }
            };
            xhr.send("course=" + encodeURIComponent(course));
        }

        function onCourseChange() {
            var course = document.getElementById("course").value;
            fetchStudents(course);
        }
    </script>
</head>
<body>

    <div class="tabs">
        <div class="tab active" onclick="openTab(event, 'Instructors')">Instructors</div>
        <div class="tab" onclick="openTab(event, 'Authors')">Authors</div>
        <div class="tab" onclick="openTab(event, 'Admin')">Admin</div>
    </div>

    <div id="Instructors" class="tab-content active">
        <h2>Instructors Tab</h2>
        <!-- Content for Instructors -->
    </div>

    <div id="Authors" class="tab-content">
        <h2>Authors Tab</h2>
        <form id="sort-form">
            <label for="course">Sort by course:</label>
            <select id="course" name="course" onchange="onCourseChange()">
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo htmlspecialchars($course); ?>"><?php echo htmlspecialchars($course); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <div id="students-table">
            <!-- Table will be loaded here via AJAX -->
        </div>
    </div>

    <div id="Admin" class="tab-content">
        <h2>Admin Tab</h2>
        <!-- Content for Admin -->
    </div>

</body>
</html>
