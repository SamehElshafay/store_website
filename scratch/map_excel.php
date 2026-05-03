<?php
require 'd:/LaravelUI/store/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'd:/LaravelUI/store/excel sheet/2222.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);

$headers = $rows[1];
$data = $rows[2];

foreach ($headers as $col => $name) {
    echo "[$col] $name => " . ($data[$col] ?? '') . "\n";
}
