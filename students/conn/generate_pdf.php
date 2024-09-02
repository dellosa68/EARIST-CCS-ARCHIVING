<?php
require_once '../../vendor/autoload.php'; 

use Mpdf\Mpdf;

// Initialize MPDF
$mpdf = new Mpdf();

// Database connection
include '../../conn/db_conn.php';

// Fetch research details and comments
$research_id = $_GET['research_id']; // Get the research ID from URL or other source

// Fetch document details
$sql = "SELECT * FROM unreleased_research WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $research_id);
$stmt->execute();
$result = $stmt->get_result();
$doc = $result->fetch_assoc();

$title = htmlspecialchars($doc['title']);
$author = htmlspecialchars($doc['author']);
$keyword = htmlspecialchars($doc['keywords']);
$adviser = htmlspecialchars($doc['adviser']);
$system = htmlspecialchars($doc['system_link']);
$abstract = htmlspecialchars($doc['abstract']);

// Fetch panelists and their comments
$panelists = explode(", ", $doc['panel']);
$panel_ids = explode(", ", $doc['panel_id']);

?>

<!DOCTYPE html>
<html>
<head>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1><?php echo htmlspecialchars($title); ?></h1>
    <table>
        <tr>
            <th>Title</th>
            <td><?php echo htmlspecialchars($title); ?></td>
        </tr>
        <tr>
            <th>Author</th>
            <td><?php echo htmlspecialchars($author); ?></td>
        </tr>
        <tr>
            <th>Keywords</th>
            <td><?php echo htmlspecialchars($keyword); ?></td>
        </tr>
        <tr>
            <th>Adviser</th>
            <td><?php echo htmlspecialchars($adviser); ?></td>
        </tr>
    </table>

    <h2>Recommendations</h2>
    <table>
        <thead>
            <tr>
                <th>Panelist</th>
                <th>Recommendation</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $total_rows = 0;
                $complete_count = 0;
                $has_revision_remarks = false;

                // Split the panelists and panel IDs by commas
                $panelists = explode(", ", $doc['panel']);
                $panel_ids = explode(",", $doc['panel_id']);

                $all_complete = true; // Initialize the flag to true

// Loop through each panelist
foreach ($panelists as $index => $panelist) {
    $panel_id = isset($panel_ids[$index]) ? htmlspecialchars(trim($panel_ids[$index])) : 'N/A';
    
    // Fetch comments and statuses from the database for the current panel_id
    $comment_sql = "SELECT comment_text, status FROM comments WHERE account_id = '$panel_id' AND research_id = '$research_id'";
    $comment_result = $conn->query($comment_sql);
    
    $comment_texts = [];
    $yes_count = 0;

    if ($comment_result->num_rows > 0) {
        while ($row = $comment_result->fetch_assoc()) {
            $comment_text = htmlspecialchars($row['comment_text']);
            if ($row['status'] === 'YES') {
                $comment_text = "<span style='text-decoration: line-through;'>$comment_text</span>"; // Apply strikethrough
                $yes_count++;
            }
            $comment_texts[] = $comment_text;
            if ($row['status'] === 'Complete') {
                $complete_count++;
            }
        }
    }
    
    // Join all comments into a single string for display
    $comments_display = !empty($comment_texts) ? '<ul><li>' . implode('</li><li>', $comment_texts) . '</li></ul>' : 'No comments';
    
    // Count the number of comments
    $total_comments = count($comment_texts);
    
    // Determine remarks based on counts
    if ($total_comments > 0) {
        if ($yes_count < $total_comments) {
            $remarks = "For Revision";
            $has_revision_remarks = true;
            $all_complete = false; // Set to false if any remarks are not complete
        } else {
            $remarks = "Complete";
        }
    } else {
        $remarks = "No comments";
        $all_complete = false; // Set to false if there are no comments
    }
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars(trim($panelist)) . "</td>"; 
    echo "<td>$comments_display</td>"; 
    echo "<td>$remarks</td>"; // Display the remarks based on status counts
    echo "</tr>";
}

$second_accordion_style = $all_complete ? '' : 'style="display: none;"'; // Show accordion if all remarks are "Complete"

                    ?>
        </tbody>
    </table>
</body>
</html>

<?php
// Get the content from the output buffer
$html = ob_get_clean();

// Write HTML to the PDF
$mpdf->WriteHTML($html);

// Output the PDF
$mpdf->Output('Recommendation.pdf', \Mpdf\Output\Destination::INLINE); // To display in browser, use INLINE. Use FILE to save on server

// Close the database connection
$conn->close();
?>
