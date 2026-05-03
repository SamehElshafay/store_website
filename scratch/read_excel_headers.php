<?php
require 'd:/LaravelUI/store/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'd:/LaravelUI/store/excel sheet/2222.xlsx';

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);
    
    // Get the first row (headers)
    $headers = $rows[1] ?? [];
    
    echo "Headers:\n";
    print_r($headers);
    
    echo "\nFirst data row (row 2):\n";
    print_r($rows[2] ?? []);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
