<?php
require_once '../conn/db_conn.php'; 
require_once 'vendor/autoload.php'; // Include Google Cloud PHP Client Library

use Google\Cloud\Storage\StorageClient;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve user input
    $research_id = $_POST['research_id']; // Assuming user input is the research ID
    
    // Connect to Google Cloud Storage
    $storage = new StorageClient([
        'projectId' => 'ccs-research',
        'keyFilePath' => 'service.json' // Path to your service account key file
    ]);

    // Retrieve soft copy link
    $soft_copy_object = $storage->bucket('ccs-researchas')->object("research/{$research_id}/soft_copy.pdf");
    $soft_copy_link = $soft_copy_object->signedUrl(new \DateTime('+5 minutes'));

    // Retrieve source code link
    $source_code_object = $storage->bucket('ccs-researchas')->object("source_code/{$research_id}/source_code.zip");
    $source_code_link = $source_code_object->signedUrl(new \DateTime('+5 minutes'));

    // Display links to the user
    echo "Soft Copy Link: <a href='{$soft_copy_link}'>Download Soft Copy</a><br>";
    echo "Source Code Link: <a href='{$source_code_link}'>Download Source Code</a><br>";
}
?>
