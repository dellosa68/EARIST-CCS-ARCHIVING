<?php
require_once '../vendor/autoload.php'; // Adjust path as needed

use Mpdf\Mpdf;

try {
    $mpdf = new Mpdf();
    $mpdf->WriteHTML('<h1>Hello World</h1>');
    $mpdf->Output('test.pdf', \Mpdf\Output\Destination::INLINE); // Display in browser
} catch (\Mpdf\MpdfException $e) {
    echo "Error: " . $e->getMessage();
}
?>
