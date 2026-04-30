<?php

require __DIR__ . '/../vendor/autoload.php';

$files = [
    'in' => __DIR__ . '/../excel sheet/in.xlsx',
    'out' => __DIR__ . '/../excel sheet/out.xlsx',
];

foreach ($files as $name => $file) {
    echo "=== $name.xlsx ===\n";
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        foreach (array_slice($rows, 0, 5) as $i => $row) {
            $label = $i === 0 ? 'HEADERS' : "ROW $i";
            echo "$label: " . implode(' | ', array_map(fn($v) => $v ?? '', $row)) . "\n";
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage() . "\n";
    }
    echo "\n";
}
