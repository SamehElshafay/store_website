<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    echo "Attempting to use IOFactory...\n";
    $class = new ReflectionClass('PhpOffice\PhpSpreadsheet\IOFactory');
    echo "Class found! Path: " . $class->getFileName() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
