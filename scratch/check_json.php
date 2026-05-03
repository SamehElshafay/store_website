<?php
$json = file_get_contents('d:/LaravelUI/store/lang/ar.json');
$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
    // Try to find duplicates manually
    $lines = explode("\n", $json);
    $keys = [];
    foreach ($lines as $i => $line) {
        if (preg_match('/^\s*"([^"]+)"\s*:/', $line, $matches)) {
            $key = $matches[1];
            if (isset($keys[$key])) {
                echo "Duplicate key: '$key' at line " . ($i + 1) . " (previous at line " . $keys[$key] . ")\n";
            }
            $keys[$key] = $i + 1;
        }
    }
} else {
    echo "JSON is valid according to PHP.\n";
}
